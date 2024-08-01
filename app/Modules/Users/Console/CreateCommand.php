<?php

namespace App\Modules\Users\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use function Laravel\Prompts\text;
use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use App\Halcyon\Access\Map;
use App\Halcyon\Access\Role;
use App\Modules\Users\Models\User;
use App\Modules\Users\Models\UserUsername;

class CreateCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $signature = 'users:create';
							/*{username : Username of user to create}
							{--name= : User name}
							{--password= : User password}
							{--email= : User email}';*/

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create a user.';

	/**
	 * Execute the console command.
	 */
	public function handle(): int
	{
		

		//$username = $this->argument('username');
		//$name     = $this->option('name');
		//$password = $this->option('password');
		//$email    = $this->option('email');

		$username = text('Desired username?', '', '', true);

		$user = User::findByUsername($username);

		if ($user && $user->id)
		{
			$this->error(trans('users::users.error.username taken'));
			return Command::FAILURE;
		}

		$password = password('User\'s password?', '', true);
		$name = text('Name? (optional)', '', '');
		$email = text('Email address? (optional)', '', $username . '@your.org');

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

		$role = select(
			'What role should the user have?',
			$allRoles->pluck('title')->toArray(),
		);
		$role = trim($role);

		/*$user = User::findByEmail($email);

		if ($user && $user->id)
		{
			$this->error(trans('users::users.error.email taken'));
			return Command::FAILURE;
		}*/

		$user = new User;
		$user->name = $name ? $name : $username;
		$user->api_token = $user->generateApiToken();
		$user->password = Hash::make($password);

		if ($user->save())
		{
			$userusername = new UserUsername;
			$userusername->userid = $user->id;
			$userusername->username = $username;
			if ($email)
			{
				$userusername->email = $email;
			}
			$userusername->save();
		}

		if (!$role)
		{
			$user->setDefaultRole();
		}
		else
		{
			$ids = Role::query()
				->whereIn('title', [$role])
				->get()
				->pluck('id')
				->toArray();

			Map::addUserToRole($user->id, $ids);
		}

		$this->info(trans('users::users.user created'));

		return Command::SUCCESS;
	}
}
