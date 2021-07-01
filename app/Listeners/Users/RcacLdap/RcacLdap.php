<?php
namespace App\Listeners\Users\RcacLdap;

use App\Modules\Users\Events\UserSearching;
use App\Modules\Users\Events\UserBeforeDisplay;
//use App\Modules\Users\Events\UserDisplay;
use App\Modules\Users\Events\UserLookup;
use App\Modules\Users\Models\User;
use App\Modules\Courses\Events\CourseEnrollment;
use App\Modules\Resources\Events\ResourceMemberStatus;
use App\Modules\Groups\Events\UnixGroupFetch;
use App\Modules\History\Traits\Loggable;
use App\Halcyon\Utility\Str;

/**
 * User listener for RCAC Ldap
 */
class RcacLdap
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
		$events->listen(UserSearching::class, self::class . '@handleUserSearching');
		$events->listen(UserBeforeDisplay::class, self::class . '@handleUserBeforeDisplay');
		$events->listen(UserLookup::class, self::class . '@handleUserLookup');
		//$events->listen(UserDisplay::class, self::class . '@handleUserDisplay');
		$events->listen(ResourceMemberStatus::class, self::class . '@handleResourceMemberStatus');
		$events->listen(UnixGroupFetch::class, self::class . '@handleUnixGroupFetch');
		$events->listen(CourseEnrollment::class, self::class . '@handleCourseEnrollment');
	}

	/**
	 * Get LDAP config
	 *
	 * @return  array
	 */
	private function config()
	{
		if (!app()->has('ldap'))
		{
			return array();
		}

		return config('ldap.rcac', []);
	}

	/**
	 * Establish LDAP connection
	 *
	 * @param   array  $config
	 * @return  object
	 */
	private function connect($config)
	{
		return app('ldap')
				->addProvider($config, 'rcac')
				->connect('rcac');
	}

	/**
	 * Search for users
	 *
	 * @param   UserSearching  $event
	 * @return  void
	 */
	public function handleUserSearching(UserSearching $event)
	{
		$config = $this->config();

		if (empty($config))
		{
			return;
		}

		$usernames = array();
		foreach ($event->results as $user)
		{
			$usernames[] = $user->username;
		}

		// We already found a macth
		if (in_array($event->search, $usernames))
		{
			return;
		}

		try
		{
			$ldap = $this->connect($config);

			$search = $event->search;
			$results = array();
			$status = 404;

			// Try finding by email address
			if (preg_match("/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}/", $search))
			{
				$results = $ldap->search()
					->where('mailHost', '=', $search)
					->select(['cn', 'uid'])
					->get();

				if (empty($results))
				{
					$search = strstr($search, '@', true);
				}
			}

			// Performing a query.
			if (empty($results))
			{
				$results = $ldap->search()
					->where('uid', 'contains', $search)
					//->orWhere('uid', '=', $search . '*')
					->select(['cn', 'uid'])
					->get();
			}

			if (!empty($results))
			{
				$status = 200;

				foreach ($results as $result)
				{
					// We have a local record for this user
					if (in_array($result['uid'][0], $usernames))
					{
						continue;
					}

					$user = User::findByUsername($result['uid'][0]);

					if (!$user || !$user->id)
					{
						$user = new User;
						$user->name = Str::properCaseNoun($result['cn'][0]);
						$user->username = $result['uid'][0];
						$user->email = $user->username . '@purdue.edu';
						//$user->id = $user->username;
					}

					$usernames[] = $user->username;

					$event->results->push($user);
				}

				// Update pagination information
				$data = $event->results->toArray();

				$query = parse_url($data['first_page_url'], PHP_URL_QUERY);
				parse_str($query, $output);

				$itemsTransformedAndPaginated = new \Illuminate\Pagination\LengthAwarePaginator(
					$event->results->getCollection(),
					count($data['data']),
					$event->results->perPage(),
					$event->results->currentPage(),
					[
						'path' => \Request::url(),
						'query' => $output
					]
				);

				$event->results = $itemsTransformedAndPaginated;
			}
		}
		catch (\Exception $e)
		{
			$status = 500;
			$results = ['error' => $e->getMessage()];
		}

		$this->log('ldap', __METHOD__, 'GET', $status, $results, 'uid=' . $event->search);
	}

	/**
	 * Display user profile info
	 *
	 * @param   UserBeforeDisplay  $event
	 * @return  void
	 */
	public function handleUserBeforeDisplay(UserBeforeDisplay $event)
	{
		$config = $this->config();

		if (empty($config))
		{
			return;
		}

		$user = $event->getUser();

		try
		{
			$ldap = $this->connect($config);

			// Performing a query.
			$results = $ldap->search()
				->where('uid', '=', $user->username)
				->select(['loginShell', 'homeDirectory'])
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

					if (isset($data['homeDirectory']))
					{
						$user->homeDirectory = $data['homeDirectory'][0];
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
	
		$this->log('ldap', __METHOD__, 'GET', $status, $results, 'uid=' . $user->username);
	}

	/**
	 * Display data for a user
	 *
	 * @param   UserDisplay  $event
	 * @return  void
	 */
	public function handleUserDisplay(UserDisplay $event)
	{
		$config = $this->config();

		if (empty($config) || !auth()->user()->can('manage users'))
		{
			return;
		}

		$content = null;
		$user = $event->getUser();

		$r = ['section' => 'rcacldap'];
		if (auth()->user()->id != $user->id)
		{
			$r['u'] = $user->id;
		}

		app('translator')->addNamespace(
			'listener.users.rcacldap',
			__DIR__ . '/lang'
		);

		if ($event->getActive() == 'rcacldap')
		{
			try
			{
				app('pathway')
					->append(
						trans('listener.users.rcacldap::rcacldap.title'),
						route('site.users.account.section', $r)
					);

				// Performing a query.
				$ldap = $this->connect($config);

				$results = $ldap->search()
					->where('uid', '=', $user->username)
					->first();

				$status = 404;

				if (!empty($results))
				{
					$status = 200;
				}

				app('view')->addNamespace(
					'listener.users.footprints',
					__DIR__ . '/views'
				);

				$content = view('listener.users.footprints::profile', [
					'user'    => $user,
					'results' => $results,
				]);
			}
			catch (\Exception $e)
			{
				$status = 500;
				$results = ['error' => $e->getMessage()];
			}

			$this->log('ldap', __METHOD__, 'GET', $status, $results, 'uid=' . $user->username);
		}

		$event->addSection(
			route('site.users.account.section', $r),
			trans('listener.users.rcacldap::rcacldap.title'),
			($event->getActive() == 'rcacldap'),
			$content
		);
	}

	/**
	 * Display user profile info
	 *
	 * @param   ResourceMemberStatus  $event
	 * @return  void
	 */
	public function handleResourceMemberStatus(ResourceMemberStatus $event)
	{
		$config = $this->config();

		if (empty($config))
		{
			return;
		}

		$event->user->pilogin = '';
		$event->user->loginShell = '/bin/bash';

		if ($event->resource->rolename != 'peregrn1')
		{
			$event->user->primarygroup = 'student';
		}
		else
		{
			// DO NOT use "Calumet" even though this is how it shows up in our LDAP
			// "Calumet" is a different group in ACMaint, we want "calumet", gid 5882
			$event->user->primarygroup = 'calumet';
		}

		try
		{
			$ldap = $this->connect($config);

			// Performing a query.
			$results = $ldap->search()
				->where('uid', '=', $event->user->username)
				->select(['loginShell', 'authorizedBy', 'gidNumber', 'uidNumber'])
				->get();

			$status = 404;

			if (!empty($results) && count($results) > 0)
			{
				$status = 200;

				$event->user->loginShell = $results[0]['loginshell'][0];

				$meta = $event->user->facets->firstWhere('key', '=', 'loginShell');
				if (!$meta)
				{
					$event->user->addFacet('loginShell', $event->user->loginShell, 0, 1);
				}

				if (isset($results[0]['authorizedby']))
				{
					$event->user->pilogin = $results[0]['authorizedby'][0];
				}

				if (isset($results[0]['uidNumber']))
				{
					$event->user->uidNumber = $results[0]['uidnumber'][0];

					$meta = $event->user->facets->firstWhere('key', '=', 'uidNumber');
					if (!$meta)
					{
						$event->user->addFacet('uidNumber', $event->user->uidNumber, 0, 1);
					}
				}

				// Resolve group name
				$config = config('ldap.rcac_group', []);

				if (!empty($config))
				{
					$gid = $results[0]['gidnumber'][0];
					$event->user->gidNumber = $gid;

					$meta = $event->user->facets->firstWhere('key', '=', 'gidNumber');
					if (!$meta)
					{
						$event->user->addFacet('gidNumber', $event->user->gidNumber, 0, 1);
					}

					$ldap_group = $this->connect($config);

					$data = array();
					$data = $ldap_group->search()
						->where('gidNumber', '=', $gid)
						->select(['cn', 'gidNumber'])
						->get();

					if (!empty($data))
					{
						$event->user->primarygroup = $data[0]['cn'][0];
					}
				}
			}
		}
		catch (\Exception $e)
		{
			//$event->status = -1;

			$status = 500;
			$results = ['error' => $e->getMessage()];
		}

		$this->log('ldap', __METHOD__, 'GET', $status, $results, 'uid=' . $event->user->username);
	}

	/**
	 * Search for unixgroup
	 *
	 * @param   UnixGroupFetch  $event
	 * @return  void
	 */
	public function handleUnixGroupFetch(UnixGroupFetch $event)
	{
		if (!app()->has('ldap'))
		{
			return;
		}

		$config = config('ldap.rcac_group', []);

		if (empty($config))
		{
			return;
		}

		try
		{
			$ldap = $this->connect($config);

			// Performing a query.
			$results = $ldap->search()
				->where('cn', '=', $event->name)
				->get();

			$status = 404;

			if (!empty($results))
			{
				$status = 200;

				$event->results = $results;
				// Gather information from LDAP
				/*
					$this->primarygroup = $rows[0]['cn'][0];

					// Un-prefixed (lacking "rcac-") version of group name exists in LDAP
					$rows = $ldap_group->query('cn=rcac-' . $base, array(), $data);

					if ($rows == 0)
					{
						return 409;
					}
					// If the above was not found, then the prefixed ("rcac-") version of
					// group name also exists in LDAP, so it should be safe to proceed, since
					// any conflict must have already been resolved by manual intervention.
				*/
			}
		}
		catch (\Exception $e)
		{
			$status = 500;
			$results = ['error' => $e->getMessage()];
		}

		$this->log('ldap', __METHOD__, 'GET', $status, $results, 'cn=' . $event->name);
	}

	/**
	 * Search for unixgroup
	 *
	 * @param   UnixGroupMemberCreating  $event
	 * @return  void
	 */
	public function handleUnixGroupMemberCreating(UnixGroupMemberCreating $event)
	{
		if (!app()->has('ldap'))
		{
			return;
		}

		$config = config('ldap.rcac_group', []);

		if (empty($config))
		{
			return;
		}

		try
		{
			$ldap = $this->connect($config);

			// Performing a query.
			$results = $ldap->search()
				->where('uid', '=', $event->member->user->username)
				->get();

			$status = 404;

			if (!empty($results))
			{
				$status = 200;

				$event->results = $results;
				// Gather information from LDAP
				/*
					$this->primarygroup = $rows[0]['cn'][0];

					// Un-prefixed (lacking "rcac-") version of group name exists in LDAP
					$rows = $ldap_group->query('cn=rcac-' . $base, array(), $data);

					if ($rows == 0)
					{
						return 409;
					}
					// If the above was not found, then the prefixed ("rcac-") version of
					// group name also exists in LDAP, so it should be safe to proceed, since
					// any conflict must have already been resolved by manual intervention.
				*/
			}
		}
		catch (\Exception $e)
		{
			$status = 500;
			$results = ['error' => $e->getMessage()];
		}

		$this->log('ldap', __METHOD__, 'GET', $status, $results, 'cn=' . $event->name);
	}

	/**
	 * Lookup enrollment for an account
	 *
	 * @param   CourseEnrollment  $event
	 * @return  void
	 */
	public function handleCourseEnrollment(CourseEnrollment $event)
	{
		if (!app()->has('ldap'))
		{
			return;
		}

		$config = config('ldap.rcac', []);

		if (empty($config))
		{
			return;
		}

		try
		{
			$ldap = $this->connect($config);

			// Performing a query.
			$ldapdata = $ldap->search()
				->where('host', '=', 'scholar.rcac.purdue.edu') //$event->account->resource->rolename . '.rcac.purdue.edu'
				->get();

			$status = 404;
			$results = array();

			if (!empty($ldapdata))
			{
				$status = 200;

				$users = $event->users;
				$ldap_users   = array();
				$system_users = array();

				foreach ($ldapdata as $row)
				{
					$foo = array();

					// Try to subtract staff users. Subtact anyone in xenon.
					$rows = $ldap->search()
						->where('uid', '=', $row['uid'][0])
						->where('host', '=', 'xenon.rcac.purdue.edu')
						->get();

					if (count($rows) == 0)
					{
						$ldap_users[$row['uid'][0]] = $row['uid'][0];
					}
					else
					{
						$system_users[$row['uid'][0]] = $row['uid'][0];
					}

					$rows = $ldap->search()
						->select(array('classification'))
						->where('uid', '=', $row['uid'][0])
						->get();

					if (count($rows) > 0)
					{
						if (isset($rows[0]['classification'][0]) && $rows[0]['classification'][0] == 'System Account')
						{
							$system_users[$row['uid'][0]] = $row['uid'][0];
						}
						if (isset($rows[0]['classification'][0]) && $rows[0]['classification'][0] == 'Software Account')
						{
							$system_users[$row['uid'][0]] = $row['uid'][0];
						}
					}
				}

				$create_users = array_diff($users, $ldap_users);
				$create_users = array_diff($create_users, $system_users);
				$remove_users = array_diff($ldap_users, $users);
				$remove_users = array_diff($remove_users, $system_users);

				$event->create_users = $create_users;
				$event->remove_users = $remove_users;

				$results = [
					'create_users' => $create_users,
					'remove_users' => $remove_users
				];
			}
		}
		catch (\Exception $e)
		{
			$status = 500;
			$results = ['error' => $e->getMessage()];
		}

		$this->log('ldap', __METHOD__, 'GET', $status, $results, 'host=scholar.rcac.purdue.edu');
	}

	/**
	 * Handle a user lookup event
	 * 
	 * Look for a user in the Purdue LDAP based on the specified
	 * criteria and return a User object based on the first match.
	 *
	 * @param   UserLookup  $event
	 * @return  void
	 */
	public function handleUserLookup(UserLookup $event)
	{
		$config = $this->config();

		if (empty($config))
		{
			return;
		}

		if (!empty($event->results))
		{
			return;
		}

		$criteria = $event->criteria;
		$query = [];
		$results = array();

		foreach ($criteria as $key => $val)
		{
			switch ($key)
			{
				case 'puid':
				case 'organization_id':
					// `employeeNumber` needs to be 10 digits in length for the query to work
					//    ex: 12345678 -> 0012345678
					$val = str_pad($val, 10, '0', STR_PAD_LEFT);
					$query[] = ['employeeNumber', '=', $val];
				break;

				case 'username':
					$query[] = ['uid', '=', $val];
				break;

				case 'host':
					$query[] = [$key, '=', $val];
				break;

				case 'name':
				default:
					$query[] = ['cn', '=', $val];
				break;
			}
		}

		if (empty($query))
		{
			return;
		}

		try
		{
			$ldap = $this->connect($config);

			$status = 404;

			// Performing a query.
			$data = $ldap->search()
				->where($query)
				->select(['cn', 'uid', 'employeeNumber'])
				->get();

			if (!empty($data))
			{
				$status = 200;

				foreach ($data as $key => $result)
				{
					$user = User::findByUsername($result['uid'][0]);
					if (!$user || !$user->id)
					{
						$user = new User;
					}
					$user->name = $result['cn'][0];
					$user->getUserUsername()->username = $result['uid'][0];
					$user->puid = $result['employeeNumber'][0];

					//$event->user = $user;
					//break;
					$results[$key] = $user;
				}
			}

			$event->results = $results;
		}
		catch (\Exception $e)
		{
			$status = 500;
			$results = ['error' => $e->getMessage()];
		}

		$this->log('ldap', __METHOD__, 'GET', $status, $results, json_encode($query));
	}
}
