<?php
namespace App\Listeners\Auth\Database;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Failed;
use App\Modules\Users\Models\User;
use App\Modules\Users\Events\Authenticators;
use App\Modules\Users\Events\Login;
use App\Modules\Users\Events\Authenticate;

/**
 * Database-based authentication plugin
 */
class Database
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen(Authenticators::class, self::class . '@handleAuthenticators');
		$events->listen(Login::class, self::class . '@handleLogin');
		$events->listen(Authenticate::class, self::class . '@handleAuthenticate');
	}

	/**
	 * Handle user login events.
	 * 
	 * @param  Authenticators $event
	 * @return void
	 */
	public function handleAuthenticators(Authenticators $event): void
	{
		$event->addAuthenticator('database', [
			'label' => 'Local Account',
			'view'  => 'users::site.auth',
		]);
	}

	/**
	 * This method should handle any authentication and report back to the subject
	 *
	 * @param   Login $event
	 * @return  void
	 */
	public function handleLogin(Login $event)
	{
	}

	/**
	 * This method should handle any authentication and report back to the subject
	 *
	 * @param   Authenticate $event
	 * @return  void
	 */
	public function handleAuthenticate(Authenticate $event)
	{
		if ($event->authenticator != 'database')
		{
			return;
		}

		$request = $event->request;

		$request->validate([
			'username' => 'required|min:3',//'required|email',
			'password' => 'required|min:3'
		]);

		$credentials = [
			'username' => $request->input('username'),
			'password' => $request->input('password'),
		];

		if (filter_var($credentials['username'], FILTER_VALIDATE_EMAIL))
		{
			$credentials['email'] = $credentials['username'];
			unset($credentials['username']);

			$user = User::findByEmail($credentials['email']);
		}
		else
		{
			$user = User::findByUsername($credentials['username']);
		}

		if (!$user)
		{
			event(new Failed('web', $user, $credentials));

			$event->error = 'Invalid Username/email.';
			return false;
		}

		if (!Hash::check($credentials['password'], $user->password))
		{
			$event->error = 'Incorrect password.';
			return false;
		}

		if (!$user->enabled && !config('module.users.allow_disabled_login'))
		{
			$event->error = 'Account has been disabled.';
			return false;
		}

		Auth::loginUsingId($user->id);

		//$remember = (bool) $request->get('remember_me', false);

		$event->authenticated = true;//Auth::attempt($credentials, $remember);
	}
}
