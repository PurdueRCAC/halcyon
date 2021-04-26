<?php
namespace App\Listeners\Users\AmieLdap;

use App\Modules\Users\Events\UserSyncing;
use App\Modules\Users\Models\User;
use App\Halcyon\Utility\Str;
use App\Modules\History\Traits\Loggable;

/**
 * User listener for Amie Ldap
 */
class AmieLdap
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
		$events->listen(UserSyncing::class, self::class . '@handleUserSyncing');
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
	 * Handle a User syncing event
	 * 
	 * This will look up information in the Amie  LDAP
	 * for the specific user.
	 *
	 * @param   UserSyncing  $event
	 * @return  void
	 */
	public function handleUserSyncing(UserSyncing $event)
	{
		$config = $this->config();

		if (empty($config))
		{
			return;
		}

		$user = new User;

		try
		{
			$ldap = $this->connect($config);
			$status = 404;

			// Look for user record in LDAP
			$result = $ldap->search()
				->where('uid', '=', $event->uid)
				->first();

			if ($result && $result->exists)
			{
				$status = 200;

				/*
				Sample LDAP entry

				# PEB215459, Projects, anvil.rcac.purdue.edu
				dn: x-xsede-pid=PEB215459,ou=Projects,dc=anvil,dc=rcac,dc=purdue,dc=edu
				objectClass: x-xsede-xsedeProject
				objectClass: x-xsede-xsedePerson
				objectClass: posixAccount
				objectClass: inetOrgPerson
				objectClass: top
				x-xsede-recordId: 87665808
				x-xsede-pid: PEB215459
				uid: x-tannazr
				x-xsede-resource: test-resource1.purdue.xsede
				x-xsede-startTime: 20210415000000Z
				x-xsede-endTime: 20220415000000Z
				x-xsede-serviceUnits: 1
				description: Lorem ipsum dolor est...
				title: Lorem Ipsum
				x-xsede-personId: x-tannazr
				givenName:: VEFOTkFaIA==
				sn: REZAEI DAMAVANDI
				cn: TANNAZ  REZAEI DAMAVANDI
				o: California State Polytechnic University, Pomona
				departmentNumber: COMPUTER SCIENCE
				mail: tannazr@cpp.edu
				telephoneNumber: 9499297548
				street: 2140 WATERMARKE PLACE
				l: IRVINE
				st: California
				postalCode: 92612
				co: United States
				x-xsede-userDn: /C=US/O=Pittsburgh Supercomputing Center/CN=TANNAZ REZAEI DAMA
				VANDI
				x-xsede-userDn: /C=US/O=National Center for Supercomputing Applications/CN=TAN
				NAZ REZAEI DAMAVANDI
				x-xsede-gid: x-peb215459
				gidNumber: 7000060
				uidNumber: 7000006
				homeDirectory: /home/x-tannazr
				*/

				// Set user data
				$atts = [
					'uid',
					'uidNumber',
					'gidNumber',
					'homeDirectory',
					'sn', // Surname
					'givenName',
					'cn',
					'mail',
					'o', // Organization
					'departmentNumber', // A string, not a number. Wut?
					'telephoneNumber',
					'co', // Country
					'x-xsede-personId'
				];

				foreach ($atts as $key)
				{
					if ($val = $result->getAttribute($key, 0))
					{
						$user->{$key} = $val;
					}
				}
			}
		}
		catch (\Exception $e)
		{
			$status = 500;
			$results = ['error' => $e->getMessage()];
		}

		$event->user = $user;

		$this->log('ldap', __METHOD__, 'GET', $status, $results, 'uid=' . $event->uid);
	}
}
