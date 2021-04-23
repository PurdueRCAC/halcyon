<?php
namespace App\Listeners\Users\AuthprimaryLdap;

use App\Modules\Users\Events\UserCreated;
use App\Modules\Users\Models\User;
use App\Modules\Users\Models\UserUsername;
use App\Halcyon\Utility\Str;
use App\Modules\History\Traits\Loggable;

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
		$events->listen(UserSearching::class, self::class . '@handleUserSearching');
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

		return config('listener.amieldap', []);
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
				->addProvider($config, 'amie')
				->connect('amie');
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
