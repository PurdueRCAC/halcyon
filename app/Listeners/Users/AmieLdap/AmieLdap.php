<?php
namespace App\Listeners\Users\AmieLdap;

use Illuminate\Support\Fluent;
use App\Modules\Users\Events\UserSyncing;
use App\Modules\Users\Events\UserSync;
use App\Modules\Users\Models\User;
use App\Modules\Users\Models\UserUsername;
use App\Halcyon\Utility\Str;
use App\Modules\History\Traits\Loggable;
use App\Modules\Groups\Models\Group;
use App\Modules\Groups\Models\UnixGroup;
use App\Modules\Groups\Models\UnixGroupMember;
use App\Modules\Resources\Models\Asset;
use App\Modules\Queues\Events\AllocationCreate;
use App\Modules\Queues\Models\Scheduler;
use App\Modules\Queues\Models\Queue;
use App\Modules\Queues\Models\User as QueueUser;
use App\Modules\Storage\Models\StorageResource;
use App\Modules\Storage\Models\Directory;
use App\Modules\Storage\Models\Loan;
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
	 * @param   string  $ou
	 * @param   string  $dc
	 * @return  array
	 */
	private function config($ou = null, $dc = null)
	{
		if (!app()->has('ldap'))
		{
			return array();
		}

		$config = config('listener.amieldap', []);

		if ($dc && isset($config['base_dn']))
		{
			$basedn = stristr($config['base_dn'], ',');

			$config['base_dn'] = 'dc=' . $dc . $basedn;
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
		$config = $this->config('Projects');

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

		$this->log('amieldap', __METHOD__, 'GET', $status, $results, 'uid=' . $event->uid);
	}

	/**
	 * Handle a AllocationCreate event
	 * 
	 * This will look up information in the Amie LDAP
	 * for the specific user.
	 *
	 * @param   AllocationCreate  $event
	 * @return  void
	 */
	public function handleAllocationCreate(AllocationCreate $event)
	{
		$config = $this->config(); //'Projects');

		if (empty($config))
		{
			return;
		}

		$data = $event->data;

		// Try to determine the primary resource being used
		//    Ex: dc=foo,dc=rcac,dc=purdue,dc=edu
		$rolename = stristr($config['base_dn'], ',', true);
		$rolename = str_replace('dc=', '', $rolename);
		$cluster = 'cpu';

		$resource = $rolename ? Asset::findByName($rolename) : null;

		if (!$resource)
		{
			// No resource found? Can't really do anything without it.
			return;
		}

		if (isset($data['dn']))
		{
			// dn: x-xsede-pid=PEB215459,ou=Projects,dc=anvil,dc=rcac,dc=purdue,dc=edu
			$dn = explode(',', $data['dn']);

			if (isset($dn[0]))
			{
				$data['x-xsede-pid'] = str_replace('x-xsede-pid=', '', $dn[0]);
			}

			if (isset($dn[2]))
			{
				$rolename = str_replace('dc=', '', $dn[2]);

				// Is this a different LDAP tree?
				// If so, we need to change the base dn in the config
				if ($rolename && $rolename != $resource->rolename)
				{
					//$basedn = stristr($config['base_dn'], ',', true);

					// Try to figure out the cluster name
					// This will be used for the proper subresource lookup later
					if (substr($rolename, 0, strlen($resource->rolename)) == $resource->rolename)
					{
						$cluster = substr($rolename, strlen($resource->rolename));
					}

					//$config['base_dn'] = str_replace($basedn, 'dc=' . $rolename, $config['base_dn']);
				}
			}
		}
		//$config['base_dn'] = 'ou=Projects,' . $config['base_dn'];
		$config = $this->config('Projects', $rolename);

		if (empty($data['x-xsede-pid']))
		{
			return;
		}

		$response = new Fluent;

		try
		{
			$ldap = $this->connect($config);
			$status = 404;

			// Look for user record in LDAP
			$results = $ldap->search()
				->where('x-xsede-pid', '=', $data['x-xsede-pid'])
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
				if (!$user || !$user->id)
				{
					$user = new User;
					$user->name = $results->getAttribute('cn', 0);
					$user->save();

					$username = new UserUsername;
					$username->userid = $user->id;
					$username->username = $results->getAttribute('uid', 0);
					$username->save();
				}

				// Add metadata
				foreach ($atts as $key)
				{
					$val = $results->getAttribute($key, 0);
					$meta = $user->facets->firstWhere('key', '=', $key);

					if (!$meta && $val)
					{
						$user->addFacet($key, $val, 0, 1);
					}
				}

				if ($uidNumber = $results->getAttribute('uidNumber', 0))
				{
					$user->uidNumber = $uidNumber;
				}
				if ($gidNumber = $results->getAttribute('gidNumber', 0))
				{
					$user->gidNumber = $gidNumber;
				}

				if ($vals = $results->getAttribute('x-xsede-userDn'))
				{
					foreach ($vals as $val)
					{
						$meta = $user->facets->firstWhere('value', '=', $val);

						if (!$meta)
						{
							$user->addFacet('x-xsede-userDn', $val, 0, 1);
						}
					}
				}

				// Check for a gid
				// If not found, fallback to a pid
				$pid = $results->getAttribute('x-xsede-gid', 0);
				if (!$pid && $results->getAttribute('x-xsede-pid', 0))
				{
					$pid = 'x-' . strtolower($results->getAttribute('x-xsede-pid', 0));
				}

				if ($pid)
				{
					//
					// Check to see if the project is active
					//

					// Any service units?
					$serviceUnits = $results->getAttribute('x-xsede-serviceUnits', 0);

					$authorized = true;
					if (!$serviceUnits)
					{
						$authorized = false;
					}

					$start = $results->getAttribute('x-xsede-startTime', 0);
					$start = $start ? Carbon::parse($start) : null;

					$stop  = $results->getAttribute('x-xsede-endTime', 0);
					$stop  = $stop ? Carbon::parse($stop) : null;

					$now = Carbon::now();

					// Is this a future allocation?
					if ($start && $start->timestamp > $now->timestamp)
					{
						$authorized = false;
					}

					// Did the allocation expire?
					if ($stop && $stop->timestamp < $now->timestamp)
					{
						$authorized = false;
					}

					//
					// Check for an associated group (a.k.a. project)
					//
					$group = Group::findByName($pid);

					if (!$group || !$group->id)
					{
						$group = new Group;
						$group->name = $pid;
						$group->owneruserid = $user->id;
						$group->unixgroup = $group->name;
						$group->save();

						$group->addManager($user->id, 1);
					}

					if (!$group->unixgroup)
					{
						$group->unixgroup = $group->name;
						$group->save();
					}

					$allmembers = array();

					//
					// Unix groups
					//
					/*
					# x-peb216887, Groups, anvil.rcac.purdue.edu
					dn: cn=x-peb216887,ou=Groups,dc=anvil,dc=rcac,dc=purdue,dc=edu
					memberUid: x-yinzhang
					cn: x-peb216887
					gidNumber: 7000167
					objectClass: posixGroup
					objectClass: top
					*/
					$gldap = $this->connect($this->config('Groups', $rolename));
					$ugs = $gldap->search()
							->where('cn', '=', $group->unixgroup)
							->first();

					$unixgroup = $group->unixgroups->first();

					if ($ugs && $ugs->exists)
					{
						// Create unix group if doesn't exist
						if (!$unixgroup || !$unixgroup->id)
						{
							$unixgroup = new UnixGroup;
							$unixgroup->groupid = $group->id;
							$unixgroup->longname = $ugs->getAttribute('cn', 0);
							$unixgroup->shortname = $unixgroup->generateShortname($unixgroup->longname);
							$unixgroup->unixgid = $ugs->getAttribute('gidNumber', 0);
							$unixgroup->save();
						}

						// Sync membership
						if ($vals = $ugs->getAttribute('memberUid'))
						{
							$ugusers = $unixgroup->members;

							$current = $ugusers->pluck('userid')->toArray();
							//$added = array();

							$pldap = $this->connect($this->config('People', $rolename));

							foreach ($vals as $val)
							{
								$member = User::findByUsername($val);

								$mem = $pldap->search()
									->where('uid', '=', $val)
									->first();

								if (!$mem && !$mem->exists)
								{
									continue;
								}

								if (!$member || !$member->id)
								{
									$member = new User;
									$member->name = $mem->getAttribute('cn', 0);
									$member->save();

									$musername = new UserUsername;
									$musername->userid = $member->id;
									$musername->username = $val;
									$musername->save();
								}
								$member->username = $val;
								if ($uidNumber = $mem->getAttribute('uidNumber', 0))
								{
									$member->uidNumber = $uidNumber;
								}
								if ($gidNumber = $mem->getAttribute('gidNumber', 0))
								{
									$member->gidNumber = $gidNumber;
								}
								if ($dns = $mem->getAttribute('x-xsede-userDn'))
								{
									foreach ($dns as $dn)
									{
										$meta = $member->facets->firstWhere('value', '=', $dn);

										if (!$meta)
										{
											$member->addFacet('x-xsede-userDn', $dn, 0, 1);
										}
									}
								}

								event(new UserSync($member, $authorized, $rolename));

								// Create user if needed
								if (!in_array($member->id, $current))
								{
									$ugu = new UnixGroupMember;
									$ugu->unixgroupid = $unixgroup->id;
									$ugu->userid = $member->id;
									$ugu->save();
								}

								//$added[] = $member->id;
								$allmembers[] = $member->id;
							}

							// Remove any users not found in the list from LDAP
							$remove = array_diff($current, $allmembers);

							foreach ($remove as $userid)
							{
								foreach ($ugusers as $uguser)
								{
									if ($uguser->userid == $userid)
									{
										$uguser->delete();
										continue;
									}
								}
							}
						}
					}

					// Queues
					$queue = $group->queues()
						->where('name', '=', $pid . ($cluster != 'cpu' ? '-' . $cluster : ''))
						->first();

					/*$dn = explode(',', $results->getAttribute('distinguishedname', 0));
					if (isset($dn[2]))
					{
						$rolename = str_replace('dc=', '', $dn[2]);
					}*/

					// Try to find an appropriate sub resource
					$subresource = null;

					// Is a specific cluster specified?
					if ($cluster)
					{
						$subresource = $resource->subresources()
							->where('cluster', '=', $cluster)
							->first();
					}
					else
					{
						$subresource = $resource->subresources->first();
					}

					//$subresource = $resource ? $resource->subresources->first() : null;

					if (!$queue || !$queue->id)
					{
						$scheduler = Scheduler::query()
							->where(function($where) use ($resource)
							{
								$where->where('hostname', '=', $resource->rolename . '-adm.rcac.purdue.edu')
									->orWhere('hostname', '=', $resource->rolename . '.adm.rcac.purdue.edu')
									->orWhere('hostname', '=', 'adm.' . $resource->rolename . '.rcac.purdue.edu');
							})
							->get()
							->first();

						$queue = new Queue;
						$queue->name = $pid . ($cluster != 'cpu' ? '-' . $cluster : '');
						$queue->groupid = $group->id;
						$queue->queuetype = 1;
						$queue->enabled = 1;
						$queue->started = 1;
						$queue->cluster = $cluster;

						if ($subresource && $scheduler)
						{
							$queue->defaultwalltime = 30 * 60;
							//$queue->maxwalltime = $scheduler->defaultmaxwalltime;
							$queue->subresourceid = $subresource->id;
							$queue->schedulerid = $scheduler->id;
							$queue->schedulerpolicyid = $scheduler->schedulerpolicyid;
							$queue->maxjobsqueued = 12000;
							$queue->maxjobsqueueduser = 5000;
							$queue->cluster = $subresource->cluster;
						}

						$queue->save();
					}

					// Sync queue membership
					$queueusers = $queue->users()
						->get();

					$queueuserids = $queueusers
						->pluck('userid')
						->toArray();

					foreach ($allmembers as $userid)
					{
						if (in_array($userid, $queueuserids))
						{
							continue;
						}
						$qm = new QueueUser;
						$qm->queueid = $queue->id;
						$qm->userid = $userid;
						$qm->membertype = 1;
						$qm->save();
					}

					$remove = array_diff($queueuserids, $allmembers);

					foreach ($remove as $userid)
					{
						foreach ($queueusers as $quser)
						{
							if ($quser->userid == $userid)
							{
								$quser->delete();
								continue;
							}
						}
					}

					$loans = $queue->loans()->orderBy('id', 'asc')->get();
					/*$serviceUnits = $results->getAttribute('x-xsede-serviceUnits', 0);

					$start = $results->getAttribute('x-xsede-startTime', 0);
					$start = $start ? Carbon::parse($start) : null;

					$stop  = $results->getAttribute('x-xsede-endTime', 0);
					$stop  = $stop ? Carbon::parse($stop) : null;

					$now = Carbon::now();*/

					if (!count($loans) && $serviceUnits && $subresource)// && $start && $start >= $now)
					{
						$lenderqueue = $subresource->queues()
							->where('groupid', '=', '-1')
							->where('cluster', '=', $cluster)
							->orderBy('id', 'asc')
							->first();

						if ($lenderqueue)
						{
							$nodecount = 0; //(int)$serviceUnits;
							$corecount = 0; //$subresource->nodecores * $nodecount;

							$queue->addLoan($lenderqueue->id, $start, $stop, $nodecount, $corecount, $serviceUnits, 'x-xsede-pid: ' . $pid);
						}
					}

					// Storage
					$storage = StorageResource::query()
						->where('path', '=', '/depot')
						->get()
						->first();

					if ($storage)
					{
						$buckets = $group->storageBuckets;
						$space = '100 GB';

						// Has any space been allocatted?
						if (!isset($buckets[$storage->parentresourceid]))
						{
							$loan = new Loan;
							$loan->resourceid = $storage->parentresourceid;
							$loan->groupid = $group->id;
							$loan->lendergroupid = -1;
							$loan->datetimestart = $start ?: $now;
							if ($stop)
							{
								$loan->datetimestop = $stop;
							}
							$loan->bytes = $space;
							$loan->comment = 'XSEDE project ' . $pid;
							$loan->save();

							// Enforce proper accounting
							if ($loan->lendergroupid)
							{
								// Convert to string to add negative or PHP will lose precision on large values
								//$group = $row->groupid;
								$data = $loan->toArray();
								unset($data['id']);
								if (isset($data['group']))
								{
									unset($data['group']);
								}

								$counter = new Loan;
								$counter->fill($data);
								if ($counter->bytes < 0)
								{
									$counter->bytes = abs($counter->bytes);
								}
								else
								{
									$counter->bytes = '-' . $counter->bytes;
								}
								$counter->groupid = $loan->lendergroupid;
								$counter->lendergroupid = $loan->groupid;
								$counter->save();
							}
						}

						// Do we have a directory?
						$dir = $group->directories()
							->where('storageresourceid', '=', $storage->id)
							->get()
							->first();

						if (!$dir || !$dir->id)
						{
							$dir = new Directory;
							$dir->ownerread   = 1;
							$dir->ownerwrite  = 1;
							$dir->groupread   = 1;
							$dir->groupwrite  = 1;
							$dir->publicread  = 0;
							$dir->publicwrite = 0;
							$dir->groupid = $group->id;
							$dir->unixgroupid = $unixgroup->id;
							$dir->autouserunixgroupid = $unixgroup->id;
							$dir->storageresourceid = $storage->id;
							$dir->resourceid = $storage->parentresourceid;
							$dir->name = $group->name;
							$dir->path = $dir->name;
							$dir->bytes = $space;
							$dir->save();
						}
					}

					// Output
					$q = $queue->toArray();
					$q['members'] = $queue->users()
						->get()
						->toArray();

					$q['loans'] = $queue->loans()
						->get()
						->toArray();

					foreach ($q['members'] as $k => $member)
					{
						$member['api'] = route('api.queues.users.read', ['id' => $member['id']]);
						$q['members'][$k] = $member;
					}
					$q['api'] = route('api.queues.read', ['id' => $q['id']]);
					$response->queue = $q;

					$g = $group->toArray();
					$g['members'] = $group->members()->get()->toArray();
					foreach ($g['members'] as $k => $member)
					{
						$member['api'] = route('api.groups.members.read', ['id' => $member['id']]);
						$g['members'][$k] = $member;
					}
					$g['api'] = route('api.groups.read', ['id' => $g['id']]);
					$response->group = $g;

					$u = $unixgroup->toArray();
					$u['members'] = $unixgroup->members()->get()->toArray();
					foreach ($u['members'] as $k => $member)
					{
						$member['api'] = route('api.unixgroups.members.read', ['id' => $member['id']]);
						$u['members'][$k] = $member;
					}

					$u['api'] = route('api.unixgroups.read', ['id' => $u['id']]);
					$response->unixgroup = $u;

					$d = $dir->toArray();
					$d['api'] = route('api.storage.directories.read', ['id' => $d['id']]);
					$response->directory = $d;
				}

				//event(new UserSync($user, true, $rolename));
			}
		}
		catch (\Exception $e)
		{
			$status = 500;
			$results = ['error' => $e->getMessage()];
		}

		$event->response = $response;

		$this->log('amieldap', __METHOD__, 'POST', $status, $results, json_encode($event->data));
	}
}
