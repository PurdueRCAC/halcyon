<?php
namespace App\Listeners\Users\DbmLdap;

use App\Modules\Users\Events\UserCreated;
use App\Modules\Users\Events\UserSearching;
use App\Modules\Users\Models\User;
use App\Halcyon\Utility\Str;
use App\Modules\History\Traits\Loggable;

/**
 * User listener for Purdue Ldap
 */
class DbmLdap
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
		$events->listen(UserCreated::class, self::class . '@handleUserCreated');
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

		return config('ldap.dbm', []);
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
				->addProvider($config, 'dbm')
				->connect('dbm');
	}

	/**
	 * Handle a user seach event
	 * 
	 * Look for users in the Purdue LDAP based on the specified
	 * criteria and return a list of User objects.
	 *
	 * @param   UserSearching   $event
	 * @return  void
	 */
	public function handleUserSearching(UserSearching $event)
	{
		$config = $this->config();

		if (empty($config))
		{
			return;
		}

		/*try
		{
			$ldap = $this->connect($config);

			// Performing a query.
			$results = $ldap->search()
				->where(
					['cn', '=', $search],
					['cn', 'contains', $search]
				)
				->select(['cn', 'uid', 'title', 'purdueEduCampus', 'employeeNumber'])
				->get();

			if (!empty($results))
			{
				$status = 200;

				foreach ($results as $result)
				{
					$user = new User;
					$user->name = $result['cn'][0];
					$user->username = $result['uid'][0];
					$user->puid = $result['employeeNumber'][0];
					$user->email = $user->username . '@purdue.edu';

					$event->results->add($user);
				}
			}
		}
		catch (\Exception $e)
		{
			$status = 500;
			$results = ['error' => $e->getMessage()];
		}

		$this->log('ldap', __METHOD__, 'GET', $status, $results, implode('', $query));*/
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

		try
		{
			$ldap = $this->connect($config);

			$criteria = $event->criteria;
			$query = [];
			$status = 404;

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

			// Performing a query.
			$results = $ldap->search()
				->where($query)
				->select(['cn', 'uid', 'employeeNumber'])
				->get();

			if (!empty($results))
			{
				$status = 200;

				foreach ($results as $result)
				{
					$user = new User;
					$user->name = $result['cn'][0];
					$user->username = $result['uid'][0];
					$user->puid = $result['employeeNumber'][0];

					$event->user = $user;
					break;
				}
			}
		}
		catch (\Exception $e)
		{
			$status = 500;
			$results = ['error' => $e->getMessage()];
		}

		$event->results = $results;

		$this->log('ldap', __METHOD__, 'GET', $status, $results, implode('', $query));
	}

	/**
	 * Handle a User creation event
	 * 
	 * This will look up information in the Purdue LDAP
	 * for the specific user and add it to the local
	 * account.
	 *
	 * @param   UserCreated  $event
	 * @return  void
	 */
	public function handleUserCreated(UserCreated $event)
	{
		$config = $this->config();

		if (empty($config))
		{
			return;
		}

		// We'll assume we already have all the user's info
		if ($event->user->puid)
		{
			return;
		}

		try
		{
			$ldap = $this->connect($config);
			$status = 404;

			// Look for user record in LDAP
			$result = $ldap->search()
				->where('cn', '=', $event->user->username)
				->select(['cn', 'mail', 'employeeNumber'])
				->first();

			if (!empty($results))
			{
				$status = 200;

				// Set user data
				$event->user->name = Str::properCaseNoun($result['cn'][0]);
				$event->user->puid = $result['employeeNumber'][0];
				//$event->user->email = $result['mail'][0];
				$event->user->save();
			}
		}
		catch (\Exception $e)
		{
			$status = 500;
			$results = ['error' => $e->getMessage()];
		}

		$this->log('ldap', __METHOD__, 'GET', $status, $results, 'cn=' . $event->user->username);
	}
}
