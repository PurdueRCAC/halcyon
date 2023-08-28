<?php

namespace App\Modules\Groups\Console;

use Illuminate\Console\Command;
use App\Modules\Groups\Models\Group;
use App\Modules\Users\Models\User;

class GroupAddMemberCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'groups:add
							{group : Group name}
							{username : Comma-separated list of usernames or emails of users to add}
							{--manager : Add the users as managers}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Add one or more users to a group';

	/**
	 * Execute the console command.
	 *
	 * @return int
	 */
	public function handle(): int
	{
		$group = $this->argument('group');
		$usernames = $this->argument('username');
		$manager = $this->option('manager');

		if (!$group)
		{
			$this->error(trans('groups::groups.error.unix group not found'));
			return Command::FAILURE;
		}

		$group = Group::findByName($group);

		if (!$group)
		{
			$this->error(trans('groups::groups.error.unix group not found'));
			return Command::FAILURE;
		}

		if (!$usernames)
		{
			$this->error(trans('groups::groups.error.user not found'));
			return Command::FAILURE;
		}

		$usernames = explode(',', $usernames);

		foreach ($usernames as $username)
		{
			$user = User::findByUsername($username);

			if (!$user)
			{
				$this->error(trans('groups::groups.error.user not found'));
				return Command::FAILURE;
			}

			if ($manager)
			{
				$group->addManager($user->id);
			}
			else
			{
				$group->addMember($user->id);
			}
		}

		return Command::SUCCESS;
	}
}
