<?php

namespace App\Modules\Queues\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
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
			->where(function($where) use ($q)
			{
				$where->whereNull($q . '.datetimeremoved')
					->orWhere($q . '.datetimeremoved', '=', '0000-00-00 00:00:00');
			})
			->where(function($where) use ($qu)
			{
				$where->whereNull($qu . '.datetimeremoved')
					->orWhere($qu . '.datetimeremoved', '=', '0000-00-00 00:00:00');
			})
			->where(function($where) use ($uu)
			{
				$where->whereNull($uu . '.dateremoved')
					->orWhere($uu . '.dateremoved', '=', '0000-00-00 00:00:00');
			})
			->where(function($where) use ($s)
			{
				$where->whereNull($s . '.datetimeremoved')
					->orWhere($s . '.datetimeremoved', '=', '0000-00-00 00:00:00');
			})
			->where(function($where) use ($uu)
			{
				$now = Carbon::now()->modify('-1 day');

				$where->whereNotNull($uu . '.datelastseen')
					->where($uu . '.datelastseen', '!=', '0000-00-00 00:00:00')
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
			if ($debug)
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
				$this->error("Could not find queue for #{$queueuser->queueid}.");
				continue;
			}

			$resource = $queueuser->queue->resource;

			if (!$resource)
			{
				$this->error("Could not find resource for #{$queueuser->id}.");
				continue;
			}

			event($event = new ResourceMemberStatus($resource, $queueuser->user));

			// -1 = connect or something equally bad
			//  0 = invalid user
			//  1 = NO_ROLE_EXISTS
			if ($event->status <= 1)
			{
				if ($event->status < 0)
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
			if ($debug)
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
				// Prepare and send actual email
				$message = new Expired($manager->user, $queueusers);

				if ($debug)
				{
					echo $message->render();
					continue;
				}

				Mail::to($manager->user->email)->send($message);

				//$this->info("Emailed expired to manager {$manager->user->email}.");
			}
		}
	}
}
