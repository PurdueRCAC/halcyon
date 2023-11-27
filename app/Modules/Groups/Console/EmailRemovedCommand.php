<?php

namespace App\Modules\Groups\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Modules\History\Models\Log;
use App\Modules\Groups\Models\Group;
use App\Modules\Groups\Models\Member;
use App\Modules\Users\Models\User;
use App\Modules\Groups\Mail\OwnerRemoved;
use App\Modules\Groups\Mail\OwnerRemovedManager;
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
	 *
	 * @return void
	 */
	public function handle()
	{
		$debug = $this->option('debug') ? true : false;

		$users = Member::query()
			->where('notice', '=', Member::MEMBERSHIP_REMOVED)
			->get();

		if (!count($users))
		{
			if ($debug)
			{
				$this->comment('No records to email.');
			}
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
					if (!$groupuser->user)
					{
						continue;
					}

					$actor = Log::query()
						->where('targetuserid', '=', $groupuser->userid)
						->where('classname', '=', 'groupowner')
						->where('classmethod', '=', 'delete')
						->where('groupid', '=', $groupuser->groupid)
						->limit(1)
						->first();

					if ($actor)
					{
						$groupuser->actor = $actor->user;
					}

					$people[$groupuser->userid] = $groupuser;
				}
			}

			// Email the affected users
			foreach ($people as $userid => $groupuser)
			{
				$user = $groupuser->user;

				if (!$user)
				{
					continue;
				}

				$message = new OwnerRemoved($user, $group);
				$message->headers()->text([
					'X-Command' => 'groups:emailremoved',
				]);

				if ($this->output->isDebug())
				{
					echo $message->render();
				}

				if ($debug || $this->output->isVerbose())
				{
					$this->info("Emailed ownerremoved to {$user->email}.");

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

				$groupuser->update(['notice' => Member::NO_NOTICE]);
			}

			// Email managers
			foreach ($group->managers as $manager)
			{
				$user = $manager->user;

				if (!$user)
				{
					continue;
				}

				$message = new OwnerRemovedManager($user, $group, $people);
				$message->headers()->text([
					'X-Command' => 'groups:emailremoved',
				]);

				if ($this->output->isDebug())
				{
					echo $message->render();
				}

				if ($debug || $this->output->isVerbose())
				{
					$this->info("Emailed ownerremoved to manager {$user->email}.");

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
			}
		}
	}
}
