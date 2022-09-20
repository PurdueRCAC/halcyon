<?php

namespace App\Modules\Groups\Console;

use Illuminate\Console\Command;
use App\Modules\Groups\Models\Group;
use App\Modules\Groups\Models\Member;
use App\Modules\Groups\Models\UnixGroup;
use App\Modules\Groups\Models\UnixGroupMember;
use App\Modules\Users\Models\UserUsername;
use App\Modules\Queues\Models\Queue;
use App\Modules\Queues\Models\User AS QueueUser;
use App\Modules\Queues\Models\Scheduler;

class SyncMembershipCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'groups:syncmembership';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Ensure group memberships include all relevant queue and unix group users.';

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function handle()
	{
		if ($this->output->isVerbose())
		{
			$this->info("Starting sync...");
		}

		$groups = Group::query()
			->select('id')
			->get()
			->pluck('id')
			->toArray();

		if (!count($groups))
		{
			if ($this->output->isVerbose())
			{
				$this->comment('No groups found.');
			}
			return;
		}

		$u = (new UserUsername)->getTable();

		$q = (new Queue)->getTable();
		$qu = (new QueueUser)->getTable();

		$g = (new UnixGroup)->getTable();
		$gu = (new UnixGroupMember)->getTable();

		$s = (new Scheduler)->getTable();

		foreach ($groups as $groupid)
		{
			$group = Group::find($groupid);

			if ($this->output->isVerbose())
			{
				$this->comment('Processing group ID #' . $group->id . '... ');
			}

			$existing = $group->members()
				->orderBy('datecreated', 'desc')
				->get()
				->pluck('userid')
				->toArray();

			$queueusers = QueueUser::query()
				->select($qu . '.queueid', $qu . '.userid', $qu . '.datetimecreated')
				->join($q, $q . '.id', $qu . '.queueid')
				->join($s, $s . '.id', $q . '.schedulerid')
				->join($u, $u . '.userid', $qu . '.userid')
				->whereNull($q . '.datetimeremoved')
				->whereNull($s . '.datetimeremoved')
				->whereNull($u . '.dateremoved')
				->where($q . '.groupid', '=', $group->id)
				->whereIn($q . '.queuetype', [1,3])
				->whereNotIn($qu . '.userid', $existing)
				->get();

			foreach ($queueusers as $queueuser)
			{
				if (in_array($queueuser->userid, $existing))
				{
					continue;
				}

				$member = new Member;
				$member->groupid = $group->id;
				$member->userid = $queueuser->userid;
				$member->datecreated = $queueuser->datetimecreated;
				$member->setAsMember();
				$member->save();

				if ($this->output->isVerbose())
				{
					$this->line('Added user ID #' . $queueuser->userid . ' to group ID #' . $group->id . ' from queue ' . $queueuser->queueid);
				}

				$existing[] = $queueuser->userid;
			}

			if (!$group->unixgroup)
			{
				continue;
			}

			$unixgroupusers = UnixGroupMember::query()
				->select($gu . '.*')
				->join($g, $g . '.id', $gu . '.unixgroupid')
				->join($u, $u . '.userid', $gu . '.userid')
				->whereNull($g . '.datetimeremoved')
				->whereNull($u . '.dateremoved')
				->where($g . '.groupid', '=', $group->id)
				->whereNotIn($gu . '.userid', $existing)
				->get();

			foreach ($unixgroupusers as $unixgroupuser)
			{
				if (in_array($unixgroupuser->userid, $existing))
				{
					continue;
				}

				$member = new Member;
				$member->groupid = $group->id;
				$member->userid = $unixgroupuser->userid;
				$member->datecreated = $unixgroupuser->datetimecreated;
				$member->setAsMember();
				$member->save();

				if ($this->output->isVerbose())
				{
					$this->line('Added user ID #' . $unixgroupuser->userid . ' to group ID #' . $group->id . ' from unix group ' . $unixgroupuser->unixgroupid);
				}

				$existing[] = $unixgroupuser->userid;
			}
		}

		if ($this->output->isVerbose())
		{
			$this->info("Finished sync.");
		}
	}
}
