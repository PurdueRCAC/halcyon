<?php

namespace App\Modules\Groups\Console;

use Illuminate\Console\Command;
use App\Modules\Groups\Models\UnixGroup;
use App\Modules\Users\Models\User;

class UnixGroupAddMemberCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'unixgroups:add
							{unixgroup : Unix group name}
							{username : Comma-separated list of usernames or emails of users to add}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Add one or more users to a unix group';

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function handle()
	{
		$unixgroup = $this->argument('unixgroup');
		$usernames = $this->argument('username');

		if (!$unixgroup)
		{
			$this->error(trans('groups::groups.error.unix group not found'));
			return Command::FAILURE;
		}

		$unixgroup = UnixGroup::findByLongname($unixgroup);

		if (!$unixgroup)
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

			$unixgroup->addMember($user->id);
		}

		return Command::SUCCESS;
	}
}
