<?php

namespace App\Modules\Queues\Console;

use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Modules\Queues\Mail\QueueRemoved;
use App\Modules\Queues\Mail\QueueRemovedManager;
use App\Modules\Queues\Models\Queue;
use App\Modules\Queues\Models\User as QueueUser;
use App\Modules\Queues\Models\Scheduler;
use App\Modules\Queues\Models\MemberType;
use App\Modules\Groups\Models\Group;
use App\Modules\Groups\Models\UnixGroup;
use App\Modules\Groups\Models\UnixGroupMember;
use App\Modules\Users\Models\User;
use App\Modules\Resources\Events\ResourceMemberStatus;

class EmailQueueRemovedCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'queues:emailqueueremoved {--debug : Output emails rather than sending}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Email queue access removals.';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		$debug = $this->option('debug') ? true : false;

		$qu = (new QueueUser)->getTable();
		$q = (new Queue)->getTable();
		$s = (new Scheduler)->getTable();

		$users = QueueUser::query()
			->onlyTrashed()
			->select($qu . '.*', $q . '.groupid')
			->join($q, $q . '.id', $qu . '.queueid')
			->whereIn($qu . '.membertype', [MemberType::MEMBER, MemberType::PENDING])
			->where($qu . '.notice', '=', QueueUser::NOTICE_REMOVED)
			->get();

		$uu = (new UnixGroupMember)->getTable();
		$u = (new UnixGroup)->getTable();

		$uusers = UnixGroupMember::query()
			->onlyTrashed()
			->select($uu . '.*', $u . '.groupid')
			->join($u, $u . '.id', $uu . '.unixgroupid')
			->where($uu . '.notice', '=', 3)
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

		$num = 0;
		foreach ($users as $user)
		{
			if (!isset($group_activity[$user->groupid]))
			{
				$group_activity[$user->groupid] = array();
			}

			array_push($group_activity[$user->groupid], $user);
			$num++;
		}

		foreach ($uusers as $user)
		{
			if (!isset($group_activity[$user->groupid]))
			{
				$group_activity[$user->groupid] = array();
			}

			array_push($group_activity[$user->groupid], $user);
			$num++;
		}

		$now = date("U");
		$threshold = 300; // threshold for when considering activity "done"

		if ($debug || $this->output->isVerbose())
		{
			$this->comment('Found ' . $num . ' records.');
		}

		foreach ($group_activity as $groupid => $groupqueueusers)
		{
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
					if ($debug || $this->output->isVerbose())
					{
						$this->error('Could not find group #' . $groupid);
					}
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
				$data = array();
				$removals = array();
				foreach ($student_activity as $userid => $queuestudents)
				{
					// Start assembling email
					$user = User::find($userid);

					if (!$user)
					{
						if ($debug || $this->output->isVerbose())
						{
							$this->error('Could not find account for user #' . $userid);
						}
						continue;
					}

					/*$existing = QueueUser::query()
						//->withTrashed()
						->join($q, $q . '.id', $qu . '.queueid')
						->join($s, $s . '.id', $q . '.schedulerid')
						->where($qu . '.membertype', '=', 1)
						->where($qu . '.userid', '=', $userid)
						->where($qu . '.notice', '<>', 6)
						//->whereNull($qu . '.datetimeremoved')
						->whereNull($q . '.datetimeremoved')
						->whereNull($s . '.datetimeremoved')
						->get()
						->pluck('queueid')
						->toArray();

					$removing = collect($queuestudents)->whereIn('queueid', $existing);

					// Is anything actually being removed?
					if (!count($removing))
					{
						continue;
					}*/
					$removing = collect($queuestudents);

					// Determine if any roles are being removed
					$last_role = '';
					$removals[$userid] = array();
					foreach ($queuestudents as $queueuser)
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
						event($event = new ResourceMemberstatus($queueuser->queue->resource, $user));

						if ($event->noStatus()
						 || $event->isPendingRemoval())
						{
							array_push($removals[$userid], $queueuser->queue->resource);
						}
					}

					$data[$userid] = array(
						'user'       => $user,
						'queueusers' => $queuestudents,
					);

					$keeping = QueueUser::query()
						//->withTrashed()
						->select($qu . '.*')
						->join($q, $q . '.id', $qu . '.queueid')
						->join($s, $s . '.id', $q . '.schedulerid')
						->where($qu . '.membertype', '=', 1)
						->where($qu . '.userid', '=', $userid)
						->where($qu . '.notice', '<>', 6)
						->whereNotIn($qu . '.queueid', $removing->pluck('queueid')->toArray())
						//->whereNull($qu . '.datetimeremoved')
						->whereNull($q . '.datetimeremoved')
						->whereNull($s . '.datetimeremoved')
						->get();

					// Prepare and send actual email
					$message = new QueueRemoved($user, $removing, $keeping, $removals[$userid]);
					$message->headers()->text([
						'X-Command' => 'queues:emailqueueremoved',
						'X-Target-Object' => $groupid
					]);

					if ($this->output->isDebug())
					{
						echo $message->render();
					}

					if ($debug || $this->output->isVerbose())
					{
						$this->info("Emailed queueremoved to {$user->email}.");

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
					foreach ($queuestudents as $queueuser)
					{
						$queueuser->update(['notice' => 0]);
					}
				}

				if (empty($data))
				{
					continue;
				}

				// Email group managers
				foreach ($group->managers as $manager)
				{
					// Prepare and send actual email
					$message = new QueueRemovedManager($manager->user, $data);
					$message->headers()->text([
						'X-Command' => 'queues:emailqueueremoved',
						'X-Target-Object' => $groupid
					]);

					if ($this->output->isDebug())
					{
						echo $message->render();
					}

					if ($debug || $this->output->isVerbose())
					{
						$this->info("Emailed queueremoved to manager {$manager->user->email}.");

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
