<?php
namespace App\Listeners\Users\RcacLdap;

use App\Modules\Users\Events\UserSearching;
use App\Modules\Users\Events\UserBeforeDisplay;
use App\Modules\Users\Models\User;
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
		$events->listen(ResourceMemberStatus::class, self::class . '@handleResourceMemberStatus');
		$events->listen(UnixGroupFetch::class, self::class . '@handleUnixGroupFetch');
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

			// Performing a query.
			$results = $ldap->search()
				->where('uid', '=', $event->search)
				->select(['cn', 'uid'])
				->get();

			$status = 404;

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

					$user = new User;
					$user->name = Str::properCaseNoun($result['cn'][0]);
					$user->username = $result['uid'][0];
					$user->email = $user->username . '@purdue.edu';

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
						$user->loginshell = $data['loginShell'][0];
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
			$user->loginshell = false;

			$status = 500;
			$results = ['error' => $e->getMessage()];
		}

		$event->setUser($user);
	
		$this->log('ldap', __METHOD__, 'GET', $status, $results, 'uid=' . $user->username);
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
		$event->user->loginshell = '/bin/bash';

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
				->select(['loginShell', 'authorizedBy', 'gidNumber'])
				->get();

			$status = 404;

			if (!empty($results))
			{
				$status = 200;

				$this->loginshell = $results[0]['loginshell'][0];

				if (isset($results[0]['authorizedby']))
				{
					$event->user->pilogin = $results[0]['authorizedby'][0];
				}

				$gid = $data[0]['gidnumber'][0];

				// Resolve group name
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
		catch (\Exception $e)
		{
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
}
