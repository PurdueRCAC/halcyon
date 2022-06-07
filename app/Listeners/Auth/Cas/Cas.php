<?php
namespace App\Listeners\Auth\Cas;

use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Modules\Users\Events\Login;
use App\Modules\Users\Events\Authenticate;
use App\Modules\Users\Models\User;
use App\Modules\Users\Models\UserUsername;
use App\Halcyon\Access\Role;

class Cas
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen(Login::class, self::class . '@handleAuthenticate');
		$events->listen(Authenticate::class, self::class . '@handleAuthenticate');
		$events->listen(Logout::class, self::class . '@handleLogout');
	}

	/**
	 * Handle user login events.
	 * 
	 * @param $event
	 */
	public function handleLogin($event)
	{
		$request = $event->request;

		if (!app()->has('cas'))
		{
			return;
		}

		$cas = app('cas');

		if ($cas->checkAuthentication())
		{
			// Store the user credentials in a Laravel managed session
			session()->put('cas_user', $cas->user());

			$this->handleAuthentication($event);
		}
		else
		{
			if ($request->ajax() || $request->wantsJson())
			{
				abort(401, trans('global.unauthorized'));
			}
			
			$cas->authenticate();
		}
	}

	/**
	 * Handle user login events.
	 * 
	 * @param $event
	 */
	public function handleAuthenticate($event)
	{
		$request = $event->request;

		if (!app()->has('cas'))
		{
			return;
		}

		$cas = app('cas');

		if (!$cas->checkAuthentication())
		{
			if ($request->ajax() || $request->wantsJson())
			{
				abort(401, trans('global.unauthorized'));
			}
			$cas->authenticate();
		}

		$event->authenticated = true;

		// Store the user credentials in a Laravel managed session
		session()->put('cas_user', $cas->user());

		if (!auth()->user())
		{
			$user = User::findByUsername($cas->user(), config('module.users.restore_on_login', 0));

			$newUsertype = config('module.users.new_usertype');

			if (!$newUsertype)
			{
				$newUsertype = Role::findByTitle('Registered')->id;
			}

			// Create accounts on login?
			if ((!$user || !$user->id) && config('module.users.create_on_login', 1))
			{
				$user = new User;
				$user->name = $cas->getAttribute('fullname');
				$user->api_token = Str::random(60);

				$attrs = $cas->getAttributes();
				if (isset($attrs['puid']))
				{
					$user->puid = intval($attrs['puid']);
				}

				if ($newUsertype)
				{
					$user->newroles = array($newUsertype);
				}

				if ($user->save())
				{
					$userusername = new UserUsername;
					$userusername->userid = $user->id;
					$userusername->username = $cas->user();
					if (isset($attrs['email']))
					{
						$userusername->email = $attrs['email'];
					}
					$userusername->save();
				}
			}

			if (!$user || !$user->id)
			{
				abort(401, trans('global.unauthorized'));
			}

			// Restore "trashed" accounts on login?
			if ($user->trashed())
			{
				if (config('module.users.restore_on_login', 0))
				{
					$user->getUserUsername()->restore();
				}
				else
				{
					abort(401, trans('global.unauthorized'));
				}
			}

			if (!count($user->roles) && $newUsertype)
			{
				$user->newroles = array($newUsertype);
				$user->save();
			}

			if (!$user->api_token)
			{
				$user->api_token = Str::random(60);
				$user->save();
			}

			if (!$user->puid)
			{
				$attrs = $cas->getAttributes();
				if (isset($attrs['puid']))
				{
					$user->update(['puid' => intval($attrs['puid'])]);
				}
			}

			Auth::loginUsingId($user->id);
		}
	}

	/**
	 * Handle user logout events.
	 */
	public function handleLogout(Logout $event)
	{
		session()->invalidate();
		session()->regenerate();

		if (app()->has('cas'))
		{
			app('cas')->logout(route('home'), route('home'));
		}
	}
}
