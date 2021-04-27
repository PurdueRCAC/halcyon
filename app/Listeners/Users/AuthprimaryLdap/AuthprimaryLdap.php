<?php
namespace App\Listeners\Users\AuthprimaryLdap;

use App\Modules\Users\Events\UserSync;
use App\Modules\History\Traits\Loggable;
use Exception;

/**
 * User listener for AuthPrimary Ldap
 */
class AuthprimaryLdap
{
	use Loggable;

	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen(UserSync::class, self::class . '@handleUserSync');
	}

	/**
	 * Get LDAP config
	 *
	 * @return  array
	 */
	private function config($sub = null)
	{
		if (!app()->has('ldap'))
		{
			return array();
		}

		return config('listener.authprimaryldap' . ($sub ? '.' . $sub : ''), []);
	}

	/**
	 * Establish LDAP connection
	 *
	 * @param   array  $config
	 * @return  object
	 */
	private function connectPeople($config)
	{
		return app('ldap')
				->addProvider($config, 'authprimary')
				->connect('authprimary');
	}

	/**
	 * Establish LDAP connection
	 *
	 * @param   array  $config
	 * @return  object
	 */
	private function connectAllPeople($config)
	{
		return app('ldap')
				->addProvider($config, 'authprimaryall')
				->connect('authprimaryall');
	}

	/**
	 * Handle a User creation event
	 * 
	 * This will add entries to the AuthPrimary LDAP
	 *
	 * @param   UserSync  $event
	 * @return  void
	 */
	public function handleUserSync(UserSync $event)
	{
		// Make sure config is set
		$configall = $this->config('all');
		$config = $this->config('authorized');

		if (empty($configall) || empty($config))
		{
			return;
		}

		$auth = false;
		$user = $event->user;
		$results = array();
		$status = 200;

		try
		{
			$ldap = $this->connectAllPeople($configall);

			// Check for an existing record
			$result = $ldap->search()
				->where('uid', '=', $user->username)
				->first();

			if (!$result || !$result->exists)
			{
				/*
				Sample LDAP entry

				# example, AllPeople, anvil.rcac.purdue.edu
				dn: uid=example,ou=AllPeople,dc=anvil,dc=rcac,dc=purdue,dc=edu
				objectClass: posixAccount
				objectClass: inetOrgPerson
				objectClass: top
				uid: example
				uidNumber: 20972
				gidNumber: 6751
				homeDirectory: /home/example
				loginShell: /bin/tcsh
				cn: Ex A Mple
				sn: Ex
				*/
				// Create user record in ou=allPeople
				$data = [
					'uid'           => $user->username,
					'uidNumber'     => $user->uidNumber,
					'gidNumber'     => $user->gidNumber,
					'cn'            => $auth ? $user->name : '-',
					'sn'            => $auth ? $user->surname : '-',
					'loginShell'    => $auth ? $user->loginShell : '/bin/false',
					'homeDirectory' => $auth ? '/home/' . $user->username : '/dev/null',
				];

				$entry = $ldap->make()->user($data);

				if (!$entry->save())
				{
					throw new Exception('Failed to make AuthPrimary ou=allPeople record', 500);
				}

				$results['created'] = $data;
				$status = 201;
			}
			elseif ($auth)
			{
				// Update user record in ou=allPeople
				$entry->setAttribute('cn', $user->name);
				$entry->setAttribute('sn', $user->surname);
				$entry->setAttribute('loginShell', $user->loginShell);
				$entry->setAttribute('homeDirectory', '/home/' . $user->username);

				if (!$entry->save())
				{
					throw new Exception('Failed to update AuthPrimary ou=allPeople record', 500);
				}

				$results['updated'] = [
					'cn' => $user->name,
					'sn' => $user->surname,
					'loginShell' => $user->loginShell,
					'homeDirectory' => '/home/' . $user->username
				];
			}

			if ($auth)
			{
				$ldap = $this->connectPeople($config);

				// Check for an existing record
				$result = $ldap->search()
					->where('uid', '=', $user->username)
					->first();

				if (!$result || !$result->exists)
				{
					/*
					Sample LDAP entry

					# example, People, anvil.rcac.purdue.edu
					dn: uid=example,ou=People,dc=anvil,dc=rcac,dc=purdue,dc=edu
					objectClass: posixAccount
					objectClass: inetOrgPerson
					objectClass: top
					uid: example
					uidNumber: 20972
					gidNumber: 6751
					homeDirectory: /home/example
					loginShell: /bin/tcsh
					cn: Ex A Mple
					givenName: Ex A
					sn: Mple
					gecos: Ex A Mple
					telephoneNumber: 49-61741
					*/
					// Create user record in ou=People
					$data = [
						'uid'           => $user->username,
						'uidNumber'     => $user->uidNumber,
						'gidNumber'     => $user->gidNumber,
						'cn'            => $user->name,
						'givenName'     => $user->givenName,
						'sn'            => $user->surname,
						'loginShell'    => $user->loginShell,
						'homeDirectory' => '/home/' . $user->username,
						'gecos'         => $user->name,
					];

					if ($user->telephone)
					{
						$data['telephoneNumber'] = $user->telephone;
					}

					$entry = $ldap->make()->user($data);

					if (!$entry->save())
					{
						throw new Exception('Failed to make AuthPrimary ou=People record', 500);
					}

					$results['created_auth'] = $data;
					$status = 201;
				}
			}
		}
		catch (Exception $e)
		{
			$status = 500;
			$results = ['error' => $e->getMessage()];
		}

		$this->log('ldap', __METHOD__, 'GET', $status, $results, 'uid=' . $event->user->username);
	}
}
