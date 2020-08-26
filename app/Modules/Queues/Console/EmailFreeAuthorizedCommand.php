<?php

namespace App\Modules\Queues\Console;

use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Modules\Queues\Mail\FreeAuthorized;
use App\Modules\Queues\Mail\FreeAuthorizedManager;
use App\Modules\Queues\Models\Queue;
use App\Modules\Queues\Models\GroupUser;
use App\Modules\Queues\Models\User as QueueUser;
use App\Modules\Users\Models\User;
use App\Modules\Groups\Models\Group;
use App\Modules\Resources\Events\ResourceMemberstatus;

/**
 * This script proccess all new authorized groupqueueuser entries
 * Notice State 2 => 8
 */
class EmailFreeAuthorizedCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	//protected $name = 'queues:emailfreeauthorized';

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

		$users = GroupUser::query()
			->select($gu . '.*', $qu . '.queueid')
			->join($qu, $qu . '.id', $gu . '.queueuserid')
			->join($q, $q . '.id', $qu . '.queueid')
			->whereIn($qu . '.membertype', [1, 4])
			->where($qu . '.notice', '=', 2)
			->get();

		if (!count($users))
		{
			$this->comment('No records to email.');
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

		foreach ($group_activity as $group)
		{
			$this->info("Starting processing group ID #{$group}.");

			// Find the latest activity
			$latest = 0;
			foreach ($group as $g)
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
				$student_activity = array();

				foreach ($group as $student)
				{
					if (!isset($student_activity[$student->userid]))
					{
						$student_activity[$student->userid] = array();
					}

					array_push($student_activity[$student->userid], $student);
				}

				// Send email to each student
				$roles = array();
				foreach ($student_activity as $userid => $queueusers)
				{
					$user = User::find($userid);

					$roles[$userid] = array();
					$last_role = '';

					foreach ($queueusers as $queueuser)
					{
						$role = $queueuser->queue->resource->rolename;

						if ($role == $last_role)
						{
							continue; // skip, we already checked this role
						}

						$last_role = $role;

						// Contact role provision service
						event($event = new ResourceMemberstatus($queueuser->queue->resource, $user));

						if ($event->status != 3) // ROLE_ACCOUNTS_READY
						{
							array_push($roles[$userid], $queueuser->queue->resource); //$last_role);
						}
					}

					// Prepare and send actual email
					$message = new FreeAuthorized($user, $queueusers, $roles);

					if ($debug)
					{
						echo $message->render();
						continue;
					}

					//Mail::to($user->email)->send($message);

					$this->info("Emailed freeauthorized to {$user->email}.");

					$r = collect($roles[$userid])->pluck('rolename')->toArray();

					// Change states
					foreach ($queueusers as $queueuser)
					{
						// Determine which state to go to, depending on whether a new role was created
						$rolename = $queueuser->queue->resource->rolename;

						if (in_array($rolename, $r))
						{
							$queueuser->notice = 8;
						}
						else
						{
							$queueuser->notice = 0;
						}

						$queueuser->save();
					}
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
						continue;
					}

					//Mail::to($manager->user->email)->send($message);

					$this->info("Emailed freeauthorized to manager {$manager->user->email}.");
				}
			}

			$this->info("Finished processing group ID #{$group}.");
		}
	}
}
