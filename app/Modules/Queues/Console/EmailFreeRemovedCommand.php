<?php

namespace App\Modules\Queues\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Modules\History\Models\Log;
use App\Modules\Queues\Mail\FreeRemoved;
use App\Modules\Queues\Mail\FreeRemovedManager;
use App\Modules\Queues\Models\Queue;
use App\Modules\Queues\Models\GroupUser;
use App\Modules\Queues\Models\User as QueueUser;
use App\Modules\Queues\Models\Scheduler;
use App\Modules\Users\Models\User;
use App\Modules\Groups\Models\Group;
use App\Modules\Resources\Events\ResourceMemberStatus;

/**
 * This script proccess all newly removed groupqueueuser entries
 * Notice State 3 => 9
 */
class EmailFreeRemovedCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'queues:emailfreeremoved {--debug : Output emails rather than sending}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Email new groupqueueuser removals.';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		$debug = $this->option('debug') ? true : false;

		$gu = (new GroupUser)->getTable();
		$qu = (new QueueUser)->getTable();
		$q = (new Queue)->getTable();
		$s = (new Scheduler)->getTable();

		$users = GroupUser::query()
			->select($gu . '.*', $qu . '.queueid')
			->join($qu, $qu . '.id', $gu . '.queueuserid')
			->join($q, $q . '.id', $qu . '.queueid')
			->onlyTrashed()
			->whereIn($qu . '.membertype', [1, 4])
			->where($qu . '.notice', '=', 3)
			->get();

		if (!count($users))
		{
			if ($debug)
			{
				$this->comment('No records to email.');
			}
			return;
		}

		// Group activity by groupid so we can determine when to send the group mail
		$group_activity = array();

		foreach ($users as $user)
		{
			if (!isset($group_activity[$user->groupid]))
			{
				$group_activity[$user->groupid] = array();
			}

			array_push($group_activity[$user->groupid], $user);
		}

		$now = date("U");
		$threshold = 300; // threshold for when considering activity "done"

		foreach ($group_activity as $groupid => $groupqueueusers)
		{
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
				$group = Group::find($groupid);

				if (!$group)
				{
					if ($debug)
					{
						$this->error('Could not find group #' . $groupid);
					}
					continue;
				}

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
				$data = array();
				$removals = array();
				foreach ($user_activity as $userid => $groupqueuestudents)
				{
					// Start assembling email
					$user = User::find($userid);

					if (!$user)
					{
						if ($debug)
						{
							$this->error('Could not find account for user #' . $userid);
						}
						continue;
					}

					$existing = QueueUser::query()
						->withTrashed()
						->join($q, $q . '.id', $qu . '.queueid')
						->join($s, $s . '.id', $q . '.schedulerid')
						->where($qu . '.membertype', '=', 1)
						->where($qu . '.userid', '=', $userid)
						->where($qu . '.notice', '<>', 6)
						->where(function($where) use ($qu)
						{
							$where->whereNull($qu . '.datetimeremoved')
								->orWhere($qu . '.datetimeremoved', '=', '0000-00-00 00:00:00');
						})
						->where(function($where) use ($q)
						{
							$where->whereNull($q . '.datetimeremoved')
								->orWhere($q . '.datetimeremoved', '=', '0000-00-00 00:00:00');
						})
						->where(function($where) use ($s)
						{
							$where->whereNull($s . '.datetimeremoved')
								->orWhere($s . '.datetimeremoved', '=', '0000-00-00 00:00:00');
						})
						->get()
						->pluck('queueid')
						->toArray();

					$removing = collect($groupqueuestudents)->whereIn('queueid', $existing);

					// Is anything actually being removed?
					if (!count($removing))
					{
						continue;
					}

					// Determine if any roles are being removed
					$last_role = '';
					$removals[$userid] = array();
					foreach ($groupqueuestudents as $queueuser)
					{
						if (!$queueuser->queue)
						{
							continue;
						}

						if (!$queueuser->queue->resource)
						{
							continue;
						}

						$role = $queueuser->queue->resource->rolename;

						if ($role == $last_role)
						{
							continue; // skip, we already checked this role
						}

						$last_role = $role;

						// Contact role provision service
						event($event = new ResourceMemberStatus($queueuser->queue->resource, $user));

						if ($event->status == 1  // ROLE_REMOVAL_PENDING
						 || $event->status == 4) // NO_ROLE_EXISTS
						{
							array_push($removals[$userid], $queueuser->queue->resource);
						}
					}

					$data[$userid] = array(
						'user'       => $user,
						'queueusers' => $groupqueuestudents,
					);

					$keeping = QueueUser::query()
						->withTrashed()
						->select($qu . '.*')
						->join($q, $q . '.id', $qu . '.queueid')
						->join($s, $s . '.id', $q . '.schedulerid')
						->where($qu . '.membertype', '=', 1)
						->where($qu . '.userid', '=', $userid)
						->where($qu . '.notice', '<>', 6)
						->whereNotIn($qu . '.queueid', $removing->pluck('queueid')->toArray())
						->where(function($where) use ($qu)
						{
							$where->whereNull($qu . '.datetimeremoved')
								->orWhere($qu . '.datetimeremoved', '=', '0000-00-00 00:00:00');
						})
						->where(function($where) use ($q)
						{
							$where->whereNull($q . '.datetimeremoved')
								->orWhere($q . '.datetimeremoved', '=', '0000-00-00 00:00:00');
						})
						->where(function($where) use ($s)
						{
							$where->whereNull($s . '.datetimeremoved')
								->orWhere($s . '.datetimeremoved', '=', '0000-00-00 00:00:00');
						})
						->get();

					// Prepare and send actual email
					$message = new FreeRemoved($user, $removing, $keeping, $removals[$userid]);

					if ($debug)
					{
						echo $message->render();
						$this->info("Emailed freeremoved to {$user->email}.");
						continue;
					}

					Mail::to($user->email)->send($message);

					//$this->info("Emailed freeremoved to {$user->email}.");

					$r = collect($removals[$userid])->pluck('rolename')->toArray();

					// Change states
					foreach ($groupqueuestudents as $queueuser)
					{
						if (!$queueuser->queue)
						{
							continue;
						}

						if (!$queueuser->queue->resource)
						{
							continue;
						}

						// Determine which state to go to, depending on whether a new role was created
						$q = $queueuser->queue->resource;

						$notice = 0;
						if (in_array($q->rolename, $r))
						{
							$notice = 9;
						}

						$groupqueue->update(['notice' => $notice]);
					}
				}

				if (empty($data))
				{
					continue;
				}

				// Email group managers
				foreach ($group->managers as $manager)
				{
					// Prepare and send actual email
					$message = new FreeRemovedManager($manager->user, $data);

					if ($debug)
					{
						echo $message->render();
						$this->info("Emailed freeremoved to manager {$manager->user->email}.");
						continue;
					}

					Mail::to($manager->user->email)->send($message);

					$this->log($manager->user->id, $manager->user->email, "Emailed freeremoved to manager.");
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
	protected function log($targetuserid, $uri = '', $payload = '')
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
			'classname'       => Str::limit('queues:emailfreeremoved', 32, ''),
			'classmethod'     => Str::limit('handle', 16, ''),
			'targetuserid'    => $targetuserid,
		]);
	}
}
