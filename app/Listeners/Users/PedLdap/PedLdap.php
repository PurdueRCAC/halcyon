<?php
namespace App\Listeners\Users\PedLdap;

use App\Modules\Users\Events\UserSearching;
use App\Modules\Users\Events\UserBeforeDisplay;
use App\Modules\Users\Models\User;
use App\Modules\History\Traits\Loggable;
use App\Halcyon\Utility\Str;

/**
 * User listener for Purdue Employee Directory Ldap
 */
class PedLdap
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

		return config('ldap.ped', []);
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
				->addProvider($config, 'ped')
				->connect('ped');
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

		try
		{
			$ldap = $this->connect($config);

			$search = $event->search;
			$status = 404;

			// We already found a match, so kip this lookup
			if (!in_array($search, $usernames))
			{
				$results = array();

				// Try finding by email address
				if (preg_match("/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}/", $search))
				{
					$results = $ldap->search()
						->where('mail', '=', $search)
						->select(['cn', 'uid', 'title', 'purdueEduCampus'])
						->get();

					if (empty($results) || count($results) == 0)
					{
						$search = strstr($search, '@', true);
					}
				}

				if (empty($results) || count($results) == 0)
				{
					// Look for a currently active username in I2A2 matching the request.
					$results = $ldap->search()
						->where('uid', 'contains', $search)
						//->orWhere('uid', '=', $search . '*')
						->select(['cn', 'uid', 'title', 'purdueEduCampus'])
						->get();
				}

				foreach ($results as $result)
				{
					if ($event->results->count() >= $event->results->total())
					{
						//break;
					}

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
			}

			// Look for all currently active users in I2A2 with a real name matching the request.
			$results = $ldap->search()
				->where('cn', 'contains', $search)
				//->orWhere('cn', 'ends_with', ' ' . $search)
				->select(['cn', 'uid', 'title', 'sn', 'givenname', 'mail', 'purdueEduCampus'])
				->get();

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
						//$user->puid = $result['puid'][0];
						$user->username = $result['uid'][0];
						if (!$user->email)
						{
							$user->getUserUsername()->email = $result['mail'][0];
						}

						//$user->id = $user->username;
					}

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
						'query' => $output/*[
							'page' => $event->results->currentPage()
						]*/
					]
				);

				$event->results = $itemsTransformedAndPaginated;
				$results = array();
			}
		}
		catch (\Exception $e)
		{
			$status = 500;
			$results = ['error' => $e->getMessage()];
		}

		$this->log('ldap', __METHOD__, 'GET', $status, $results, 'cn=' . $event->search);
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

		try
		{
			$ldap = $this->connect($config);

			$user = $event->getUser();
			$status = 404;

			$results = $ldap->search()
				->orWhere('uid', '=', $user->username)
				->select([
					'cn', 'title', 'mail', 'roomNumber', 'purdueEduCampus',
					'purdueEduDepartment', 'purdueEduBuilding', 'purdueEduSchool',
					'purdueEduOfficePhone', 'purdueEduOtherPhone'
				])
				->get();

			if (!empty($results))
			{
				$status = 200;

				foreach ($results as $data)
				{
					if (isset($data['cn']) && strtolower($user->name) != strtolower($data['cn'][0]))
					{
						// This is weird and messy. Due to all the extra data
						// on the `$user` object, an `update()` throws an error.
						$u = User::find($user->id);
						$u->update(['name' => Str::properCaseNoun($data['cn'][0])]);

						$user->name = Str::properCaseNoun($data['cn'][0]);
					}

					$f = $user->facets->where('key', 'title')->first();

					if ($f && $f->value && !$user->title)
					{
						$user->title = $f;
					}
					elseif (isset($data['title']))
					{
						$user->title = Str::properCaseNoun($data['title'][0]);
					}

					if (!$user->getUserUsername()->email && isset($data['mail']))
					{
						$user->getUserUsername()->email = $data['mail'][0];
						$user->getUserUsername()->save();
					}

					if (isset($data['roomnumber']))
					{
						$user->roomnumber = $data['roomnumber'][0];
					}

					if (isset($data['purdueeducampus']))
					{
						$user->campus = Str::properCaseNoun($data['purdueeducampus'][0]);
					}

					if (isset($data['purdueedudepartment']))
					{
						$user->department = Str::properCaseNoun($data['purdueedudepartment'][0]);
					}

					if (isset($data['purdueedubuilding']))
					{
						$user->building = strtoupper($data['purdueedubuilding'][0]);
					}

					if (isset($data['purdueeduschool']))
					{
						$user->school = Str::properCaseNoun($data['purdueeduschool'][0]);
					}

					if (isset($data['purdueeduofficephone']))
					{
						$user->phone = $data['purdueeduofficephone'][0];
						$user->phone = preg_replace('/^\+1 /', '', $user->phone);
					}
					elseif (isset($data['purdueeduotherphone']))
					{
						$user->phone = $data['purdueeduotherphone'][0];
						$user->phone = preg_replace('/^\+1 /', '', $user->phone);
					}
				}

				$event->setUser($user);
			}
		}
		catch (\Exception $e)
		{
			$status = 500;
			$results = ['error' => $e->getMessage()];
		}

		$this->log('ldap', __METHOD__, 'GET', $status, $results, 'uid=' . $event->getUser()->username);
	}
}
