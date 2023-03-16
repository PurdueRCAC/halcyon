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
	public function handle()
	{
		$username = $this->argument('username');
		$listRole = $this->option('list');
		$addRole = $this->option('add');
		$removeRole = $this->option('remove');

		$user = User::findByUsername($username);

		if (!$user || !$user->id)
		{
			$this->danger('User not found.');
			return;
		}

		$roles = $user->roles
			->pluck('role_id')
			->toArray();

		if ($listRole)
		{
			$allroles = Role::query()->orderBy('lft', 'asc')->get();

			$this->line('Roles for ' . $user->username . ':');

			foreach ($allroles as $role)
			{
				$this->comment('    [' . (in_array($role->id, $roles) ? 'X' : ' ') . '] ' . ($role->id < 10 ? ' ' : '') . $role->id . ' : ' . $role->title);
			}
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
			$allroles = Role::query()->orderBy('lft', 'asc')->get();

			$this->line('Roles for ' . $user->username . ':');

			foreach ($allroles as $role)
			{
				$this->comment('    [' . (in_array($role->id, $roles) ? 'X' : ' ') . '] ' . ($role->id < 10 ? ' ' : '') . $role->id . ' : ' . $role->title);
			}
			return;
		}
		else
		{
			$this->danger('Failed to set roles for user ' . $user->username);
		}
	}
}
