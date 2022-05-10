<?php
namespace App\Listeners\Users\AuthprimaryLdap;

use App\Modules\Users\Events\UserSync;
use App\Modules\Users\Events\UserBeforeDisplay;
use App\Modules\History\Traits\Loggable;
use App\Modules\Resources\Events\ResourceMemberCreated;
use App\Modules\Resources\Events\ResourceMemberStatus;
use App\Modules\Resources\Events\ResourceMemberDeleted;
use App\Modules\Groups\Events\UnixGroupCreating;
use App\Modules\Groups\Events\UnixGroupDeleted;
use App\Modules\Groups\Events\UnixGroupMemberCreated;
use App\Modules\Groups\Events\UnixGroupMemberDeleted;
use Exception;

/**
 * User listener for AuthPrimary Ldap
 */
class AuthprimaryLdap
{
	use Loggable;

	/**
	 * Ignore some resources
	 * 
	 * @var  array
	 */
	private $whitelist = [
		'anvil',
		'internal',
	];

	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen(UserSync::class, self::class . '@handleUserSync');
		$events->listen(UserBeforeDisplay::class, self::class . '@handleUserBeforeDisplay');

		// User/Resource
		$events->listen(ResourceMemberCreated::class, self::class . '@handleResourceMemberCreated');
		$events->listen(ResourceMemberStatus::class, self::class . '@handleResourceMemberStatus');
		$events->listen(ResourceMemberDeleted::class, self::class . '@handleResourceMemberDeleted');

