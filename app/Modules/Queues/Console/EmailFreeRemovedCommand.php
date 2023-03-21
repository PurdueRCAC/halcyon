<?php

namespace App\Modules\Queues\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
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
			if ($debug || $this->output->isVerbose())
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
					if ($debug || $this->output->isVerbose())
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
						if ($debug || $this->output->isVerbose())
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
						->whereNull($qu . '.datetimeremoved')
						->whereNull($q . '.datetimeremoved')
						->whereNull($s . '.datetimeremoved')
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

						if ($event->noStatus()
						 || $event->isPendingRemoval())
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
						->whereNull($qu . '.datetimeremoved')
						->whereNull($q . '.datetimeremoved')
						->whereNull($s . '.datetimeremoved')
						->get();

					// Prepare and send actual email
					$message = new FreeRemoved($user, $removing, $keeping, $removals[$userid]);
					$message->headers()->text([
						'X-Command' => 'queues:emailfreeremoved',
						'X-Target-Object' => $groupid
					]);

					if ($this->output->isDebug())
					{
						echo $message->render();
					}

					if ($debug || $this->output->isVerbose())
					{
						$this->info("Emailed freeremoved to {$user->email}.");

						if ($debug)
						{
							continue;
						}
					}

					if ($user->email)
					{
						Mail::to($user->email)->send($message);
					}
					else
					{
						if ($debug || $this->output->isVerbose())
						{
							$this->error("Email address not found for user {$user->name}.");
						}
					}

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
					$message->headers()->text([
						'X-Command' => 'queues:emailfreeremoved',
						'X-Target-Object' => $groupid
					]);

					if ($this->output->isDebug())
					{
						echo $message->render();
					}

					if ($debug || $this->output->isVerbose())
					{
						$this->info("Emailed freeremoved to manager {$manager->user->email}.");

						if ($debug)
						{
							continue;
						}
					}

					if (!$manager->user->email)
					{
						if ($debug || $this->output->isVerbose())
						{
							$this->error("Email address not found for user {$manager->user->name}.");
						}
						continue;
					}

					Mail::to($manager->user->email)->send($message);
				}
			}
		}
	}
}
