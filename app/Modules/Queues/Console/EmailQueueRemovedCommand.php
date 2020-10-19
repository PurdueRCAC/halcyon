<?php

namespace App\Modules\Queues\Console;

use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Modules\Queues\Mail\QueueRemoved;
use App\Modules\Queues\Mail\QueueRemovedManager;
use App\Modules\Queues\Models\Queue;
use App\Modules\Queues\Models\User as QueueUser;
use App\Modules\Queues\Models\Scheduler;
use App\Modules\Users\Models\User;
use App\Modules\Groups\Models\Group;

class EmailQueueRemovedCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'queues:emailqueueremoved {--debug : Output emails rather than sending}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Email queue access removals.';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		$debug = $this->option('debug') ? true : false;

		$qu = (new QueueUser)->getTable();
		$q = (new Queue)->getTable();
		$s = (new Scheduler)->getTable();

		$users = QueueUser::query()
			->select($qu . '.*', $q . '.groupid')
			->join($q, $q . '.id', $qu . '.queueid')
			->whereIn($qu . '.membertype', [1, 4])
			->where($qu . '.notice', '=', 3)
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
					$this->error('Could not find group #' . $groupid);
					continue;
				}

				// Condense students
				$student_activity = array();

				foreach ($groupqueueusers as $student)
				{
					if (!isset($student_activity[$student->userid]))
					{
						$student_activity[$student->userid] = array();
					}

					array_push($student_activity[$student->userid], $student);
				}

				// Send email to each student
				//$roles = array();
				$data = array();
				$removals = array();
				foreach ($student_activity as $userid => $queuestudents)
				{
					// Start assembling email
					$user = User::find($userid);

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

					$removing = collect($queuestudents)->whereIn('queueid', $existing);

					// Is anything actually being removed?
					if (!count($removing))
					{
						continue;
					}

					// Determine if any roles are being removed
					$last_role = '';
					$removals[$userid] = array();
					foreach ($queuestudents as $queueuser)
					{
						$role = $queueuser->queue->resource->rolename;

						if ($role == $last_role)
						{
							continue; // skip, we already checked this role
						}

						$last_role = $role;

						// Contact role provision service
						event($event = new ResourceMemberstatus($queueuser->queue->resource, $user));

						if ($event->status == 1  // ROLE_REMOVAL_PENDING
						 || $event->status == 4) // NO_ROLE_EXISTS
						{
							array_push($removals[$userid], $queueuser->queue->resource); //$last_role);
						}
					}

					$data[$userid] = array(
						'user'       => $user,
						'queueusers' => $queuestudents,
					);

					// Prepare and send actual email
					$message = new QueueRemoved($user, $removing, $keeping, $removals[$userid]);

					if ($debug)
					{
						echo $message->render();
						continue;
					}

					Mail::to($user->email)->send($message);

					$this->info("Emailed queueremoved to {$user->email}.");
				}

				// Email group managers
				foreach ($group->managers as $manager)
				{
					// Prepare and send actual email
					$message = new QueueRemovedManager($manager->user, $data);

					if ($debug)
					{
						echo $message->render();
						continue;
					}

					Mail::to($manager->user->email)->send($message);

					$this->info("Emailed queueremoved to manager {$manager->user->email}.");
				}
			}
		}
	}
}
