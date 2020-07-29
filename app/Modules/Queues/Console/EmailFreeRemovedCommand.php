<?php

namespace App\Modules\Queues\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Support\Facades\Mail;
use App\Modules\Queues\Models\Queue;
use App\Modules\ContactReports\Models\Report;
use App\Modules\ContactReports\Mail\NewComment;
use App\Modules\Users\Models\User;

class EmailFreeRemovedCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'queues:emailfreeremoved';

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
		$u = (new User)->getTable();
		$q = (new Queue)->getTable();

		$users = User::query()
			->select($u . '.*', $q . '.groupid')
			->join($q, $q . '.id', $u . '.queueid')
			->whereIn($u . '.membertype', [1, 4])
			->where($u . '.notice', '=', 2)
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

		foreach ($group_activity as $group)
		{
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
				foreach ($student_activity as $student)
				{
					$roles[$student[0]->userid] = array();
					$last_role = '';

					foreach ($student as $queue)
					{
						if (!isset($queue->unixgroupid))
						{
							$role = $db->getQueueRole($queue->queueid);

							if ($role == $last_role)
							{
								continue; // skip, we already checked this role
							}

							$last_role = $role;

							// Contact role provision service
							$role_output = "";
							$role = new roleprovision();
							$url = "getRoleStatus/rcs/" . $last_role . "/" . $vars['student']->username;
							/*$return = $role->get($url, $role_output);

							if ($return >= 401) {
								die("An error occurred while assembling email.\n");
							}*/

							$role_output = json_decode($role_output);

							if ($role_output->roleStatus != "ROLE_ACCOUNTS_READY") {
								array_push($roles[$student[0]->userid], $last_role);
							}
						}
					}
					// Prepare and send actual email
					//Mail::to($user->email)->send(new QueueAuthorized($user));
					echo (new QueueAuthorized($user))->render();

					$this->info("Emailed queueauthorized to {$user->email}.");
				}
			}

			// Change states
			foreach ($comments as $comment)
			{
				$comment->notice = 0;
				$comment->save();
			}
		}
	}
}
