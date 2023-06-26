<?php

namespace App\Modules\Queues\Console;

use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Modules\Queues\Models\Queue;
use App\Modules\Queues\Models\User as QueueUser;
use App\Modules\Queues\Models\MemberType;
use App\Modules\Queues\Mail\QueueRequested;
use App\Modules\Users\Models\User;
use App\Modules\Groups\Models\Group;

class EmailQueueRequestedCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'queues:emailqueuerequested {--debug : Output emails rather than sending}';

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
		$debug = $this->option('debug') ? true : false;

		$qu = (new QueueUser)->getTable();
		$q = (new Queue)->getTable();

		$users = QueueUser::query()
			->select($qu . '.*', $q . '.groupid')
			->join($q, $q . '.id', $qu . '.queueid')
			->whereIn($qu . '.membertype', [MemberType::MEMBER, MemberType::PENDING])
			->where($qu . '.notice', '=', QueueUser::NOTICE_REQUESTED)
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
			$group = Group::find($groupid);

			if (!$group)
			{
				if ($debug || $this->output->isVerbose())
				{
					$this->error('Could not find group #' . $groupid);
				}
				continue;
			}

			if (!count($group->managers))
			{
				if ($debug || $this->output->isVerbose())
				{
					$this->error('No active managers found for group #' . $groupid);
				}
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
				$user_activity = array();
				foreach ($users as $user)
				{
					if (!isset($user_activity[$user->userid]))
					{
						$user_activity[$user->userid] = array();
					}
					array_push($user_activity[$user->userid], $user);
				}

				foreach ($user_activity as $userid => $activity)
				{
					$user = User::find($userid);

					if (!$user)
					{
						unset($user_activity[$userid]);
						if ($debug || $this->output->isVerbose())
						{
							$this->error('Could not find account for user #' . $userid);
						}
						continue;
					}

					$user_activity[$userid] = array(
						'user' => $user,
						'queueusers' => $activity,
					);
				}

				// Assemble list of managers to email
				foreach ($group->managers as $manager)
				{
					// Prepare and send actual email
					$message = new QueueRequested($manager->user, $user_activity);
					$message->headers()->text([
						'X-Command' => 'queues:emailqueuerequested',
						'X-Target-Object' => $groupid
					]);

					if ($this->output->isDebug())
					{
						echo $message->render();
					}

					if ($debug || $this->output->isVerbose())
					{
						$this->info("Emailed queuerequested to {$manager->user->email}.");

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

				if (!$debug)
				{
					foreach ($user_activity as $userid => $activity)
					{
						// Change states
						foreach ($activity['queueusers'] as $queueuser)
						{
							$queueuser->update(['notice' => 0]);
						}
					}
				}
			}
		}
	}
}
