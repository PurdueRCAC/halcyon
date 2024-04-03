<?php
namespace App\Listeners\Auth\Ldap;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Events\Dispatcher;
use LdapRecord\Models\Entry as LdapUser;
use LdapRecord\Container;
use LdapRecord\Connection;
use App\Listeners\Auth\Ldap\Events\DiscoveredWithCredentials;
use App\Listeners\Auth\Ldap\Events\Authenticating;
use App\Listeners\Auth\Ldap\Events\AuthenticationRejected;
use App\Listeners\Auth\Ldap\Events\AuthenticationFailed;
use App\Listeners\Auth\Ldap\Events\AuthenticationSuccessful;
use App\Listeners\Auth\Ldap\Events\AuthenticatedWithCredentials;
use App\Listeners\Auth\Ldap\Events\AuthenticatedModelTrashed;
use App\Listeners\Auth\Ldap\Events\Authenticated;
use App\Modules\Users\Events\Authenticators;
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
	 * @param  Dispatcher  $events
	 * @return void
	 */
	public function subscribe(Dispatcher $events): void
	{
		$events->listen(Authenticators::class, self::class . '@handleAuthenticators');
		//$events->listen(Login::class, self::class . '@handleAuthenticate');
		$events->listen(Authenticate::class, self::class . '@handleAuthenticate');
	}

	/**
	 * Establish LDAP connection
	 *
	 * @return  Connection|null
	 */
	private function resolver(): ?Connection
	{
		$config = config('listener.openldap.connection', []);

		if (empty($config))
		{
			return null;
		}

		$connection = new Connection($config);

		Container::addConnection($connection, 'openldap');

		$connection->connect();

		return $connection;
	}

	/**
	 * Handle user login events.
	 * 
	 * @param  Authenticators $event
	 * @return void
	 */
	public function handleAuthenticators(Authenticators $event): void
	{
		$event->addAuthenticator('ldap', [
			'label' => 'LDAP',
			'view'  => 'users::site.auth',
		]);
	}

	/**
	 * Handle user login events.
	 * 
	 * @param Login $event
	 * @return void
	 */
	public function handleLogin(Login $event): void
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
		if ($event->authenticator != 'ldap')
		{
			return;
		}

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
	 * Find a user by their credentials
	 *
	 * @param array<string,string> $credentials
	 * @return null|LdapUser
	 * @throws RuntimeException
	 */
	protected function findByCredentials(array $credentials = []): ?LdapUser
	{
		$resolver = $this->resolver();

		if (empty($credentials) || !$resolver)
		{
			return null;
		}

		$attribute = $this->getLdapDiscoveryAttribute();

		if (! array_key_exists($attribute, $credentials))
		{
			throw new RuntimeException(
				"The '$attribute' key is missing from the given credentials array."
			);
		}

		return $resolver->query()->users()->whereEquals(
			$attribute,
			$credentials[$attribute]
		)->first();
	}

	/**
	 * @param LdapUser $user
	 * @param array<string,string> $credentials
	 * @return bool
	 */
	protected function authenticate(LdapUser $user, array $credentials = []): bool
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
	 * Retrieve a local user account by either email or username.
	 *
	 * @param LdapUser $ldapuser
	 * @return User|null
	 */
	protected function getDatabaseUser(LdapUser $ldapuser): ?User
	{
		$attribute = $this->getLdapDiscoveryAttribute();
		$username = $ldapuser->getFirstAttribute($attribute);

		if (filter_var($username, FILTER_VALIDATE_EMAIL))
		{
			return User::findByEmail($username);
		}

		return User::findByUsername($username);
	}

	/**
	 * Create a local user instance in the database from the LDAP attributes.
	 *
	 * @param LdapUser $ldapuser
	 * @return User
	 */
	protected function createDatabaseUser(LdapUser $ldapuser): User
	{
		$user = new User;
		$user->api_token = $user->generateApiToken();

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

		$user->setDefaultRole();

		if ($user->save())
		{
			$userusername->userid = $user->id;
			$userusername->save();
		}

		return $user;
	}

	/**
	 * This value is the users attribute you would like to locate LDAP users by in your directory.
	 *
	 * @return string
	 */
	protected function getLdapDiscoveryAttribute(): string
	{
		return config('listener.ldap.locate_users_by', 'userprincipalname');
	}

	/**
	 * This value is the users attribute you would like to use to bind to your LDAP server.
	 *
	 * @return string
	 */
	protected function getLdapAuthAttribute(): string
	{
		return config('listener.ldap.bind_users_by', 'distinguishedname');
	}

	/**
	 * Returns the password field to retrieve from the credentials.
	 *
	 * @param array<string,string> $credentials
	 * @return string|null
	 */
	protected function getPasswordFromCredentials(array $credentials): ?string
	{
		return Arr::get($credentials, 'password');
	}
}
