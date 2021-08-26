<?php

namespace App\Modules\Queues\Console;

use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Modules\History\Models\Log;
use App\Modules\Queues\Models\Queue;
use App\Modules\Queues\Models\User as QueueUser;
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
			->whereIn($qu . '.membertype', [1, 4])
			->where($qu . '.notice', '=', 6)
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

		foreach ($group_activity as $groupid => $users)
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

			if (!count($group->managers))
			{
				if ($debug)
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
						if ($debug)
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

					if ($debug)
					{
						//$this->info("Emailed queuerequested to {$manager->user->email}.");
						echo $message->render();
						continue;
					}

					Mail::to($manager->user->email)->send($message);

					$this->log($manager->user->id, $manager->user->email, "Emailed queue requested.");
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
			'classname'       => Str::limit('queues:emailqueuerequested', 32, ''),
			'classmethod'     => Str::limit('handle', 16, ''),
			'targetuserid'    => $targetuserid,
		]);
	}
}
