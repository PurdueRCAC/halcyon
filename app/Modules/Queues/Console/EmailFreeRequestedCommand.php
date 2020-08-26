<?php

namespace App\Modules\Queues\Console;

use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Modules\Queues\Mail\FreeRequested;
use App\Modules\Queues\Models\Queue;
use App\Modules\Queues\Models\GroupUser;
use App\Modules\Queues\Models\User as QueueUser;
use App\Modules\Users\Models\User;
use App\Modules\Groups\Models\Group;

/**
 * This script proccess all new requested groupqueueuser entries
 * Notice State 6 => 0
 */
class EmailFreeRequestedCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	//protected $name = 'queues:emailfreerequested';

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'queues:emailfreerequested {--debug : Output emails rather than sending}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Email latest groupqueueuser requests.';

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
			->where($qu . '.notice', '=', 6)
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
				$group = Group::find($groupid);

				if (!$group)
				{
					$this->error('Could not find group #' . $groupid);
					continue;
				}

				$student_activity = array();
				foreach ($users as $user)
				{
					if (!isset($user_activity[$user->userid]))
					{
						$user_activity[$user->userid] = array();
					}
					array_push($user_activity[$usert->userid], $user);
				}

				foreach ($user_activity as $userid => $activity)
				{
					// Change states
					foreach ($activity as $queueuser)
					{
						$queueuser->notice = 0;
						$queueuser->save();
					}
				}

				// Assemble list of managers to email
				foreach ($group->managers as $manager)
				{
					// Prepare and send actual email
					$message = new FreeRequested($manager->user, $user_activity);

					if ($debug)
					{
						echo $message->render();
						continue;
					}

					Mail::to($manager->user->email)->send($message);

					$this->info("Emailed freerequested to manager {$manager->user->email}.");
				}
			}
		}
	}
}
