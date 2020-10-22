<?php

namespace App\Modules\Groups\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Modules\Groups\Models\Group;
use App\Modules\Groups\Models\Member;
use App\Modules\Users\Models\User;
use App\Modules\Groups\Mail\OwnerRemoved;
use App\Modules\Groups\Mail\OwnerRemovedManager;
use App\Modules\History\Models\Log;
use Carbon\Carbon;

class EmailRemovedCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'groups:emailremoved {--debug : Output emails rather than sending}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Email latest group member removals.';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		$debug = $this->option('debug') ? true : false;

		$users = Member::query()
			->where('notice', '=', 22)
			->get();

		if (!count($users))
		{
			$this->comment('No records to email.');
			return;
		}

		$group_activity = array();
		foreach ($users as $user)
		{
			if (!isset($group_activity[$user->groupid]))
			{
				$group_activity[$user->groupid] = array();
			}

			array_push($group_activity[$user->groupid], $user);
		}

		$now = Carbon::now()->timestamp;
		$threshold = 1200;

		foreach ($group_activity as $groupid => $groupusers)
		{
			$group = Group::find($groupid);

			if (!$group)
			{
				continue;
			}

			// Find the latest activity
			$latest = 0;
			foreach ($groupusers as $g)
			{
				if ($g->datecreated->timestamp > $latest)
				{
					$latest = $g->datecreated->timestamp;
				}
			}

			if ($now - $latest < $threshold)
			{
				continue;
			}

			// Condense people
			$people = array();
			foreach ($groupusers as $groupuser)
			{
				if (!isset($people[$groupuser->userid]))
				{
					$user = $groupuser->user;

					$actor = Log::query()
						->where('targetuserid', '=', $groupuser->userid)
						->where('classname', '=', 'groupowner')
						->where('classmethod', '=', 'delete')
						->where('groupid', '=', $groupuser->groupid)
						->limit(1)
						->first();

					if ($actor)
					{
						$user->actor = $actor->user;
					}

					$people[$groupuser->userid] = $user;
				}
			}

			foreach ($people as $userid => $user)
			{
				if (!$user)
				{
					continue;
				}

				$message = new OwnerRemoved($user, $group);

				if ($debug)
				{
					echo $message->render();
					continue;
				}

				Mail::to($user->email)->send($message);

				$this->info("Emailed ownerremoved to {$user->email}.");
			}

			foreach ($group->managers as $manager)
			{
				$user = $manager->user;

				if (!$user)
				{
					continue;
				}

				$message = new OwnerRemovedManager($user, $group, $people);

				if ($debug)
				{
					echo $message->render();
					continue;
				}

				Mail::to($user->email)->send($message);

				$this->info("Emailed ownerremoved to {$user->email}.");
			}
		}
	}
}
