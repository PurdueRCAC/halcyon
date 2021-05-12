<?php
namespace App\Listeners\Users\AmieLdap;

use App\Modules\Users\Events\UserSyncing;
use App\Modules\Users\Events\UserSync;
use App\Modules\Users\Models\User;
use App\Modules\Users\Models\Userusername;
use App\Halcyon\Utility\Str;
use App\Modules\History\Traits\Loggable;
use App\Modules\Groups\Models\Group;
use App\Modules\Resources\Models\Asset;
use App\Modules\Queues\Events\AllocationCreate;
use App\Modules\Queues\Models\Scheduler;
use App\Modules\Queues\Models\Queue;
use Carbon\Carbon;

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
		$events->listen(AllocationCreate::class, self::class . '@handleAllocationCreate');
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

		$user = $event->user; //new User;

		try
		{
			$ldap = $this->connect($config);
			$status = 404;

			// Look for user record in LDAP
			$results = $ldap->search()
				->where('uid', '=', $event->uid)
				->first();

			if ($results && $results->exists)
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
					'x-xsede-personId',
					/*'x-xsede-recordId',
					'x-xsede-pid',
					'x-xsede-resource',
					'x-xsede-startTime',
					'x-xsede-endTime',
					'x-xsede-serviceUnits',
					'x-xsede-gid',*/
				];

				if (!$user)
				{
					$user = User::findByUsername($event->uid);
				}

				// Create new user if doesn't exist
				if (!$user)
				{
					$user = new User;
					$user->name = $results->getAttribute('cn', 0);
					$user->save();

					$username = new Userusername;
					$username->userid = $user->id;
					$username->username = $results->getAttribute('uid', 0);
					$username->save();
				}

				// Add metadata
				foreach ($atts as $key)
				{
					$meta = $user->facets->firstWhere($key, $val);
					$val = $results->getAttribute($key, 0);

					if (!$meta && $val)
					{
						$user->addFacet($key, $val, 0, 1);
					}
				}

				if ($vals = $results->getAttribute('x-xsede-userDn'))
				{
					foreach ($vals as $val)
					{
						$meta = $user->facets->search($val);

						if (!$meta)
						{
							$user->addFacet($key, $val, 0, 1);
						}
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

	/**
	 * Handle a AllocationCreate event
	 * 
	 * This will look up information in the Amie  LDAP
	 * for the specific user.
	 *
	 * @param   AllocationCreate  $event
	 * @return  void
	 */
	public function handleAllocationCreate(AllocationCreate $event)
	{
		$config = $this->config();

		if (empty($config))
		{
			return;
		}

		$data = $event->data;

		if (!empty($data['dn']))
		{
			return;
		}

		$queue = null;

		try
		{
			$ldap = $this->connect($config);
			$status = 404;

			// Look for user record in LDAP
			$results = $ldap->search()
				->where('dn', '=', $data['dn'])
				->first();

			if ($results && $results->exists)
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
					'x-xsede-personId',
					/*'x-xsede-recordId',
					'x-xsede-pid',
					'x-xsede-resource',
					'x-xsede-startTime',
					'x-xsede-endTime',
					'x-xsede-serviceUnits',
					'x-xsede-gid',*/
				];

				$user = User::findByUsername($results->getAttribute('uid', 0));

				// Create new user if doesn't exist
				if (!$user)
				{
					$user = new User;
					$user->name = $results->getAttribute('cn', 0);
					$user->save();

					$username = new Userusername;
					$username->userid = $user->id;
					$username->username = $results->getAttribute('uid', 0);
					$username->save();
				}

				// Add metadata
				foreach ($atts as $key)
				{
					$meta = $user->facets->firstWhere($key, $val);
					$val = $results->getAttribute($key, 0);

					if (!$meta && $val)
					{
						$user->addFacet($key, $val, 0, 1);
					}
				}

				if ($vals = $results->getAttribute('x-xsede-userDn'))
				{
					foreach ($vals as $val)
					{
						$meta = $user->facets->search($val);

						if (!$meta)
						{
							$user->addFacet($key, $val, 0, 1);
						}
					}
				}

				if ($pid = $results->getAttribute('x-xsede-pid', 0))
				{
					$group = Group::findByName($pid);

					if (!$group)
					{
						$group = new Group;
						$group->name = $pid;
						$group->owneruserid = $user->id;
						$group->save();

						$group->addManager($user->id, 1);
					}

					$queue = $group->queues->search($pid);

					if (!$queue)
					{
						$dn = explode(',', $data['dn']);
						if (isset($dn[2]))
						{
							$rolename = str_replace('dc=', '', $dn[2]);
						}

						$resource = Asset::findByName($rolename);

						$subresource = $resource ? null : $resource->subresources->first();

						$scheduler = Scheduler::query()
							->where('hostname', '=', $rolename . '-adm.rcac.purdue.edu')
							->get();

						if ($subresource && $scheduler)
						{
							$queue = new Queue;
							$queue->name = $pid;
							$queue->groupid = $group->id;
							$queue->queuetype = 1;
							$queue->enabled = 1;
							$queue->started = 1;
							$queue->defaultwalltime = 30 * 60;
							$queue->maxwalltime = $scheduler->defaultmaxwalltime;
							$queue->subresourceid = $subresource->id;
							$queue->schedulerid = $scheduler->schedulerid;
							$queue->schedulerpolicyid = $scheduler->schedulerpolicyid;
							$queue->maxjobsqueued = 12000;
							$queue->maxjobsqueuesuser = 5000;
							$queue->cluster = $subresource->cluster;
							$queue->save();
						}
					}

					$sizes = $queue->sizes()->orderBy('id', 'asc')->get();
					$serviceUnits = $results->getAttribute('x-xsede-serviceUnits', 0);

					$start = $results->getAttribute('x-xsede-startTime', 0);
					$start = $start ? Carbon::parse($start) : null;
					$now = Carbon::now();

					if (!count($sizes) && $serviceUnits && $start && $start >= $now)
					{
						$start = $results->getAttribute('x-xsede-startTime', 0);
						$start = $start ?: null;

						$stop = $results->getAttribute('x-xsede-endTime', 0);
						$stop = $stop ?: null;

						$nodecount = (int)$serviceUnits;
						$corecount = $subresource->nodecores * $nodecount;

						$queue->addLoan($seller->id, $start, $stop, $nodecount, $corecount);
					}
				}

				event(new UserSync($user));
			}
		}
		catch (\Exception $e)
		{
			$status = 500;
			$results = ['error' => $e->getMessage()];
		}

		$event->response = $queue;

		$this->log('ldap', __METHOD__, 'GET', $status, $results, 'uid=' . $event->uid);
	}
}
