<?php
namespace App\Listeners\Auth\Ldap;

use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Auth\Events\Failed;
use Adldap\Models\User as LdapUser;
use App\Listeners\Auth\Ldap\Events\DiscoveredWithCredentials;
use App\Listeners\Auth\Ldap\Events\Authenticating;
use App\Listeners\Auth\Ldap\Events\AuthenticationRejected;
use App\Listeners\Auth\Ldap\Events\AuthenticationFailed;
use App\Listeners\Auth\Ldap\Events\AuthenticationSuccessful;
use App\Listeners\Auth\Ldap\Events\AuthenticatedWithCredentials;
use App\Listeners\Auth\Ldap\Events\AuthenticatedModelTrashed;
use App\Listeners\Auth\Ldap\Events\Authenticated;
use App\Modules\Users\Events\Login;
use App\Modules\Users\Events\Authenticate;
use App\Modules\Users\Models\User;
use App\Modules\Users\Models\UserUsername;
use App\Halcyon\Access\Role;
use RuntimeException;

class Ldap
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		//$events->listen(Login::class, self::class . '@handleAuthenticate');
		$events->listen(Authenticate::class, self::class . '@handleAuthenticate');
	}

	/**
	 * Establish LDAP connection
	 *
	 * @param   array  $config
	 * @return  object
	 */
	private function resolver()
	{
		if (!app()->has('ldap'))
		{
			return false;
		}

		$config = config('listener.openldap.connection', []);

		return app('ldap')
				->addProvider($config, 'openldap')
				->connect('openldap');
	}

	/**
	 * Handle user login events.
	 * 
	 * @param $event
	 */
	public function handleLogin($event)
	{
		$config = $this->config();

		if (empty($config))
		{
			return;
		}

		$request = $event->request;

		$ldap = $this->connect($config);

		if ($cas->checkAuthentication())
		{
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
		$resolver = $this->resolver();

		if (!$resolver)
		{
			return;
		}

		$request = $event->request;
		$request->validate([
			'username' => 'required|min:3',
			'password' => 'required|min:3'
		]);

		$credentials = [
			//'username' => $request->input('username'),
			'password' => $request->input('password'),
		];
		$credentials[$this->getLdapDiscoveryAttribute()] = $request->input('username');

		$ldapuser = $this->findByCredentials($credentials);

		if (!$ldapuser)
		{
			event(new Failed('web', $ldapuser, $credentials));

			$event->error = 'Invalid Username/email.';
			return false;
		}

		event(new DiscoveredWithCredentials($ldapuser));

		if (!$this->authenticate($ldapuser, $credentials))
		{
			event(new AuthenticationRejected($ldapuser));

			$event->error = 'Incorrect password.';
			return false;
		}

		event(new AuthenticatedWithCredentials($ldapuser));

		$event->authenticated = true;

		if (!auth()->user())
		{
			$user = $this->getDatabaseUser($ldapuser);

			// Create accounts on login?
			if ((!$user || !$user->id) && config('module.users.create_on_login', 1))
			{
				$user = $this->createDatabaseUser($ldapuser);
			}

			if (!$user || !$user->id)
			{
				abort(401, trans('global.unauthorized'));
			}

			// Restore "trashed" accounts on login?
			if ($user->trashed())
			{
				event(new AuthenticatedModelTrashed($ldapuser, $user));

				if (!config('module.users.restore_on_login', 0))
				{
					abort(401, trans('global.unauthorized'));
				}

				$user->getUserUsername()->restore();
			}

			Auth::loginUsingId($user->id);

			event(new AuthenticationSuccessful($ldapuser, $user));
		}
	}

	/**
	 * @param array $credentials
	 * @return null|LdapUser
	 */
	protected function findByCredentials(array $credentials = [])
	{
		$resolver = $this->resolver();

		if (empty($credentials) || !$resolver)
		{
			return;
		}

		$attribute = $this->getLdapDiscoveryAttribute();

		if (! array_key_exists($attribute, $credentials))
		{
			throw new RuntimeException(
				"The '$attribute' key is missing from the given credentials array."
			);
		}

		return $resolver->search()->users()->whereEquals(
			$attribute,
			$credentials[$attribute]
		)->first();
	}

	/**
	 * @param LdapUser $user
	 * @param array $credentials
	 * @return bool
	 */
	protected function authenticate(LdapUser $user, array $credentials = [])
	{
		$attribute = $this->getLdapAuthAttribute();

		// If the developer has inserted 'dn' as their LDAP
		// authentication attribute, we'll convert it to
		// the full attribute name for convenience.
		if ($attribute == 'dn')
		{
			$attribute = $user->getSchema()->distinguishedName();
		}

		$username = $user->getFirstAttribute($attribute);
		$password = $this->getPasswordFromCredentials($credentials);

		event(new Authenticating($user, $username));

		if ($this->resolver()->auth()->attempt($username, $password))
		{
			event(new Authenticated($user));

			return true;
		}

		event(new AuthenticationFailed($user));

		return false;
	}

	/**
	 * @param LdapUser $ldapuser
	 * @return User
	 */
	protected function getDatabaseUser(LdapUser $ldapuser)
	{
		$attribute = $this->getLdapDiscoveryAttribute(); //$ldapuser->getSchema()->distinguishedName();
		$username = $ldapuser->getFirstAttribute($attribute);

		if (filter_var($username, FILTER_VALIDATE_EMAIL))
		{
			return User::findByEmail($username);
		}

		return User::findByUsername($username);
	}

	/**
	 * @param LdapUser $ldapuser
	 * @return User
	 */
	protected function createDatabaseUser(LdapUser $ldapuser)
	{
		$user = new User;
		$user->api_token = Str::random(60);

		$userusername = new UserUsername;

		$sync_attributes = config('listener.openldap.sync_attributes', [
			'name'  => 'cn',
			'email' => 'userprincipalname',
		]);

		foreach ($sync_attributes as $key => $attr)
		{
			if (in_array($key, ['name', 'puid']))
			{
				$user->{$key} = $ldapuser->getAttribute($attr);
			}

			if (in_array($key, ['email', 'username']))
			{
				$userusername->{$key} = $ldapuser->getAttribute($attr);
			}
		}

		$newUsertype = config('module.users.new_usertype');
		$newUsertype = !$newUsertype ? Role::findByTitle('Registered')->id : $newUsertype;

		if ($newUsertype)
		{
			$user->newroles = array($newUsertype);
		}

		if ($user->save())
		{
			$userusername->userid = $user->id;
			$userusername->save();
		}

		return $user;
	}

	/**
	 * @return string
	 */
	protected function getLdapDiscoveryAttribute(): string
	{
		return config('listener.openldap.locate_users_by', 'userprincipalname');
	}

	/**
	 * @return string
	 */
	protected function getLdapAuthAttribute(): string
	{
		return config('listener.openldap.bind_users_by', 'distinguishedname');
	}

	/**
	 * Returns the password field to retrieve from the credentials.
	 *
	 * @param array $credentials
	 * @return string|null
	 */
	protected function getPasswordFromCredentials($credentials)
	{
		return Arr::get($credentials, 'password');
	}
}
