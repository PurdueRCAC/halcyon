<?php

namespace App\Modules\Users\Console;

use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
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
							{username : Username or email of user to add or remove roles}
							{--list : List roles for the user}
							{--add= : Comma separated list of roles to add}
							{--remove= : Comma separated list of roles to remove}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Add or remove roles on a user.';

	/**
	 * Execute the console command.
	 */
	public function handle(): void
	{
		$username   = $this->argument('username');
		$listRole   = $this->option('list');
		$addRole    = $this->option('add');
		$removeRole = $this->option('remove');

		$user = User::findByUsername($username);

		if (!$user || !$user->id)
		{
			$this->error(trans('users::users.error.user not found'));
			return;
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
			return;
		}

		if ($addRole)
		{
			$role = explode(',', $addRole);
			$role = array_map('trim', $role);
			$role = array_filter($role);

			$this->comment('Adding roles to user ' . $user->username . ': ' . implode(', ', $role));

			$ids = Role::query()
				->whereIn('title', $role)
				->get()
				->pluck('id')
				->toArray();

			$roles = array_merge($roles, $ids);
		}

		if ($removeRole)
		{
			$role = explode(',', $removeRole);
			$role = array_map('trim', $role);
			$role = array_filter($role);

			$this->comment('Removing roles from user ' . $user->username . ': ' . implode(', ', $role));

			$ids = Role::query()
				->whereIn('title', $role)
				->get()
				->pluck('id')
				->toArray();

			$roles = array_diff($roles, $ids);
		}

		$user->newroles = $roles;

		if ($user->save())
		{
			$this->listRoles($allRoles, $user, $roles);
		}
		else
		{
			$this->error(trans('users::users.error.role set failed', ['username' => $user->username]));
		}
	}

	/**
	 * Output the list of roles as a tree
	 *
	 * @param array $allRoles
	 * @param User $user
	 * @param array $roles
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