		// Unix Groups
		$events->listen(UnixGroupCreating::class, self::class . '@handleUnixGroupCreating');
		$events->listen(UnixGroupDeleted::class, self::class . '@handleUnixGroupDeleted');
		$events->listen(UnixGroupMemberCreated::class, self::class . '@handleUnixGroupMemberCreated');
		$events->listen(UnixGroupMemberDeleted::class, self::class . '@handleUnixGroupMemberDeleted');
	}

	/**
	 * Get LDAP config
	 *
	 * @param   string  $ou
	 * @return  array
	 */
	private function config($ou = 'People', $dc = null)
	{
		if (!app()->has('ldap'))
		{
			return array();
		}

		$config = config('listener.authprimaryldap', []);

		if ($dc && isset($config['base_dn']))
		{
			$basedn = stristr($config['base_dn'], ',');
			$basedn = trim($basedn, ',');

			if ($dc != 'internal')
			{
				$basedn = 'dc=' . $dc . ',' . $basedn;
			}

			$config['base_dn'] = $basedn;
		}

		if ($ou && isset($config['base_dn']))
		{
			$config['base_dn'] = 'ou=' . $ou . ',' . $config['base_dn'];
		}

		return $config;
	}

	/**
	 * Establish LDAP connection
	 *
	 * @param   array  $config
	 * @param   string $name
	 * @return  object
	 */
	private function connect($config, $name = 'authprimary')
	{
		return app('ldap')
				->addProvider($config, $name)
				->connect($name);
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
		$auth = $event->authorized;
		$user = $event->user;

		// There are multiple ou=allPeople trees
		// We need to add to:
		//     ou=allPeople,dc=rcac,dc=purdue,dc=edu
		//     ou=allPeople,dc=anvil,dc=rcac,dc=purdue,dc=edu
		$configall = array(
			[
				'name' => 'authprimaryall',
				'ldap' => $this->config('allPeople', 'internal'),
				'auth' => false
			]
		);
		if ($event->rolename)
		{
			$configall[] = [
				'name' => 'authprimaryall' . $event->rolename,
				'ldap' => $this->config('allPeople', $event->rolename),
				'auth' => $auth
			];
		}

		$config = array(
			[
				'name' => 'authprimary',
				'ldap' => $this->config('People', 'internal'),
				'auth' => $auth
			]
		);
		if ($event->rolename)
		{
			$config[] = [
				'name' => 'authprimary' . $event->rolename,
				'ldap' => $this->config('People', $event->rolename),
				'auth' => $auth
			];
		}

		//$config = $this->config('People', $event->rolename);

		// Make sure config is set
		if (empty($config))
		{
			return;
		}

		$results = array(
			'created' => array(),
			'updated' => array()
		);
		$status = 200;

		try
		{
			if (!$user->loginShell)
			{
				$user->loginShell = '/bin/bash';
			}
			if (!$user->uidNumber)
			{
				$f = $user->facets()->where('key', '=', 'uidNumber')->first();
				if ($f)
				{
					$user->uidNumber = $f->value;
				}
			}
			if (!$user->gidNumber)
			{
				$f = $user->facets()->where('key', '=', 'gidNumber')->first();
				if ($f)
				{
					$user->gidNumber = $f->value;
				}
			}
			if (!$user->telephoneNumber)
			{
				$f = $user->facets()->where('key', '=', 'telephoneNumber')->first();
				if ($f)
				{
					$user->telephoneNumber = $f->value;
				}
			}

			$userDns = array();
			foreach ($user->facets()->where('key', '=', 'x-xsede-userDn')->get() as $facet)
			{
				$userDns[] = $facet->value;
			}

			foreach ($configall as $conf)
			{
				$ldap = $this->connect($conf['ldap'], $conf['name']);

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
						'cn'            => $conf['auth'] ? $user->name : '-',
						'sn'            => $conf['auth'] ? $user->surname : '-',
						'loginShell'    => $conf['auth'] ? $user->loginShell : '/bin/false',
						//'homeDirectory' => $conf['auth'] ? '/home/' . $user->username : '/dev/null',
						'homeDirectory' => '/home/' . $user->username,
					];

					$entry = $ldap->make()->user($data);
					$entry->setAttribute('objectclass', ['x-xsede-xsedePerson', 'posixAccount', 'inetOrgPerson', 'top']);
					$entry->setDn('uid=' . $data['uid'] . ',' . $entry->getDnBuilder()->get());

					if (!$entry->save())
					{
						throw new Exception('Failed to make AuthPrimary ou=allPeople record', 500);
					}

					$results['created'][] = $data;
					$status = 201;
				}
				else
				{
					if ($conf['auth'])
					{
						// Update user record in ou=allPeople
						$result->setAttribute('cn', $user->name);
						$result->setAttribute('sn', $user->surname);
						$result->setAttribute('loginShell', $user->loginShell);
						$result->setAttribute('homeDirectory', '/home/' . $user->username);

						if (!$result->save())
						{
							throw new Exception('Failed to update AuthPrimary ou=allPeople record', 500);
						}

						$results['updated'][] = [
							'cn' => $user->name,
							'sn' => $user->surname,
							'loginShell' => $user->loginShell,
							'homeDirectory' => '/home/' . $user->username
						];
					}
					elseif ($user->name != $result->getAttribute('cn', 0))
					{
						$result->setAttribute('cn', $user->name);
						$result->setAttribute('sn', $user->surname);

						if (!$result->save())
						{
							throw new Exception('Failed to update AuthPrimary ou=allPeople record', 500);
						}

						$results['updated'][] = [
							'cn' => $user->name,
							'sn' => $user->surname,
						];
					}
				}
			}

			foreach ($config as $conf)
			{
				$ldap = $this->connect($conf['ldap'], $conf['name']);
				//$ldap = $this->connect($config);

				// Check for an existing record
				$result = $ldap->search()
					->where('uid', '=', $user->username)
					->first();

				if ($auth)
				{
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
							'x-xsede-userDn' => $userDns,
						];

						if (empty($data['x-xsede-userDn']))
						{
							unset($data['x-xsede-userDn']);
						}

						if ($user->telephoneNumber)
						{
							$data['telephoneNumber'] = $user->telephoneNumber;
						}

						$entry = $ldap->make()->user($data);
						$entry->setAttribute('objectclass', ['x-xsede-xsedePerson', 'posixAccount', 'inetOrgPerson', 'top']);

						$dn = $entry->getDnBuilder()->get();
						$uid = 'uid=' . $data['uid'];
						if (substr($dn, 0, strlen($uid)) != $uid)
						{
							$dn = $uid . ',' . $dn;
						}
						$entry->setDn($dn);

						if (!$entry->save())
						{
							throw new Exception('Failed to make AuthPrimary ou=People record', 500);
						}

						$results['created'][] = $data;
						$status = 201;
					}
					else
					{
						if ($user->name != $result->getAttribute('cn', 0))
						{
							$result->setAttribute('cn', $user->name);
							$result->setAttribute('sn', $user->surname);
							$result->setAttribute('givenName', $user->givenName);
							$result->setAttribute('gecos', $user->name);

							if (!$result->save())
							{
								throw new Exception('Failed to update AuthPrimary ou=People record', 500);
							}

							$results['updated'][] = [
								'cn'            => $user->name,
								'givenName'     => $user->givenName,
								'sn'            => $user->surname,
								'gecos'         => $user->name,
							];
						}
					}
				}
				else
				{
					// Remove unauthorized records
					if ($result && $result->exists)
					{
						$result->delete();
						$results['deleted'] = 'uid=' . $event->user->username;
					}
				}
			}
		}
		catch (Exception $e)
		{
			$status = 500;
			$results = ['error' => $e->getMessage()];
		}

		$this->log('authprimaryldap', __METHOD__, 'GET', $status, $results, 'uid=' . $event->user->username);
	}

	/**
	 * Display user profile info
	 *
	 * @param   UserBeforeDisplay  $event
	 * @return  void
	 */
	public function handleUserBeforeDisplay(UserBeforeDisplay $event)
	{
		$config = $this->config('People', 'anvil');

		if (empty($config))
		{
			return;
		}

		$user = $event->getUser();

		if (substr($user->username, 0, 2) != 'x-')
		{
			return;
		}

		if ($user->loginShell)
		{
			return;
		}

		try
		{
			$ldap = $this->connect($config);

			// Performing a query.
			$results = $ldap->search()
				->where('uid', '=', $user->username)
				->select(['cn', 'loginShell'])
				->get();

			$status = 404;

			if (!empty($results))
			{
				$status = 200;

				foreach ($results as $data)
				{
					if (isset($data['loginShell']))
					{
						$user->loginShell = $data['loginShell'][0];
					}
				}
			}
		}
		catch (\Exception $e)
		{
			$user->loginShell = false;

			$status = 500;
			$results = ['error' => $e->getMessage()];
		}

		$event->setUser($user);

		$this->log('authprimaryldap', __METHOD__, 'GET', $status, $results, 'uid=' . $user->username);
	}

	/**
	 * Search for users
	 *
	 * @param   ResourceMemberCreated  $event
	 * @return  void
	 */
	public function handleResourceMemberCreated(ResourceMemberCreated $event)
	{
		// Make sure config is set
		$configall = $this->config('allPeople', $event->resource->rolename);
		$config = $this->config('People', $event->resource->rolename);

		if (empty($configall) || empty($config))
		{
			return;
		}

		if (!in_array($event->resource->rolename, $this->whitelist))
		{
			return;
		}

		$user = $event->user;

		$user->pilogin = $user->pilogin ?: '';
		$user->loginShell = $user->loginShell ?: '/bin/bash';

		if (!$user->primarygroup)
		{
			if ($event->resource->rolename != 'peregrn1')
			{
				$user->primarygroup = 'student';
			}
			else
			{
				// DO NOT use "Calumet" even though this is how it shows up in our LDAP
				// "Calumet" is a different group in ACMaint, we want "calumet", gid 5882
				$user->primarygroup = 'calumet';
			}
		}

		// Check for cached metadata
		foreach (['uidNumber', 'gidNumber', 'telephoneNumber'] as $key)
		{
			if (!$user->{$key})
			{
				$f = $user->facets()->where('key', '=', $key)->first();

				if ($f)
				{
					$user->{$key} = $f->value;
				}
			}
		}

		$userDns = array();
		foreach ($user->facets()->where('key', '=', 'x-xsede-userDn')->get() as $facet)
		{
			$userDns[] = $facet->value;
		}

		//$results = array();
		$status = 200;

		try
		{
			$ldap = $this->connect($configall, 'authprimaryall');

			// Check for an existing record in ou=allPeople
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
					'cn'            => $user->name,
					'sn'            => $user->surname,
					'loginShell'    => $user->loginShell,
					'homeDirectory' => '/home/' . $user->username,
				];

				$entry = $ldap->make()->user($data);
				$entry->setAttribute('objectclass', ['posixAccount', 'inetOrgPerson', 'top']);
				$entry->setDn('uid=' . $data['uid'] . ',' . $entry->getDnBuilder()->get());

				if (!$entry->save())
				{
					throw new Exception('Failed to make AuthPrimary ou=allPeople record', 500);
				}

				$results['created'] = $data;
				$status = 201;
			}
			else
			{
				// Update user record in ou=allPeople
				$result->setAttribute('cn', $user->name);
				$result->setAttribute('sn', $user->surname);
				$result->setAttribute('loginShell', $user->loginShell);
				$result->setAttribute('homeDirectory', '/home/' . $user->username);

				if (!$result->save())
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

			// Check for an existing record in the ou=People (i.e., authorized) tree
			$ldap = $this->connect($config);

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
					'uid'            => $user->username,
					'uidNumber'      => $user->uidNumber,
					'gidNumber'      => $user->gidNumber,
					'cn'             => $user->name,
					'givenName'      => $user->givenName,
					'sn'             => $user->surname,
					'loginShell'     => $user->loginShell,
					'homeDirectory'  => '/home/' . $user->username,
					'gecos'          => $user->name,
					'x-xsede-userDn' => $userDns,
				];

				if (empty($data['x-xsede-userDn']))
				{
					unset($data['x-xsede-userDn']);
				}

				if ($user->telephoneNumber)
				{
					$data['telephoneNumber'] = $user->telephoneNumber;
				}

				$entry = $ldap->make()->user($data);
				$entry->setAttribute('objectclass', ['posixAccount', 'inetOrgPerson', 'top']);
				$entry->setDn('uid=' . $data['uid'] . ',' . $entry->getDnBuilder()->get());

				if (!$entry->save())
				{
					throw new Exception('Failed to make AuthPrimary ou=People record', 500);
				}

				$results['created_auth'] = $data;
				$status = 201;
			}
			else
			{
				// Update user record in ou=People
				$result->setAttribute('cn', $user->name);
				$result->setAttribute('sn', $user->surname);
				$result->setAttribute('loginShell', $user->loginShell);
				$result->setAttribute('homeDirectory', '/home/' . $user->username);

				if (!$result->save())
				{
					throw new Exception('Failed to update AuthPrimary ou=People record', 500);
				}

				$results['updated_auth'] = [
					'cn' => $user->name,
					'sn' => $user->surname,
					'loginShell' => $user->loginShell,
					'homeDirectory' => '/home/' . $user->username
				];
			}
		}
		catch (Exception $e)
		{
			$status = 500;
			$results = ['error' => $e->getMessage()];
		}

		$event->status = $status > 400 ? -1 : 1;

		$this->log('authprimaryldap', __METHOD__, 'POST', $status, $results, 'uid=' . $event->user->username);
	}

	/**
	 * Get status for a user
	 *
	 * @param   ResourceMemberStatus   $event
	 * @return  void
	 */
	public function handleResourceMemberStatus(ResourceMemberStatus $event)
	{
		// Make sure config is set
		$config = $this->config('People', $event->resource->rolename);

		if (empty($config))
		{
			return;
		}

		if (!in_array($event->resource->rolename, $this->whitelist))
		{
			return;
		}

		$results = array();

		try
		{
			// Check for an existing record in the ou=People (i.e., authorized) tree
			$ldap = $this->connect($config);

			$result = $ldap->search()
				->select(['dn', 'loginShell'])
				->where('uid', '=', $event->user->username)
				->first();

			if (!$result || !$result->exists)
			{
				$event->status = 1;
			}
			else
			{
				if (isset($result['loginShell']))
				{
					$event->user->loginShell = $result['loginShell'][0];
				}

				$results['dn'] = $result->getAttribute('distinguishedname', 0);
				$event->status = 3;
			}

			$status = 200;
		}
		catch (\Exception $e)
		{
			$event->status = -1;

			$status  = 500;
			$results = ['error' => $e->getMessage()];
		}

		$this->log('authprimaryldap', __METHOD__, 'GET', $status, $results, 'uid=' . $event->user->username, $event->user->id);
	}

	/**
	 * Handle removal of resource/member association
	 *
	 * @param   ResourceMemberDeleted   $event
	 * @return  void
	 */
	public function handleResourceMemberDeleted(ResourceMemberDeleted $event)
	{
		// Make sure config is set
		$configall = $this->config('allPeople', $event->resource->rolename);
		$config = $this->config('People', $event->resource->rolename);

		if (empty($configall) || empty($config))
		{
			return;
		}

		if (!in_array($event->resource->rolename, $this->whitelist))
		{
			return;
		}

		$results = array();

		try
		{
			// Check for an existing record in the ou=People (i.e., authorized) tree
			$ldap = $this->connect($config);

			$result = $ldap->search()
				->where('uid', '=', $event->user->username)
				->first();

			if ($result && $result->exists)
			{
				if (!$result->delete())
				{
					throw new Exception('Failed to delete AuthPrimary ou=People record for uid=' . $event->user->username, 500);
				}
			}

			// Note: We only delete from ou=People, and NOT from ou=allPeople
			// For ou=allPeople, we mask out some fields
			$ldap = $this->connect($configall, 'authprimaryall');

			$result = $ldap->search()
				->where('uid', '=', $event->user->username)
				->first();

			if ($result && $result->exists)
			{
				$result->cn = '-';
				$result->sn = '-';
				$result->longinShell = '/bin/false';
				$result->homeDirectory = '/dev/null';

				if (!$result->update())
				{
					throw new Exception('Failed to mask AuthPrimary ou=allPeople record for uid=' . $event->user->username, 500);
				}
			}

			$status = 200;
		}
		catch (\Exception $e)
		{
			$status  = 500;
			$results = ['error' => $e->getMessage()];
		}

		$this->log('authprimaryldap', __METHOD__, 'DELETE', $status, $results, 'uid=' . $event->user->username, $event->user->id);
	}

	/**
	 * Handle a UnixGroup sync event
	 * 
	 * This will add entries to the AuthPrimary LDAP
	 *
	 * @param   UnixGroupCreating  $event
	 * @return  void
	 */
	public function handleUnixGroupCreating(UnixGroupCreating $event)
	{
		// Make sure config is set
		$config = $this->config('Groups');

		if (empty($config))
		{
			return;
		}

		$unixgroup = $event->unixgroup;

		if (!$unixgroup || substr($unixgroup->longname, 0, 2) != 'x-')
		{
			return;
		}

		$results = array();
		$status = 200;

		try
		{
			$ldap = $this->connect($config, 'authprimarygroups');

			// Check for an existing record
			$result = $ldap->search()
				->where('cn', '=', $unixgroup->longname)
				->first();

			if (!$result || !$result->exists)
			{
				// Sample LDAP entry
				//
				// # x-peb216887, Groups, anvil.rcac.purdue.edu
				// dn: cn=x-peb216887,ou=Groups,dc=anvil,dc=rcac,dc=purdue,dc=edu
				// cn: x-peb216887
				// gidNumber: 7000167
				// objectClass: posixGroup
				// objectClass: top
				// memberUid: x-username1
				// memberUid: x-username2

				// Create user record in ou=allPeople
				$data = [
					'cn'        => $unixgroup->longname,
					'gidNumber' => $unixgroup->unixgid,
					//'memberUid' => $usernames
				];

				$entry = $ldap->make()->group($data);
				$entry->setAttribute('objectclass', ['posixGroup', 'top']);
				$entry->setDn('cn=' . $data['cn'] . ',' . $entry->getDnBuilder()->get());

				if (!$entry->save())
				{
					throw new Exception('Failed to make AuthPrimary ou=Groups record', 500);
				}

				$results['created'] = $data;
				$status = 201;
			}
		}
		catch (Exception $e)
		{
			$status = 500;
			$results['error'] = $e->getMessage();
		}

		$this->log('ldap', __METHOD__, 'POST', $status, $results, 'cn=' . $event->unixgroup->longname);
	}

	/**
	 * Handle a unix group being deleted
	 *
	 * @param   UnixGroupDeleted  $event
	 * @return  void
	 */
	public function handleUnixGroupDeleted(UnixGroupDeleted $event)
	{
		// Make sure config is set
		$config = $this->config('Groups');

		if (empty($config))
		{
			return;
		}

		$unixgroup = $event->unixgroup;

		if (!$unixgroup || substr($unixgroup->longname, 0, 2) != 'x-')
		{
			return;
		}

		try
		{
			// Check for an existing record in the ou=Groups tree
			$ldap = $this->connect($config, 'authprimarygroups');

			$result = $ldap->search()
				->where('cn', '=', $unixgroup->longname)
				->first();

			if ($result && $result->exists)
			{
				if (!$result->delete())
				{
					throw new Exception('Failed to delete unix group `' . $unixgroup->longname . '`', 500);
				}
			}

			$status = 204;
			$body   = null;
		}
		catch (\Exception $e)
		{
			$status = $e->getCode();
			$body   = ['error' => $e->getMessage()];
		}

		$this->log('authprimaryldap', __METHOD__, 'DELETE', $status, $body, 'cn=' . $unixgroup->longname);
	}

	/**
	 * Handle a UnixGroupMemberCreated sync event
	 * 
	 * This will add entries to the AuthPrimary LDAP
	 *
	 * @param   UnixGroupMemberCreated  $event
	 * @return  void
	 */
	public function handleUnixGroupMemberCreated(UnixGroupMemberCreated $event)
	{
		$this->syncUnixGroupMembers($event);
	}

	/**
	 * Handle a UnixGroupMemberDeleted sync event
	 * 
	 * This will add entries to the AuthPrimary LDAP
	 *
	 * @param   UnixGroupMemberDeleted  $event
	 * @return  void
	 */
	public function handleUnixGroupMemberDeleted(UnixGroupMemberDeleted $event)
	{
		$this->syncUnixGroupMembers($event);
	}

	/**
	 * Handle a UnixGroup sync event
	 * 
	 * This will add entries to the AuthPrimary LDAP
	 *
	 * @param   object  $event
	 * @return  void
	 */
	public function syncUnixGroupMembers($event)
	{
		// Make sure config is set
		$config = $this->config('Groups');

		if (empty($config))
		{
			return;
		}

		$unixgroup = $event->member->unixgroup;

		if (!$unixgroup || substr($event->member->user->username, 0, 2) != 'x-')
		{
			return;
		}

		$results = array();
		$status = 200;

		try
		{
			$ldap = $this->connect($config, 'authprimarygroups');

			// Check for an existing record
			$result = $ldap->search()
				->where('cn', '=', $unixgroup->longname)
				->first();

			if ($result && $result->exists)
			{
				$members = $unixgroup->members;

				$usernames = array();
				foreach ($members as $member)
				{
					if (!$member->user || substr($member->user->username, 0, 2) != 'x-')
					{
						continue;
					}

					$usernames[] = $member->user->username;
				}
				$usernames = array_unique($usernames);

				$result->setAttribute('memberUid', $usernames);

				if (!$result->save())
				{
					throw new Exception('Failed to update AuthPrimary ou=Groups record', 500);
				}

				$results['updated'] = [
					'cn' => $unixgroup->longname,
					'gidNumber' => $unixgroup->unixgid,
					'memberUid' => $usernames
				];
			}
		}
		catch (Exception $e)
		{
			$status = 500;
			$results['error'] = $e->getMessage();
		}

		$this->log('authprimaryldap', __METHOD__, 'PUT', $status, $results, 'cn=' . $unixgroup->longname);
	}
}
