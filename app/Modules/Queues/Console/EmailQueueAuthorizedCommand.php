<?php

namespace App\Modules\Queues\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Modules\Queues\Models\Queue;
use App\Modules\Queues\Models\User as QueueUser;
use App\Modules\Queues\Models\MemberType;
use App\Modules\Queues\Mail\QueueAuthorized;
use App\Modules\Queues\Mail\QueueAuthorizedManager;
use App\Modules\Users\Models\User;
use App\Modules\Groups\Models\Group;
use App\Modules\Groups\Models\UnixGroup;
use App\Modules\Groups\Models\UnixGroupMember;
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
			->whereIn($qu . '.membertype', [MemberType::MEMBER, MemberType::PENDING])
			->where($qu . '.notice', '=', QueueUser::NOTICE_REQUEST_GRANTED)
			->get();

		$uu = (new UnixGroupMember)->getTable();
		$u = (new UnixGroup)->getTable();

		$uusers = UnixGroupMember::query()
			->select($uu . '.*', $u . '.groupid')
			->join($u, $u . '.id', $uu . '.unixgroupid')
			->where($uu . '.notice', '=', UnixGroupMember::NOTICE_AUTHORIZED)
			->get();

		if (!count($users) && !count($uusers))
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

		foreach ($uusers as $user)
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
			if ($debug || $this->output->isVerbose())
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
						if ($debug || $this->output->isVerbose())
						{
							$this->error('Could not find account for user #' . $userid);
						}
						continue;
					}

					$roles[$userid] = array();
					$last_role = '';

					foreach ($queueusers as $queueuser)
					{
						if (!$queueuser->queue)
						{
							continue;
						}

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
					$message->headers()->text([
						'X-Command' => 'queues:emailqueueauthorized',
						'X-Target-Object' => $groupid
					]);

					if ($this->output->isDebug())
					{
						echo $message->render();
					}

					if ($debug || $this->output->isVerbose())
					{
						$this->info("Emailed queue authorized to {$user->email}.");

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

					$r = collect($roles[$userid])->pluck('rolename')->toArray();

					// Change states
					foreach ($queueusers as $queueuser)
					{
						$notice = QueueUser::NO_NOTICE;

						// Determine which state to go to, depending on whether a new role was created
						if ($queueuser->queue)
						{
							$rolename = $queueuser->queue->resource->rolename;

							if (in_array($rolename, $r))
							{
								$notice = QueueUser::NOTICE_WELCOME;
							}
						}

						$queueuser->update(['notice' => $notice]);
					}
				}

				// Assemble list of managers to email
				foreach ($group->managers as $manager)
				{
					// Prepare and send actual email
					$message = new QueueAuthorizedManager($manager->user, $data);
					$message->headers()->text([
						'X-Command' => 'queues:emailqueueauthorized',
						'X-Target-Object' => $groupid
					]);

					if ($this->output->isDebug())
					{
						echo $message->render();
					}

					if ($debug || $this->output->isVerbose())
					{
						$this->info("Emailed queue authorized to manager {$manager->user->email}.");

						if ($debug)
						{
							continue;
						}
					}

					if (!$manager->user->email)
					{
						if ($debug || $this->output->isVerbose())
						{
							$this->error("Email address not found for manager {$manager->user->name}.");
						}
						continue;
					}

					Mail::to($manager->user->email)->send($message);
				}
			}
		}
	}
}
