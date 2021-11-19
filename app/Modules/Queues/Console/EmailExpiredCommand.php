<?php

namespace App\Modules\Queues\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Modules\History\Models\Log;
use App\Modules\Queues\Mail\Expired;
use App\Modules\Queues\Models\Queue;
use App\Modules\Queues\Models\User as QueueUser;
use App\Modules\Users\Models\UserUsername;
use App\Modules\Groups\Models\Group;
use App\Modules\Resources\Events\ResourceMemberStatus;
use App\Modules\Resources\Models\Subresource;
use Carbon\Carbon;

/**
 * Newly exipred users
 */
class EmailExpiredCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'queues:emailexpired {--debug : Output emails rather than sending}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Email expired accounts.';

	/**
	 * Execute the console command.
	 * 
	 * @return  void
	 */
	public function handle()
	{
		$debug = $this->option('debug') ? true : false;

		$uu = (new UserUsername)->getTable();
		$qu = (new QueueUser)->getTable();
		$q = (new Queue)->getTable();
		$s = (new Subresource)->getTable();

		$queueusers = QueueUser::query()
			->select($qu . '.*')
			->join($uu, $uu . '.userid', $qu . '.userid')
			->join($q, $q . '.id', $qu . '.queueid')
			->join($s, $s . '.id', $q . '.subresourceid')
			->whereNull($q . '.datetimeremoved')
			->whereNull($qu . '.datetimeremoved')
			->whereNull($uu . '.dateremoved')
			->whereNull($s . '.datetimeremoved')
			->where(function($where) use ($uu)
			{
				$now = Carbon::now()->modify('-1 day');

				$where->whereNotNull($uu . '.datelastseen')
					->where($uu . '.datelastseen', '<', $now->toDateTimeString());
			})
			->groupBy($qu . '.id')
			->groupBy($qu . '.datetimecreated')
			->groupBy($qu . '.userid')
			->groupBy($qu . '.queueid')
			->groupBy($qu . '.userrequestid')
			->groupBy($qu . '.membertype')
			->groupBy($qu . '.datetimeremoved')
			->groupBy($qu . '.datetimelastseen')
			->groupBy($qu . '.notice')
			->groupBy($q . '.groupid')
			->groupBy($uu . '.datecreated')
			->groupBy($uu . '.datelastseen')
			->orderBy($uu . '.datecreated', 'asc')
			->orderBy($uu . '.datelastseen', 'asc')
			->limit(1000)
			->get();

		if (!count($queueusers))
		{
			if ($debug || $this->output->isVerbose())
			{
				$this->comment('No records to email.');
			}
			return;
		}

		// Group activity by groupid so we can determine when to send the group mail
		$group_activity = array();

		foreach ($queueusers as $queueuser)
		{
			if (!$queueuser->queue)
			{
				if ($debug || $this->output->isVerbose())
				{
					$this->error("Could not find queue for #{$queueuser->queueid}.");
				}
				continue;
			}

			$resource = $queueuser->queue->resource;

			if (!$resource)
			{
				if ($debug || $this->output->isVerbose())
				{
					$this->error("Could not find resource for #{$queueuser->id}.");
				}
				continue;
			}

			event($event = new ResourceMemberStatus($resource, $queueuser->user));

			// -1 = connect or something equally bad
			//  0 = invalid user
			//  1 = NO_ROLE_EXISTS
			if ($event->status <= 1)
			{
				if ($event->status < 0 && ($debug || $this->output->isVerbose()))
				{
					$this->error("Something bad happened looking up resource member status for " . $resource->id . '.' . $queueuser->userid);
				}

				continue;
			}

			$groupid = $queueuser->queue->groupid;

			if (!isset($group_activity[$groupid]))
			{
				$group_activity[$groupid] = array();
			}

			array_push($group_activity[$groupid], $queueuser);
		}

		if (!count($group_activity))
		{
			if ($debug || $this->output->isVerbose())
			{
				$this->comment('No records to email.');
			}
			return;
		}

		foreach ($group_activity as $groupid => $queueusers)
		{
			if ($debug)
			{
				$this->line("Starting processing group ID #{$groupid}.");
			}

			$group = Group::find($groupid);

			if (!$group)
			{
				continue;
			}

			foreach ($group->managers as $manager)
			{
				$user = $manager->user;

				if (!$user || !$user->id || $user->trashed())
				{
					continue;
				}

				// Prepare and send actual email
				$message = new Expired($manager->user, $queueusers);

				if ($this->output->isDebug())
				{
					echo $message->render();
				}

				if ($debug || $this->output->isVerbose())
				{
					$this->info("Emailed expired to manager {$manager->user->email}.");

					if ($debug)
					{
						continue;
					}
				}

				if (!$user->email)
				{
					if ($debug || $this->output->isVerbose())
					{
						$this->error("Email address not found for user {$user->name}.");
					}
					continue;
				}

				Mail::to($user->email)->send($message);

				$this->log($user->id, $groupid, $user->email, "Emailed expired to manager.");
			}
		}
	}

	/**
	 * Log email
	 *
	 * @param   integer $targetuserid
	 * @param   integer $targetobjectid
	 * @param   string  $uri
	 * @param   mixed   $payload
	 * @return  null
	 */
	protected function log($targetuserid, $targetobjectid, $uri = '', $payload = '')
	{
		Log::create([
			'ip'              => request()->ip(),
			'userid'          => (auth()->user() ? auth()->user()->id : 0),
			'status'          => 200,
			'transportmethod' => 'POST',
			'servername'      => request()->getHttpHost(),
			'uri'             => Str::limit($uri, 128, ''),
			'app'             => Str::limit('email', 20, ''),
			'payload'         => Str::limit($payload, 2000, ''),
			'classname'       => Str::limit('queues:emailexpired', 32, ''),
			'classmethod'     => Str::limit('handle', 16, ''),
			'targetuserid'    => (int)$targetuserid,
			'targetobjectid'  => (int)$targetobjectid,
			'objectid'        => (int)$targetobjectid,
		]);
	}
}
