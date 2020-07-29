<?php

namespace App\Modules\Queues\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Support\Facades\Mail;
use App\Modules\Queues\Models\Queue;
use App\Modules\ContactReports\Models\Report;
use App\Modules\ContactReports\Mail\NewComment;
use App\Modules\Users\Models\User;

class EmailQueueRequestedCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'queues:emailrequested';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Email latest queue requests.';

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
			->where($u . '.notice', '=', 0)
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

		foreach ($group_activity as $groupid => $users)
		{
			// Find the latest activity
			$latest = 0;
			foreach ($users as $user)
			{
				if ($user->datetimecreated->format('U') > $latest)
				{
					$latest = $user->datetimecreated->format('U');
				}
			}

			if ($now - $latest >= $threshold)
			{
				$student_activity = array();
				foreach ($users as $user)
				{
					if (!isset($user_activity[$user->userid]))
					{
						$user_activity[$user->userid] = array();
					}
					array_push($user_activity[$usert->userid], $user);
				}

				// Assemble list of managers to email
				$managers = User::query()
					->where('membertype', '=', 2)
					->where('groupid', '=', $groupid)
					->get();

				foreach ($managers as $manager)
				{
					// Prepare and send actual email
					//Mail::to($manager->user->email)->send(new QueueRequested($user_activity));
					echo (new QueueRequested($user_activity))->render();

					$this->info("Emailed queuerequested to {$manager->user->email}.");
				}
			}
		}
	}
}
