<?php

namespace App\Modules\Users\Console;

use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use function Laravel\Prompts\text;
use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use App\Halcyon\Access\Map;
use App\Halcyon\Access\Role;
use App\Modules\Users\Models\User;
use App\Modules\Users\Models\UserUsername;

class RoleCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $signature = 'users:role
							{--list : List roles for the user}
							{--add : Add a role to a user}
							{--remove : Remove a role from a user}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Add or remove roles on a user.';

	/**
	 * Execute the console command.
	 */
	public function handle(): int
	{
		$listRole   = $this->option('list');
		$addRole    = $this->option('add');
		$removeRole = $this->option('remove');

		$username = text('Username?', '', '', true);

		if (!$username)
		{
			$this->error(trans('users::users.error.user not found'));
			return Command::FAILURE;
		}

		$user = User::findByUsername($username);

		if (!$user || !$user->id)
		{
			$this->error(trans('users::users.error.user not found'));
			return Command::FAILURE;
		}

		$roles = $user->roles
			->pluck('role_id')
			->toArray();

		$ug = new Role;

		$allRoles = Role::query()
			->select(['a.id', 'a.title', 'a.parent_id', DB::raw('COUNT(DISTINCT b.id) AS level')])
			->from($ug->getTable() . ' AS a')
			->leftJoin($ug->getTable() . ' AS b', function($join)
				{
					$join->on('a.lft', '>', 'b.lft')
						->on('a.rgt', '<', 'b.rgt');
				})
			->groupBy(['a.id', 'a.title', 'a.lft', 'a.rgt', 'a.parent_id'])
			->orderBy('a.lft', 'asc')
			->get();

		$allRoles->each(function($item)
		{
			$item->title = str_repeat('  ', $item->level) . $item->title;
		});

		if ($listRole)
		{
			$this->listRoles($allRoles, $user, $roles);
			return Command::SUCCESS;
		}

		$opts = array();
		foreach ($allRoles as $allRole)
		{
			$opts[$allRole->id] = $allRole->title;
		}

		if ($addRole)
		{
			$role = select(
				'What role should the user have?',
				$opts,
				1,
				count($opts)
			);

			$ids = [$role];

			Map::addUserToRole($user->id, $ids);
		}

		if ($removeRole)
		{
			$role = select(
				'What role should be removed from the user?',
				$opts,
				null,
				count($opts)
			);

			$ids = [$role];

			Map::removeUserFromRole($user->id, $ids);
		}

		$roles = Map::query()
			->where('user_id', '=', $user->id)
			->pluck('role_id')
			->toArray();

		$this->listRoles($allRoles, $user, $roles);

		return Command::SUCCESS;
	}

	/**
	 * Output the list of roles as a tree
	 *
	 * @param array<int,Role>|Collection $allRoles
	 * @param User $user
	 * @param array<int,int> $roles
	 * @return void
	 */
	private function listRoles($allRoles, $user, $roles): void
	{
		$this->line('Roles for ' . $user->username . ':');

		foreach ($allRoles as $role)
		{
			if (in_array($role->id, $roles))
			{
				$this->info('  [X] ' . $role->title);
			}
			else
			{
				$this->comment('  [ ] ' . $role->title);
			}
		}
	}
}
