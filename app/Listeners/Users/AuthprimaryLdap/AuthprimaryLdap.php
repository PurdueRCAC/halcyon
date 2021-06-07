<?php
namespace App\Listeners\Users\AuthprimaryLdap;

use App\Modules\Users\Events\UserSync;
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
		'anvil'
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

		// User/Resource
		$events->listen(ResourceMemberCreated::class, self::class . '@handleResourceMemberCreated');
		$events->listen(ResourceMemberStatus::class, self::class . '@handleResourceMemberStatus');
		$events->listen(ResourceMemberDeleted::class, self::class . '@handleResourceMemberDeleted');

		// Unix Groups
		//$events->listen(UnixGroupCreating::class, self::class . '@handleUnixGroupCreating');
		//$events->listen(UnixGroupDeleted::class, self::class . '@handleUnixGroupDeleted');
		//$events->listen(UnixGroupMemberCreated::class, self::class . '@handleUnixGroupMemberCreated');
		//$events->listen(UnixGroupMemberDeleted::class, self::class . '@handleUnixGroupMemberDeleted');
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

		$auth = $event->authorized;
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
				$entry->setAttribute('objectclass', ['posixAccount', 'inetOrgPerson', 'top']);
				$entry->setDn('uid=' . $data['uid'] . ',' . $entry->getDnBuilder()->get());

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

			$ldap = $this->connectPeople($config);

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
					];

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
			}
			else
			{
				// Remove unauthorized records
				if ($result && $result->exists)
				{
					$result->delete();
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

	/**
	 * Search for users
	 *
	 * @param   ResourceMemberCreated  $event
	 * @return  void
	 */
	public function handleResourceMemberCreating(ResourceMemberCreating $event)
	{
		// Make sure config is set
		$configall = $this->config('all');
		$config = $this->config('authorized');

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

		//$results = array();
		$status = 200;

		try
		{
			$ldap = $this->connectAllPeople($configall);

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

			// Check for an existing record in the ou=People (i.e., authorized) tree
			$ldap = $this->connectPeople($config);

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
		$config = $this->config('authorized');

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
			$ldap = $this->connectPeople($config);

			$result = $ldap->search()
				->where('uid', '=', $event->user->username)
				->first();

			if (!$result || !$result->exists)
			{
				$event->status = 1;
			}
			else
			{
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
		$configall = $this->config('all');
		$config = $this->config('authorized');

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
			$ldap = $this->connectPeople($config);

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
			$ldap = $this->connectAllPeople($configall);

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
	 * Handle a unix group being created
	 *
	 * @param   UnixGroupCreated  $event
	 * @return  void
	 */
	public function handleUnixGroupCreated(UnixGroupCreated $event)
	{
		$config = $this->config('groups');

		if (empty($config))
		{
			return;
		}

		try
		{
			// Check for an existing record in the ou=Groups tree
			$ldap = $this->connectGroups($config);

			$result = $ldap->search()
				->where('cn', '=', $event->unixgroup->longname)
				->first();

			$data = [
				'cn' => $event->unixgroup->longname,
				'gidNumber' => $event->unixgroup->unixgroupid,
			];

			if (!$result || !$result->exists)
			{
				$entry = $ldap->make()->group($data);
				$entry->setAttribute('objectclass', ['posixGroup', 'top']);
				$entry->setDn('cn=' . $data['cn'] . ',' . $entry->getDnBuilder()->get());

				if (!$entry->save())
				{
					throw new Exception('Failed to create unix group `' . $event->unixgroup->longname . '`', 500);
				}
			}

			$status = 201;
			$body   = $data;
		}
		catch (\Exception $e)
		{
			$status = $e->getCode();
			$body   = ['error' => $e->getMessage()];
		}

		$this->log('authprimaryldap', __METHOD__, 'DELETE', $status, $body, 'cn=' . $event->unixgroup->longname);
	}

	/**
	 * Handle a unix group being deleted
	 *
	 * @param   UnixGroupDeleted  $event
	 * @return  void
	 */
	public function handleUnixGroupDeleted(UnixGroupDeleted $event)
	{
		$config = $this->config('groups');

		if (empty($config))
		{
			return;
		}

		try
		{
			// Check for an existing record in the ou=Groups tree
			$ldap = $this->connectGroups($config);

			$result = $ldap->search()
				->where('cn', '=', $event->unixgroup->longname)
				->first();

			if ($result && $result->exists)
			{
				if (!$result->delete())
				{
					throw new Exception('Failed to delete unix group `' . $event->unixgroup->longname . '`', 500);
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

		$this->log('authprimaryldap', __METHOD__, 'DELETE', $status, $body, 'cn=' . $event->unixgroup->longname);
	}

	/**
	 * Handle a unix group member being created
	 *
	 * @param   UnixGroupMemberCreated  $event
	 * @return  void
	 */
	public function handleUnixGroupMemberCreated(UnixGroupMemberCreated $event)
	{
		$config = $this->config('groups');

		if (empty($config))
		{
			return;
		}

		$member = $event->member;
		$url = 'cn=' . $member->unixgroup->longname;

		try
		{
			// Check for an existing record in the ou=Groups tree
			$ldap = $this->connectGroups($config);

			$result = $ldap->search()
				->where('cn', '=', $member->unixgroup->longname)
				->first();

			if (!$result || !$result->exists)
			{
				throw new Exception('Unix Group `' . $member->unixgroup->longname . '` not found.', 404);
			}

			// Get the list of current members and set that on the unixgroup's entry
			$members = $member->unixgroup->members()
				->withTrashed()
				->whereIsActive()
				->get();

			$usernames = array();
			foreach ($members as $m)
			{
				$usernames[] = $m->user->username;
			}
			$usernames = array_unique($usernames);

			$result->setAttribute('memberUid', $usernames);

			if (!$result->save())
			{
				throw new Exception('Failed to set members on unix group `' . $member->unixgroup->longname . '`', 500);
			}

			$status = 201;
			$body   = $usernames;
		}
		catch (\Exception $e)
		{
			$status = $e->getCode();
			$body   = ['error' => $e->getMessage()];
		}

		$this->log('authprimaryldap', __METHOD__, 'POST', $status, $body, $url);
	}

	/**
	 * Handle a unix group being deleted
	 *
	 * @param   UnixGroupMemberDeleted  $event
	 * @return  void
	 */
	public function handleUnixGroupMemberDeleted(UnixGroupMemberDeleted $event)
	{
		$config = $this->config('groups');

		if (empty($config))
		{
			return;
		}

		$member = $event->member;
		$url = 'cn=' . $member->unixgroup->longname;

		try
		{
			// Check for an existing record in the ou=Groups tree
			$ldap = $this->connectGroups($config);

			$result = $ldap->search()
				->where('cn', '=', $member->unixgroup->longname)
				->first();

			if (!$result || !$result->exists)
			{
				throw new Exception('Unix Group `' . $member->unixgroup->longname . '` not found.', 404);
			}

			// Get the list of current members and set that on the unixgroup's entry
			$members = $member->unixgroup->members()
				->withTrashed()
				->whereIsActive()
				->get();

			$usernames = array();
			foreach ($members as $m)
			{
				if ($m->user && $m->userid != $member->userid)
				{
					$usernames[] = $m->user->username;
				}
			}
			$usernames = array_unique($usernames);

			$result->setAttribute('memberUid', $usernames);

			if (!$result->save())
			{
				throw new Exception('Failed to set members on unix group `' . $member->unixgroup->longname . '`', 500);
			}

			$status = 204;
			$body   = $usernames;
		}
		catch (\Exception $e)
		{
			$status = $e->getCode();
			$body   = ['error' => $e->getMessage()];
		}

		$this->log('authprimaryldap', __METHOD__, 'DELETE', $status, $body, $url);
	}
}
