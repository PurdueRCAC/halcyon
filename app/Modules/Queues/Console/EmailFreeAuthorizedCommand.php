<?php

namespace App\Modules\Queues\Console;

//use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Modules\History\Models\Log;
use App\Modules\Queues\Mail\FreeAuthorized;
use App\Modules\Queues\Mail\FreeAuthorizedManager;
use App\Modules\Queues\Models\Queue;
use App\Modules\Queues\Models\GroupUser;
use App\Modules\Queues\Models\User as QueueUser;
use App\Modules\Users\Models\User;
use App\Modules\Groups\Models\Group;
use App\Modules\Resources\Events\ResourceMemberStatus;

/**
 * This script proccess all new authorized groupqueueuser entries
 * Notice State 2 => 8
 */
class EmailFreeAuthorizedCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'queues:emailfreeauthorized {--debug : Output emails rather than sending}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Email latest authorized groupqueueuser entries.';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		$debug = $this->option('debug') ? true : false;

		$gu = (new GroupUser)->getTable();
		$qu = (new QueueUser)->getTable();
		$q = (new Queue)->getTable();

		$groupqueueusers = GroupUser::query()
			->select($gu . '.*', $qu . '.queueid')
			->join($qu, $qu . '.id', $gu . '.queueuserid')
			->join($q, $q . '.id', $qu . '.queueid')
			->whereIn($qu . '.membertype', [1, 4])
			->where($qu . '.notice', '=', 2)
			->where(function($where) use ($qu)
			{
				$where->whereNull($qu . '.datetimeremoved')
					->orWhere($qu . '.datetimeremoved', '=', '0000-00-00 00:00:00');
			})
			->where(function($where) use ($gu)
			{
				$where->whereNull($gu . '.datetimeremoved')
					->orWhere($gu . '.datetimeremoved', '=', '0000-00-00 00:00:00');
			})
			->get();

		if (!count($groupqueueusers))
		{
			if ($debug)
			{
				$this->comment('No records to email.');
			}
			return;
		}

		// Group activity by groupid so we can determine when to send the group mail
		$group_activity = array();

		foreach ($groupqueueusers as $groupqueueuser)
		{
			if (!isset($group_activity[$groupqueueuser->groupid]))
			{
				$group_activity[$groupqueueuser->groupid] = array();
			}

			array_push($group_activity[$groupqueueuser->groupid], $groupqueueuser);
		}

		$now = date("U");
		$threshold = 300; // threshold for when considering activity "done"

		foreach ($group_activity as $groupid => $groupqueueusers)
		{
			if ($debug)
			{
				$this->info("Processing group ID #{$groupid}...");
			}

			// Find the latest activity
			$latest = 0;
			foreach ($groupqueueusers as $g)
			{
				if ($g->datetimecreated->format('U') > $latest)
				{
					$latest = $g->datetimecreated->format('U');
				}
			}

			if ($now - $latest >= $threshold)
			{
				// Email everyone involved in this group

				// Condense students
				$user_activity = array();

				foreach ($groupqueueusers as $gquser)
				{
					$queueuser = $gquser->queueuser;

					if (!isset($user_activity[$queueuser->userid]))
					{
						$user_activity[$queueuser->userid] = array();
					}

					array_push($user_activity[$queueuser->userid], $queueuser);
				}

				// Send email to each student
				$roles = array();
				$data = array();
				foreach ($user_activity as $userid => $queueusers)
				{
					$user = User::find($userid);

					if (!$user || !$user->id || $user->isTrashed())
					{
						if ($debug)
						{
							$this->error('Could not find account for user #' . $userid);
						}
						continue;
					}

					$roles[$userid] = array();
					$last_role = '';

					foreach ($queueusers as $queueuser)
					{
						$queue = $queueuser->queue()->withTrashed()->first();

						// Queue was removed
						if (!$queueuser->queue)
						{
							continue;
						}

						$resource = $queue->resource()->withTrashed()->first();

						// Resource was removed
						if (!$resource)
						{
							continue;
						}

						$role = $resource->rolename;

						if ($role == $last_role)
						{
							continue; // skip, we already checked this role
						}

						$last_role = $role;

						// Contact role provision service
						event($event = new ResourceMemberStatus($resource, $user));

						if ($event->status != 3) // ROLE_ACCOUNTS_READY
						{
							echo $resource->id . "\n";
							array_push($roles[$userid], $resource);
						}
					}

					$data[$userid] = array(
						'user'       => $user,
						'queueusers' => $queueusers,
						'roles'      => $roles[$userid]
					);

					// Prepare and send actual email
					$message = new FreeAuthorized($user, $queueusers, $roles[$userid]);

					if ($debug)
					{
						echo $message->render();
						$this->info("Emailed freeauthorized to {$user->email}.");
						continue;
					}

					Mail::to($user->email)->send($message);

					$this->log($user->id, $groupid, $user->email, 'Emailed freeauthorized.');

					$r = collect($roles[$userid])->pluck('rolename')->toArray();

					// Change states
					foreach ($queueusers as $queueuser)
					{
						// Determine which state to go to, depending on whether a new role was created
						$rolename = $queueuser->queue->resource->rolename;

						$notice = 0;
						if (in_array($rolename, $r))
						{
							$notice = 8;
						}

						$queueuser->update(['notice' => $notice]);
					}
				}

				if (empty($data))
				{
					continue;
				}

				// Assemble list of managers to email
				$group = Group::find($groupid);

				foreach ($group->managers as $manager)
				{
					// Prepare and send actual email
					$message = new FreeAuthorizedManager($manager->user, $data);

					if ($debug)
					{
						echo $message->render();
						$this->info("Emailed freeauthorized to manager {$manager->user->email}.");
						continue;
					}

					$user = $manager->user;

					if (!$user || !$user->id || $user->isTrashed())
					{
						continue;
					}

					Mail::to($user->email)->send($message);

					$this->log($user->id, $groupid, $user->email, 'Emailed freeauthorized to manager.');
				}
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
			'classname'       => Str::limit('queues:emailfreeauthorized', 32, ''),
			'classmethod'     => Str::limit('handle', 16, ''),
			'targetuserid'    => (int)$targetuserid,
			'targetobjectid'  => (int)$targetobjectid,
			'objectid'        => (int)$targetobjectid,
		]);
	}
}
