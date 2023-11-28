<?php

namespace App\Modules\Queues\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Modules\Queues\Mail\FreeAuthorized;
use App\Modules\Queues\Mail\FreeAuthorizedManager;
use App\Modules\Queues\Models\Queue;
use App\Modules\Queues\Models\GroupUser;
use App\Modules\Queues\Models\User as QueueUser;
use App\Modules\Queues\Models\MemberType;
use App\Modules\Users\Models\User;
use App\Modules\Groups\Models\Group;
use App\Modules\Resources\Events\ResourceMemberStatus;

/**
 * This script proccess all new authorized groupqueueuser entries
 * Notice State 2 => 8
 */
class EmailFreeAuthorizedCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'queues:emailfreeauthorized {--debug : Output emails rather than sending}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Email latest authorized groupqueueuser entries.';

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
			->whereIn($qu . '.membertype', [MemberType::MEMBER, MemberType::PENDING])
			->where($qu . '.notice', '=', QueueUser::NOTICE_REQUEST_GRANTED)
			->whereNull($qu . '.datetimeremoved')
			->whereNull($gu . '.datetimeremoved')
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
			if ($debug || $this->output->isVerbose())
			{
				$this->info("Processing group ID #{$groupid}...");
			}

			if (!count($groupqueueusers))
			{
				continue;
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
				// Email everyone involved in this group

				// Condense students
				$user_activity = array();

				foreach ($groupqueueusers as $gquser)
				{
					$queueuser = $gquser->queueuser;

					if (!isset($user_activity[$queueuser->userid]))
					{
						$user_activity[$queueuser->userid] = array();
					}

					array_push($user_activity[$queueuser->userid], $queueuser);
				}

				// Send email to each student
				$roles = array();
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

					$roles[$userid] = array();
					$last_role = '';

					foreach ($queueusers as $queueuser)
					{
						$queue = $queueuser->queue()->withTrashed()->first();

						// Queue was removed
						if (!$queueuser->queue)
						{
							continue;
						}

						$resource = $queue->resource()->withTrashed()->first();

						// Resource was removed
						if (!$resource)
						{
							continue;
						}

						$role = $resource->rolename;

						if ($role == $last_role)
						{
							continue; // skip, we already checked this role
						}

						$last_role = $role;

						// Contact role provision service
						event($event = new ResourceMemberStatus($resource, $user));

						if ($event->status != 3) // ROLE_ACCOUNTS_READY
						{
							echo $resource->id . "\n";
							array_push($roles[$userid], $resource);
						}
					}

					$data[$userid] = array(
						'user'       => $user,
						'queueusers' => $queueusers,
						'roles'      => $roles[$userid]
					);

					// Prepare and send actual email
					$message = new FreeAuthorized($user, $queueusers, $roles[$userid]);
					$message->headers()->text([
						'X-Command' => 'queues:emailfreeauthorized',
						'X-Target-Object' => $groupid
					]);

					if ($this->output->isDebug())
					{
						echo $message->render();
					}

					if ($debug || $this->output->isVerbose())
					{
						$this->info("Emailed freeauthorized to {$user->email}.");

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
						// Determine which state to go to, depending on whether a new role was created
						$rolename = $queueuser->queue->resource->rolename;

						$notice = QueueUser::NO_NOTICE;
						if (in_array($rolename, $r))
						{
							$notice = QueueUser::NOTICE_WELCOME;
						}

						$queueuser->update(['notice' => $notice]);
					}
				}

				if (!count($data))
				{
					continue;
				}

				// Assemble list of managers to email
				$group = Group::find($groupid);

				foreach ($group->managers as $manager)
				{
					$user = $manager->user;

					if (!$user || !$user->id || $user->trashed())
					{
						continue;
					}

					// Prepare and send actual email
					$message = new FreeAuthorizedManager($manager->user, $data);
					$message->headers()->text([
						'X-Command' => 'queues:emailfreeauthorized',
						'X-Target-Object' => $groupid
					]);

					if ($this->output->isDebug())
					{
						echo $message->render();
					}

					if ($debug || $this->output->isVerbose())
					{
						$this->info("Emailed freeauthorized to manager {$user->email}.");

						if ($debug)
						{
							continue;
						}
					}

					if (!$user->email)
					{
						if ($debug || $this->output->isVerbose())
						{
							$this->error("Email address not found for manager {$user->name}.");
						}
						continue;
					}

					Mail::to($user->email)->send($message);
				}
			}
		}
	}
}
