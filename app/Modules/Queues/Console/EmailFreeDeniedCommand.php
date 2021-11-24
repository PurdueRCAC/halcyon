<?php

namespace App\Modules\Queues\Console;

use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Modules\History\Models\Log;
use App\Modules\Queues\Mail\FreeDenied;
use App\Modules\Queues\Mail\FreeDeniedManager;
use App\Modules\Queues\Models\Queue;
use App\Modules\Queues\Models\GroupUser;
use App\Modules\Queues\Models\User as QueueUser;
use App\Modules\Users\Models\User;
use App\Modules\Groups\Models\Group;

/**
 * This script proccess all newly denied groupqueueuser entries
 * Notice State 12 => 0
 */
class EmailFreeDeniedCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'queues:emailfreedenied {--debug : Output emails rather than sending}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Email latest groupqueueuser denials.';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		$debug = $this->option('debug') ? true : false;

		$gu = (new GroupUser)->getTable();
		$qu = (new QueueUser)->getTable();
		$q = (new Queue)->getTable();

		$groupqueueusers = GroupUser::query()
			->select($gu . '.*', $qu . '.queueid')
			->join($qu, $qu . '.id', $gu . '.queueuserid')
			->join($q, $q . '.id', $qu . '.queueid')
			->withTrashed()
			->whereIn($qu . '.membertype', [1, 4])
			->where($qu . '.notice', '=', 12)
			->get();

		if (!count($groupqueueusers))
		{
			if ($debug || $this->output->isVerbose())
			{
				$this->comment('No records to email.');
			}
			return;
		}

		// Group activity by groupid so we can determine when to send the group mail
		$group_activity = array();

		foreach ($groupqueueusers as $groupqueueuser)
		{
			if (!isset($group_activity[$groupqueueuser->groupid]))
			{
				$group_activity[$groupqueueuser->groupid] = array();
			}

			array_push($group_activity[$groupqueueuser->groupid], $groupqueueuser);
		}

		$now = date("U");
		$threshold = 300; // threshold for when considering activity "done"

		foreach ($group_activity as $groupid => $groupqueueusers)
		{
			// Find the latest activity
			$latest = 0;
			foreach ($groupqueueusers as $groupqueueuser)
			{
				if ($groupqueueuser->datetimecreated->format('U') > $latest)
				{
					$latest = $groupqueueuser->datetimecreated->format('U');
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

				$user_activity = array();

				foreach ($groupqueueusers as $gquser)
				{
					$queueuser = $gquser->queueuser;

					if (!isset($user_activity[$queueuser->userid]))
					{
						$user_activity[$queueuser->userid] = array();
					}

					array_push($user_activity[$queueuser->userid], $gquser);
				}

				// Send email to each student
				$data = array();
				foreach ($user_activity as $userid => $queueusers)
				{
					$user = User::find($userid);

					if (!$user || !$user->id || $user->trashed())
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

					$message = new FreeDenied($user, $queueusers);

					if ($this->output->isDebug())
					{
						echo $message->render();
					}

					if ($debug || $this->output->isVerbose())
					{
						$this->info("Emailed freedenied to {$user->email}.");

						if ($debug)
						{
							continue;
						}
					}

					if ($user->email)
					{
						Mail::to($user->email)->send($message);

						$this->log($user->id, $groupid, $user->email, 'Emailed freedenied.');
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
						$queueuser->update(['notice' => 0]);
					}
				}

				// Assemble list of managers to email
				foreach ($group->managers as $manager)
				{
					$user = $manager->user;

					if (!$user || !$user->id || $user->trashed())
					{
						if ($debug || $this->output->isVerbose())
						{
							$this->error('Could not find account for user #' . $manager->userid);
						}
						continue;
					}

					// Prepare and send actual email
					$message = new FreeDeniedManager($user, $data);

					if ($this->output->isDebug())
					{
						echo $message->render();
					}

					if ($debug || $this->output->isVerbose())
					{
						$this->info("Emailed freedenied to manager {$user->email}.");

						if ($debug)
						{
							continue;
						}
					}

					if (!$user->email)
					{
						if ($debug || $this->output->isVerbose())
						{
							$this->error("Email address not found for user {$user->name}.");
						}
						continue;
					}

					Mail::to($user->email)->send($message);

					$this->log($user->id, $groupid, $user->email, 'Emailed freedenied to manager.');
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
	protected function log($targetuserid, $targetobjectid, $uri = '', $payload = '')
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
			'classname'       => Str::limit('queues:emailfreedenied', 32, ''),
			'classmethod'     => Str::limit('handle', 16, ''),
			'targetuserid'    => (int)$targetuserid,
			'targetobjectid'  => (int)$targetobjectid,
			'objectid'        => (int)$targetobjectid,
		]);
	}
}
