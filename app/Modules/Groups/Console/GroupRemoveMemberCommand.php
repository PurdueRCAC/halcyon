<?php

namespace App\Modules\Groups\Console;

use Illuminate\Console\Command;
use App\Modules\Groups\Models\UnixGroup;
use App\Modules\Users\Models\User;

class GroupRemoveMemberCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'groups:remove
							{group : Group name}
							{username : Comma-separated list of usernames or emails of users to add}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Remove one or more users from a group';

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function handle()
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

		$usernames = explode(',', $username);

		foreach ($usernames as $username)
		{
			$user = User::findByUsername($username);

			if (!$user)
			{
				$this->error(trans('groups::groups.error.user not found'));
				return Command::FAILURE;
			}

			$group->removeMember($user->id);
		}

		return Command::SUCCESS;
	}
}
