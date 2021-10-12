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
	 * @return  array
	 */
	private function config($ou = null)
	{
		if (!app()->has('ldap'))
		{
			return array();
		}

		$config = config('listener.amieldap', []);

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
	 * This will look up information in the Amie  LDAP
	 * for the specific user.
	 *
	 * @param   AllocationCreate  $event
	 * @return  void
	 */
	public function handleAllocationCreate(AllocationCreate $event)
	{
		$config = $this->config('Projects');

		if (empty($config))
		{
			return;
		}

		$data = $event->data;

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

					// Unix groups
					/*
					# x-peb216887, Groups, anvil.rcac.purdue.edu
					dn: cn=x-peb216887,ou=Groups,dc=anvil,dc=rcac,dc=purdue,dc=edu
					memberUid: x-yinzhang
					cn: x-peb216887
					gidNumber: 7000167
					objectClass: posixGroup
					objectClass: top
					*/
					$gldap = $this->connect($this->config('Groups'));
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
							$added = array();

							foreach ($vals as $val)
							{
								$member = User::findByUsername($val);

								if (!$member || !$member->id)
								{
									continue;
								}

								// Create user if needed
								if (!in_array($member->id, $current))
								{
									$ugu = new UnixGroupMember;
									$ugu->unixgroupid = $unixgroup->id;
									$ugu->userid = $member->id;
									$ugu->save();
								}

								$added[] = $member->id;
							}

							// Remove any users not found in the list from LDAP
							$remove = array_diff($current, $added);

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
						->withTrashed()
						->whereIsActive()
						->where('name', '=', $pid)
						->first();

					$dn = explode(',', $results->getAttribute('distinguishedname', 0));
					if (isset($dn[2]))
					{
						$rolename = str_replace('dc=', '', $dn[2]);
					}

					$resource = Asset::findByName($rolename);

					$subresource = $resource ? $resource->subresources->first() : null;

					if (!$queue || !$queue->id)
					{
						$scheduler = Scheduler::query()
							->where('hostname', '=', $rolename . '-adm.rcac.purdue.edu')
							->get()
							->first();

						$queue = new Queue;
						$queue->name = $pid;
						$queue->groupid = $group->id;
						$queue->queuetype = 1;
						$queue->enabled = 1;
						$queue->started = 1;

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

					$queuemembers = $queue->users()
						->withTrashed()
						->whereIsActive()
						->count();

					if (!$queuemembers)
					{
						$qm = new QueueUser;
						$qm->queueid = $queue->id;
						$qm->userid = $user->id;
						$qm->membertype = 1;
						$qm->save();
					}

					$sizes = $queue->sizes()->orderBy('id', 'asc')->get();
					$serviceUnits = $results->getAttribute('x-xsede-serviceUnits', 0);

					$start = $results->getAttribute('x-xsede-startTime', 0);
					$start = $start ? Carbon::parse($start) : null;

					$stop  = $results->getAttribute('x-xsede-endTime', 0);
					$stop  = $stop ? Carbon::parse($stop) : null;

					$now = Carbon::now();

					if (!count($sizes) && $serviceUnits && $subresource)// && $start && $start >= $now)
					{
						//$start = $results->getAttribute('x-xsede-startTime', 0);
						//$start = $start ?: null;

						//$stop = $results->getAttribute('x-xsede-endTime', 0);
						//$stop = $stop ?: null;

						$lenderqueue = $subresource->queues()
							->withTrashed()
							->whereIsActive()
							->where('groupid', '=', '-1')
							->where('cluster', '=', '')
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
						->withTrashed()
						->whereIsActive()
						->get()
						->toArray();
					if (isset($q['datetimeremoved'])
					 && ($q['datetimeremoved'] == '0000-00-00 00:00:00' || $q['datetimeremoved'] == '-000001-11-30T06:00:00.000000Z'))
					{
						$q['datetimeremoved'] = null;
					}
					if (isset($q['datetimelastseen'])
					 && ($q['datetimelastseen'] == '0000-00-00 00:00:00' || $q['datetimelastseen'] == '-000001-11-30T06:00:00.000000Z'))
					{
						$q['datetimelastseen'] = null;
					}
					foreach ($q['members'] as $k => $member)
					{
						if (isset($member['datetimeremoved'])
						 && ($member['datetimeremoved'] == '0000-00-00 00:00:00' || $member['datetimeremoved'] == '-000001-11-30T06:00:00.000000Z'))
						{
							$member['datetimeremoved'] = null;
						}
						if (isset($member['datetimelastseen'])
						 && ($member['datetimelastseen'] == '0000-00-00 00:00:00' || $member['datetimelastseen'] == '-000001-11-30T06:00:00.000000Z'))
						{
							$member['datetimelastseen'] = null;
						}
						$member['api'] = route('api.queues.users.read', ['id' => $member['id']]);
						$q['members'][$k] = $member;
					}
					$q['api'] = route('api.queues.read', ['id' => $q['id']]);
					$response->queue = $q;

					$g = $group->toArray();
					$g['members'] = $group->members->toArray();
					foreach ($g['members'] as $k => $member)
					{
						if (isset($member['dateremoved'])
						 && ($member['dateremoved'] == '0000-00-00 00:00:00' || $member['dateremoved'] == '-000001-11-30T06:00:00.000000Z'))
						{
							$member['dateremoved'] = null;
						}
						if (isset($member['datelastseen'])
						 && ($member['datelastseen'] == '0000-00-00 00:00:00' || $member['datelastseen'] == '-000001-11-30T06:00:00.000000Z'))
						{
							$member['datelastseen'] = null;
						}
						$member['api'] = route('api.groups.members.read', ['id' => $member['id']]);
						$g['members'][$k] = $member;
					}
					$g['api'] = route('api.groups.read', ['id' => $g['id']]);
					$response->group = $g;

					$u = $unixgroup->toArray();
					$u['members'] = $unixgroup->members->toArray();
					foreach ($u['members'] as $k => $member)
					{
						if (isset($member['datetimeremoved'])
						 && ($member['datetimeremoved'] == '0000-00-00 00:00:00' || $member['datetimeremoved'] == '-000001-11-30T06:00:00.000000Z'))
						{
							$member['datetimeremoved'] = null;
						}
						$member['api'] = route('api.unixgroups.members.read', ['id' => $member['id']]);
						$u['members'][$k] = $member;
					}
					if (isset($u['datetimeremoved'])
					 && ($u['datetimeremoved'] == '0000-00-00 00:00:00' || $u['datetimeremoved'] == '-000001-11-30T06:00:00.000000Z'))
					{
						$u['datetimeremoved'] = null;
					}
					$u['api'] = route('api.unixgroups.read', ['id' => $u['id']]);
					$response->unixgroup = $u;

					$d = $dir->toArray();
					if (isset($d['datetimeremoved'])
					 && ($d['datetimeremoved'] == '0000-00-00 00:00:00' || $d['datetimeremoved'] == '-000001-11-30T06:00:00.000000Z'))
					{
						$d['datetimeremoved'] = null;
					}
					if (isset($d['datetimeconfigured'])
					 && ($d['datetimeconfigured'] == '0000-00-00 00:00:00' || $d['datetimeconfigured'] == '-000001-11-30T06:00:00.000000Z'))
					{
						$d['datetimeconfigured'] = null;
					}
					$d['api'] = route('api.storage.directories.read', ['id' => $d['id']]);
					$response->directory = $d;
				}

				event(new UserSync($user, true));
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
