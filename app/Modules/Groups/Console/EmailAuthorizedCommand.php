<?php

namespace App\Modules\Groups\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Modules\Groups\Models\Group;
use App\Modules\Groups\Models\Member;
use App\Modules\Groups\Mail\OwnerAuthorized;
use App\Modules\Groups\Mail\OwnerAuthorizedManager;
use App\Modules\Users\Models\User;
use Carbon\Carbon;

class EmailAuthorizedCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'groups:emailauthorized {--debug : Output actions that would be taken without making them}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Email latest group member authorizations.';

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function handle()
	{
		$debug = $this->option('debug') ? true : false;

		$users = Member::query()
			->where('notice', '=', Member::MEMBERSHIP_AUTHORIZED)
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

			if (($now - $latest) < $threshold)
			{
				continue;
			}

			// Condense people
			$people = array();
			foreach ($groupusers as $groupuser)
			{
				if (!isset($people[$groupuser->userid]))
				{
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

				$message = new OwnerAuthorized($user, $group);
				$message->headers()->text([
					'X-Command' => 'groups:emailauthorized',
				]);

				if ($this->output->isDebug())
				{
					echo $message->render();
				}

				if ($debug || $this->output->isVerbose())
				{
					$this->info("Emailed ownerauthorized to {$user->email}.");

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

				$message = new OwnerAuthorizedManager($user, $group, $people);
				$message->headers()->text([
					'X-Command' => 'groups:emailauthorized',
				]);

				if ($this->output->isDebug())
				{
					echo $message->render();
				}

				if ($debug || $this->output->isVerbose())
				{
					$this->info("Emailed ownerauthorized to manager {$user->email}.");

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
