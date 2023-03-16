<?php

namespace App\Modules\Users\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
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
	protected $signature = 'users:create
							{username : Username of user to create}
							{--name= : User name}
							{--password= : User password}
							{--email= : User email}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create a user.';

	/**
	 * Execute the console command.
	 */
	public function handle(): void
	{
		$username = $this->argument('username');
		$name     = $this->option('name');
		$password = $this->option('password');
		$email    = $this->option('email');

		$user = User::findByUsername($username);

		if ($user && $user->id)
		{
			$this->error(trans('users::users.error.username taken'));
			return;
		}

		$user = User::findByEmail($email);

		if ($user && $user->id)
		{
			$this->error(trans('users::users.error.email taken'));
			return;
		}

		$user = new User;
		$user->name = $name;
		$user->api_token = Str::random(60);
		$user->password = Hash::make($password);

		$newUsertype = config('module.users.new_usertype');

		if (!$newUsertype)
		{
			$newUsertype = Role::findByTitle('Registered')->id;
		}

		if ($newUsertype)
		{
			$user->newroles = array($newUsertype);
		}

		if ($user->save())
		{
			$userusername = new UserUsername;
			$userusername->userid = $user->id;
			$userusername->username = $username;
			$userusername->email = $email;
			$userusername->save();
		}

		$this->info(trans('users::users.error.user created'));
	}
}
