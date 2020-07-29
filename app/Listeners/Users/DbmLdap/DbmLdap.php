<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Listeners\Users\DbmLdap;

use App\Modules\Users\Events\UserCreated;
use App\Modules\Users\Events\UserSearching;
use App\Modules\Users\Models\User;
use App\Halcyon\Utility\Str;

/**
 * User listener for Purdue Ldap
 */
class DbmLdap
{
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
	 * Plugin that loads module positions within content
	 *
	 * @param   string   $context  The context of the content being passed to the plugin.
	 * @param   object   $article  The article object.  Note $article->text is also available
	 * @return  void
	 */
	public function handleUserSearching(UserSearching $event)
	{
		if (!app()->has('ldap'))
		{
			return;
		}

		/*try
		{
			$ldap = app('ldap')->connect('dbm');

			// Performing a query.
			$results = $ldap->search()
				->where(
					['cn', '=', $search],
					['cn', 'contains', $search]
				)
				->select(['cn', 'uid', 'title', 'purdueEduCampus'])
				->get();

			foreach ($results as $result)
			{
				$user = new User;
				$user->name = $result['cn'][0];
				$user->username = $result['uid'][0];
				$user->email = $user->username . '@purdue.edu';

				$event->results->add($user);
			}
		}
		catch (\Adldap\Auth\BindException $e)
		{
		}*/
	}

	/**
	 * Plugin that loads module positions within content
	 *
	 * @param   string   $context  The context of the content being passed to the plugin.
	 * @param   object   $article  The article object.  Note $article->text is also available
	 * @return  void
	 */
	public function handleUserLookup(UserLookup $event)
	{
		if (!app()->has('ldap'))
		{
			return;
		}

		try
		{
			$ldap = app('ldap')->connect('dbm');

			$criteria = $event->criteria;
			$query = [];

			foreach ($criteria as $key => $val)
			{
				switch ($key)
				{
					case 'organization_id':
						// `employeeNumber` needs to be 10 digits in length for the query to work
						//    ex: 12345678 -> 0012345678
						$val = str_pad($val, 10, '0', STR_PAD_LEFT);
						$query[] = ['employeeNumber', '=', $val];
					break;

					case 'username':
						$query[] = ['uid', '=', $val];
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

			foreach ($results as $result)
			{
				$user = new User;
				$user->name = $result['cn'][0];
				$user->username = $result['uid'][0];
				$user->email = $user->username . '@purdue.edu';
				$user->organization_id = $result['employeeNumber'][0];

				$event->user = $user;
				break;
			}
		}
		catch (\Adldap\Auth\BindException $e)
		{
		}
	}

	/**
	 * Plugin that loads module positions within content
	 *
	 * @param   string   $context  The context of the content being passed to the plugin.
	 * @param   object   $article  The article object.  Note $article->text is also available
	 * @return  void
	 */
	public function handleUserCreated(UserCreated $event)
	{
		if (!app()->has('ldap'))
		{
			return;
		}

		$config = config('ldap.dbm', []);

		if (empty($config))
		{
			return;
		}

		// We'll assume we already have all the user's info
		if ($event->user->organization_id)
		{
			return;
		}

		try
		{
			$ldap = app('ldap')
				->addProvider($config, 'dbm')
				->connect('dbm');

			// Look for user record in LDAP
			$result = $ldap->search()
				->where('cn', '=', $event->user->username)
				->select(['cn', 'mail', 'employeeNumber'])
				->first();

			if ($result)
			{
				// Set user data
				$event->user->name = Str::properCaseNoun($result['cn'][0]);
				$event->user->organization_id = $result['employeeNumber'][0];
				$event->user->email = $result['mail'][0];
				$event->user->save();
			}
		}
		catch (\Adldap\Auth\BindException $e)
		{
		}
	}
}
