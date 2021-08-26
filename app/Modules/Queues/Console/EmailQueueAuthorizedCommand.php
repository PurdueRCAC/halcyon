<?php

namespace App\Modules\Queues\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Modules\History\Models\Log;
use App\Modules\Queues\Models\Queue;
use App\Modules\Queues\Models\User as QueueUser;
use App\Modules\Queues\Mail\QueueAuthorized;
use App\Modules\Queues\Mail\QueueAuthorizedManager;
use App\Modules\Users\Models\User;
use App\Modules\Groups\Models\Group;
use App\Modules\Resources\Events\ResourceMemberStatus;

class EmailQueueAuthorizedCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'queues:emailqueueauthorized {--debug : Output emails rather than sending}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Email authorized queue access requests.';

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
			->where($qu . '.notice', '=', 2)
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

		foreach ($group_activity as $groupid => $groupqueueusers)
		{
			if ($debug)
			{
				$this->info("Starting processing group ID #{$groupid}.");
			}

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
				$roles = array();
				$data = array();
				foreach ($student_activity as $userid => $queueusers)
				{
					$user = User::find($userid);

					if (!$user)
					{
						$this->error('Could not find account for user #' . $userid);
						continue;
					}

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
						event($event = new ResourceMemberStatus($queueuser->queue->resource, $user));

						if ($event->status != 3) // ROLE_ACCOUNTS_READY
						{
							array_push($roles[$userid], $queueuser->queue->resource); //$last_role);
						}
					}

					$data[$userid] = array(
						'user'       => $user,
						'queueusers' => $queueusers,
						'roles'      => $roles[$userid]
					);

					// Prepare and send actual email
					$message = new QueueAuthorized($user, $queueusers, $roles[$userid]);

					if ($debug)
					{
						echo $message->render();
						$this->info("Emailed freeauthorized to {$manager->user->email}.");
						continue;
					}

					Mail::to($user->email)->send($message);

					$this->log($user->id, $user->email, "Emailed freeauthorized.");

					$r = collect($roles[$userid])->pluck('rolename')->toArray();

					// Change states
					foreach ($queueusers as $queueuser)
					{
						// Determine which state to go to, depending on whether a new role was created
						$rolename = $queueuser->queue->resource->rolename;

						$notice = 0;
						if (in_array($rolename, $r))
						{
							$notice = 8;
						}

						$queueuser->update(['notice' => $notice]);
					}
				}

				// Assemble list of managers to email
				foreach ($group->managers as $manager)
				{
					// Prepare and send actual email
					$message = new QueueAuthorizedManager($manager->user, $data);

					if ($debug)
					{
						echo $message->render();
						$this->info("Emailed freeauthorized to manager {$manager->user->email}.");
						continue;
					}

					Mail::to($manager->user->email)->send($message);

					$this->log($manager->user->id, $manager->user->email, "Emailed freeauthorized to manager.");
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
			'classname'       => Str::limit('queues:emailqueueauthorized', 32, ''),
			'classmethod'     => Str::limit('handle', 16, ''),
			'targetuserid'    => $targetuserid,
		]);
	}
}
