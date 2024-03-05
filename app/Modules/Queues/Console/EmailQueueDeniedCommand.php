<?php

namespace App\Modules\Queues\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Modules\Queues\Mail\QueueDenied;
use App\Modules\Queues\Mail\QueueDeniedManager;
use App\Modules\Queues\Models\Queue;
use App\Modules\Queues\Models\User as QueueUser;
use App\Modules\Queues\Models\MemberType;
use App\Modules\Users\Models\User;
use App\Modules\Groups\Models\Group;

/**
 * This script proccess all newly denied queueuser entries
 * Notice State 12 => 0
 */
class EmailQueueDeniedCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'queues:emailqueuedenied {--debug : Output emails rather than sending}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Email denied queue access requests.';

	/**
	 * Execute the console command.
	 */
	public function handle(): void
	{
		$debug = $this->option('debug') ? true : false;

		$qu = (new QueueUser)->getTable();
		$q = (new Queue)->getTable();

		$users = QueueUser::query()
			->select($qu . '.*', $q . '.groupid')
			->join($q, $q . '.id', $qu . '.queueid')
			->whereIn($qu . '.membertype', [MemberType::MEMBER, MemberType::PENDING])
			->where($qu . '.notice', '=', QueueUser::NOTICE_REQUEST_DENIED)
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

		foreach ($group_activity as $groupid => $users)
		{
			if ($debug || $this->output->isVerbose())
			{
				$this->info("Starting processing group ID #{$groupid}.");
			}

			if (!count($users))
			{
				continue;
			}

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
					if ($debug || $this->output->isVerbose())
					{
						$this->error('Could not find group #' . $groupid);
					}
					continue;
				}

				// Condense students
				$user_activity = array();

				foreach ($users as $student)
				{
					if (!isset($user_activity[$student->userid]))
					{
						$user_activity[$student->userid] = array();
					}

					array_push($user_activity[$student->userid], $student);
				}

				// Send email to each student
				$data = array();
				foreach ($user_activity as $userid => $queueusers)
				{
					$user = User::find($userid);

					if (!$user)
					{
						if ($debug || $this->output->isVerbose())
						{
							$this->error('Could not find account for user #' . $userid);
						}
						continue;
					}

					$data[$userid] = array(
						'user'       => $user,
						'queueusers' => $queueusers,
					);

					$message = new QueueDenied($user, $queueusers);
					$message->headers()->text([
						'X-Command' => 'queues:emailqueuedenied',
						'X-Target-Object' => $groupid
					]);

					if ($this->output->isDebug())
					{
						echo $message->render();
					}

					if ($debug || $this->output->isVerbose())
					{
						$this->info("Emailed queuedenied to {$user->email}.");

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

					// Change states
					foreach ($queueusers as $queueuser)
					{
						$queueuser->update(['notice' => QueueUser::NO_NOTICE]);
					}
				}

				// Assemble list of managers to email
				foreach ($group->managers as $manager)
				{
					// Prepare and send actual email
					$message = new QueueDeniedManager($manager->user, $data);
					$message->headers()->text([
						'X-Command' => 'queues:emailqueuedenied',
						'X-Target-Object' => $groupid
					]);

					if ($this->output->isDebug())
					{
						echo $message->render();
					}

					if ($debug || $this->output->isVerbose())
					{
						$this->info("Emailed queuedenied to manager {$manager->user->email}.");

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
