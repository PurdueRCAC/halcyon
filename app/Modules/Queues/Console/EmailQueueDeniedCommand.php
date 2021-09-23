<?php

namespace App\Modules\Queues\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Modules\History\Models\Log;
use App\Modules\Queues\Mail\QueueDenied;
use App\Modules\Queues\Mail\QueueDeniedManager;
use App\Modules\Queues\Models\Queue;
use App\Modules\Queues\Models\User as QueueUser;
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
	public function handle()
	{
		$debug = $this->option('debug') ? true : false;

		$qu = (new QueueUser)->getTable();
		$q = (new Queue)->getTable();

		$users = QueueUser::query()
			->select($qu . '.*', $q . '.groupid')
			->join($q, $q . '.id', $qu . '.queueid')
			->whereIn($qu . '.membertype', [1, 4])
			->where($qu . '.notice', '=', 12)
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

					Mail::to($user->email)->send($message);

					$this->log($user->id, $user->email, "Emailed queuedenied.");

					// Change states
					foreach ($queueusers as $queueuser)
					{
						$queueuser->update(['notice' => 0]);
					}
				}

				// Assemble list of managers to email
				foreach ($group->managers as $manager)
				{
					// Prepare and send actual email
					$message = new QueueDeniedManager($manager->user, $data);

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

					Mail::to($manager->user->email)->send($message);

					$this->log($manager->user->id, $manager->user->email, "Emailed queuedenied to manager.");
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
			'classname'       => Str::limit('queues:emailqueuedenied', 32, ''),
			'classmethod'     => Str::limit('handle', 16, ''),
			'targetuserid'    => $targetuserid,
		]);
	}
}
