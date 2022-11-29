<?php

namespace App\Modules\Queues\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Queues\Http\Resources\AllocationResourceCollection;
use App\Modules\Queues\Models\Queue;
use App\Modules\Queues\Models\Scheduler;
use App\Modules\Queues\Models\SchedulerPolicy;
use App\Modules\Queues\Events\AllocationCreate;
use App\Modules\Queues\Events\AllocationUpdate;
use App\Modules\Queues\Events\AllocationDelete;
use App\Modules\Resources\Models\Asset;
use App\Modules\Resources\Models\Child;
use App\Modules\Resources\Models\Subresource;
use App\Modules\Users\Models\UserUsername;
use App\Halcyon\Access\Map;
use Carbon\Carbon;

/**
 * Queue Allocations
 *
 * @apiUri    /allocations
 */
class AllocationsController extends Controller
{
	/**
	 * Display a listing of allocations.
	 *
	 * @apiMethod GET
	 * @apiUri    /allocations/{hostname?}
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "hostname",
	 * 		"description":   "Scheduler hostname.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "format",
	 * 		"description":   "Output format. JSON is the default but output can be formatted to accommodate schedulers such as SLURM.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"enum": [
	 * 				"json",
	 * 				"slurmcfg"
	 * 			]
	 * 		}
	 * }
	 * @param   Request  $request
	 * @return  Response
	 */
	public function index(Request $request, $hostname = null)
	{
		/*
		"SELECT
			queues.name AS queuename,
			queues.enabled,
			queues.started,
			queues.cluster,
			queues.priority,
			queues.defaultwalltime,
			queues.maxjobsqueued,
			queues.maxjobsqueueduser,
			queues.maxjobsrun,
			queues.maxjobsrunuser,
			queues.maxjobcores,
			queues.nodecoresmin,
			queues.nodecoresmax,
			queues.nodememmin,
			queues.nodememmax,
			queues.aclgroups,
			SUM(queuecores.corecount) AS corecount,
			MAX(queuewalltimes.walltime) AS walltime,
			IF(schedulers.datetimedraindown IS NULL OR schedulers.datetimedraindown = '0000-00-00 00:00:00', '0', '1') AS draindown,
			IF((UNIX_TIMESTAMP(schedulers.datetimedraindown) - UNIX_TIMESTAMP(NOW())) > '0', (UNIX_TIMESTAMP(schedulers.datetimedraindown) - UNIX_TIMESTAMP(NOW())), '0') AS draindown_timeremaining,
			queues.aclusersenabled,
			uniqaclusers.username,
			nodeaccesspolicy.code AS nodeaccesspolicy,
			defaultnodeaccesspolicy.code AS defaultnodeaccesspolicy,
			subresources.nodecores,
			queues.reservation,
			queues.maxijobfactor,
			queues.maxijobuserfactor,
			queues.groupid,
			subresources.nodegpus
		FROM
			schedulers
			INNER JOIN queues ON schedulers.id = queues.schedulerid
				AND  queues.datetimecreated       < NOW()
				AND (queues.datetimeremoved       > NOW() OR queues.datetimeremoved IS NULL OR queues.datetimeremoved = '0000-00-00 00:00:00')
			INNER JOIN schedulerpolicies AS nodeaccesspolicy ON nodeaccesspolicy.id = queues.schedulerpolicyid
			INNER JOIN schedulerpolicies AS defaultnodeaccesspolicy ON defaultnodeaccesspolicy.id = schedulers.schedulerpolicyid
			INNER JOIN subresources ON subresources.id = queues.subresourceid
			LEFT OUTER JOIN groups ON queues.groupid = groups.id
			LEFT OUTER JOIN (
				SELECT queueid, datetimestart, datetimestop, corecount FROM queuesizes
				UNION
				SELECT queueid, datetimestart, datetimestop, corecount FROM queueloans
				) AS queuecores ON queues.id = queuecores.queueid
					AND  queuecores.datetimestart     < NOW()
					AND (queuecores.datetimestop      > NOW() OR queuecores.datetimestop IS NULL OR queuecores.datetimestop = '0000-00-00 00:00:00')
			LEFT OUTER JOIN queuewalltimes ON queues.id = queuewalltimes.queueid
				AND  queuewalltimes.datetimestart < NOW()
				AND (queuewalltimes.datetimestop  > NOW() OR queuewalltimes.datetimestop IS NULL OR queuewalltimes.datetimestop = '0000-00-00 00:00:00')
			LEFT OUTER JOIN (
				SELECT DISTINCT
					queueid,
					username
				FROM
				(
					SELECT DISTINCT
						queues.id AS queueid,
						userusernames.username
					FROM schedulers
					INNER JOIN queues ON schedulers.id = queues.schedulerid
					INNER JOIN queueusers ON queues.id = queueusers.queueid
						AND  queueusers.membertype = '1'
						AND  queueusers.datetimecreated < NOW()
						AND (queueusers.datetimeremoved > NOW() OR queueusers.datetimeremoved IS NULL OR queueusers.datetimeremoved = '0000-00-00 00:00:00')
					INNER JOIN userusernames ON queueusers.userid = userusernames.userid
						AND  userusernames.datecreated  < NOW()
						AND (userusernames.dateremoved  > NOW() OR userusernames.dateremoved IS NULL OR userusernames.dateremoved = '0000-00-00 00:00:00')
					WHERE
						schedulers.hostname = 'cluster-adm.yourinstution'
				UNION
					SELECT DISTINCT
						queues.id AS queueid,
						userusernames.username
					FROM schedulers
					INNER JOIN queues ON schedulers.id = queues.schedulerid
					INNER JOIN groupusers ON queues.groupid = groupusers.groupid
						AND  groupusers.membertype = '2'
						AND  groupusers.datecreated     < NOW()
						AND (groupusers.dateremoved     > NOW() OR groupusers.dateremoved IS NULL OR groupusers.dateremoved = '0000-00-00 00:00:00')
					INNER JOIN userusernames ON groupusers.userid = userusernames.userid
						AND  userusernames.datecreated  < NOW()
						AND (userusernames.dateremoved  > NOW() OR userusernames.dateremoved IS NULL OR userusernames.dateremoved = '0000-00-00 00:00:00')
					WHERE
						schedulers.hostname = 'cluster-adm.yourinstution'
				) AS aclusers
			) AS uniqaclusers ON (queues.id = uniqaclusers.queueid OR uniqaclusers.queueid = '0')
		WHERE
			schedulers.hostname = 'cluster-adm.yourinstution'
			AND schedulers.batchsystem = '1'
		GROUP BY
			queuename,
			username,
			enabled,
			started,
			cluster,
			priority,
			defaultwalltime,
			maxjobsqueued,
			maxjobsqueueduser,
			maxjobsrun,
			maxjobsrunuser,
			maxjobcores,
			nodecoresmin,
			nodecoresmax,
			nodememmin,
			nodememmax,
			aclgroups,
			draindown,
			draindown_timeremaining,
			aclusersenabled,
			username,
			nodeaccesspolicy,
			defaultnodeaccesspolicy,
			nodecores,
			reservation,
			maxijobfactor,
			maxijobuserfactor,
			groupid,
			nodegpus";
		*/

		$isAdmin = (auth()->user() && auth()->user()->can('manage resources'));

		$q = (new Queue)->getTable();
		$s = (new Scheduler)->getTable();
		$r = (new Subresource)->getTable();
		$c = (new Child)->getTable();
		$a = (new Asset)->getTable();
		$p = (new SchedulerPolicy)->getTable();

		$now = Carbon::now();

		$query = Queue::query()
			->select(
				$q . '.*',
				$p . '.code AS nodeaccesspolicy',
				$r . '.nodecores',
				$r . '.nodegpus'
			)
			->join($s, $s . '.id', $q . '.schedulerid')
			->join($r, $r . '.id', $q . '.subresourceid')
			->join($p, $p . '.id', $q . '.schedulerpolicyid')
			->join($c, $c . '.subresourceid', $r . '.id')
			->join($a, $a . '.id', $c . '.resourceid')
			->where($q . '.datetimecreated', '<', $now->toDateTimeString())
			->whereNull($s . '.datetimeremoved')
			->whereNull($r . '.datetimeremoved');

		if (!$isAdmin)
		{
			$query->whereNull($a . '.datetimeremoved');
		}
			//->where($s . '.batchsystem', '=', 1)

		if ($hostname)
		{
			$query->where($s . '.hostname', '=', $hostname);
		}

		$queues = $query
			->orderBy($r . '.name', 'asc')
			->orderBy($q . '.name', 'asc')
			->get();

		/*$queues->reject(function ($queue, $key)
		{
			// Count loans
			$allocations = $queue->loans()
				->where('datetimestart', '<', Carbon::now()->toDateTimeString())
				->where(function($where)
				{
					$where->whereNull('datetimestop')
						->orWhere('datetimestop', '>', Carbon::now()->toDateTimeString());
				})
				->count();

			// Count purchases
			$allocations += $queue->sizes()
				->where('datetimestart', '<', Carbon::now()->toDateTimeString())
				->where(function($where)
				{
					$where->whereNull('datetimestop')
						->orWhere('datetimestop', '>', Carbon::now()->toDateTimeString());
				})
				->count();

			// Count walltimes
			$allocations += $queue->walltimes()
				->where('datetimestart', '<', Carbon::now()->toDateTimeString())
				->where(function($where)
				{
					$where->whereNull('datetimestop')
						->orWhere('datetimestop', '>', Carbon::now()->toDateTimeString());
				})
				->count();

			return $allocations <= 0;
		});*/

		if ($format = $request->input('format'))
		{
			if ($format == 'slurmcfg')
			{
				$admin_users = ['root', 'nagios', 'rcactest', 'rcacdata'];
				$admin_groups = ['rcacadms', 'rcacsupp'];

				// Get admins
				$roles = config('module.queues.admins', []);

				if (!empty($roles))
				{
					$admins = Map::query()
						->whereIn('role_id', $roles)
						->get()
						->pluck('user_id')
						->toArray();
					$admins = array_unique($admins);

					$usernames = UserUsername::query()
						->whereIn('userid', $admins)
						->get()
						->pluck('username')
						->toArray();

					$admin_users = array_merge($admin_users, $usernames);
				}

				$scheduler = Scheduler::query()
					->where('hostname', '=', $hostname)
					->first();

				$out = array();
				$out[] = "# Cluster - 'cluster_name':MaxTRESPerJob=node=50";
				$out[] = "# Followed by Accounts you want in this fashion (root is created by default)...";
				$out[] = "# Parent - 'root'";
				$out[] = "# Account - 'cs':MaxTRESPerJob=node=5:MaxJobs=4:MaxTRESMinsPerJob=cpu=20:FairShare=399:MaxWallDurationPerJob=40:Description='Computer Science':Organization='LC'";
				$out[] = "# Any of the options after a ':' can be left out and they can be in any order.";
				$out[] = "# If you want to add any sub accounts just list the Parent THAT HAS ALREADY";
				$out[] = "# BEEN CREATED before the account line in this fashion...";
				$out[] = "# Parent - 'cs'";
				$out[] = "# Account - 'test':MaxTRESPerJob=node=1:MaxJobs=1:MaxTRESMinsPerJob=cpu=1:FairShare=1:MaxWallDurationPerJob=1:Description='Test Account':Organization='Test'";
				$out[] = "# To add users to a account add a line like this after a Parent - 'line'";
				$out[] = "# User - 'lipari':MaxTRESPerJob=node=2:MaxJobs=3:MaxTRESMinsPerJob=cpu=4:FairShare=1:MaxWallDurationPerJob=1";
				$out[] = "Cluster - '" . $scheduler->resource->rolename . "'";
				$out[] = "Parent - 'root'";
				$out[] = "User - 'root':DefaultAccount='partner':AdminLevel='Administrator':Fairshare=1";

				$users = array();

				foreach ($queues as $queue)
				{
					$unit = 'cores';
					$resource = $queue->resource;
					if ($facet = $resource->getFacet('allocation_unit'))
					{
						$unit = $facet->value;
					}

					if (!$queue->totalcores && !$queue->totalnodes && !$queue->serviceunits)
					{
						// No resources!
						continue;
					}

					if ($queue->groupid > 0 && !count($queue->users))
					{
						// No users!
						continue;
					}

					$line = array();
					$line[] = "Account - '" . $queue->name . "'";
					$line[] = "Description='" . $scheduler->resource->rolename . "-" . $queue->cluster . "'";
					$line[] = "Organization='" . $queue->name . "'";
					$line[] = "Fairshare=1";

					//$line[] = "GrpTRES=cpu=128,gres/gpu=2";
					$nodecores = $queue->subresource->nodecores;

					if ($queue->groupid <= 0)
					{
						$newhardware = $queue->sizes()->orderBy('datetimestart', 'asc')->first();
						$totalcores = ($newhardware ? $newhardware->corecount : abs($queue->totalcores));
						$l = "GrpTRES=cpu=" . $totalcores;

						if ($unit == 'gpus' && $queue->subresource->nodegpus)
						{
							//$tres['gres/gpu'] = $queue->totalcores;
							$nodes = round($totalcores / $nodecores, 1);
							//$nodes = round($queue->totalcores / $queue->subresource->nodegpus, 1);
							//$l  = "GrpTRES=cpu=" . ($nodecores ? $nodes * $nodecores : 0);
							$l .= ',gres/gpu=' . ($queue->serviceunits ? $queue->serviceunits : round($nodes * $queue->subresource->nodegpus)); //$queue->totalcores;
						}
						elseif ($unit == 'sus')
						{
						}
					}
					else
					{
						$l = "GrpTRES=cpu=" . $queue->totalcores; //($nodecores ? round($queue->totalcores / $nodecores, 1) : 0);

						if ($unit == 'gpus' && $queue->subresource->nodegpus)
						{
							//$tres['gres/gpu'] = $queue->totalcores;
							$nodes = round($queue->totalcores / $nodecores, 1);
							//$nodes = round($queue->totalcores / $queue->subresource->nodegpus, 1);
							//$l  = "GrpTRES=cpu=" . ($nodecores ? $nodes * $nodecores : 0);
							$l .= ',gres/gpu=' . ($queue->serviceunits ? $queue->serviceunits : round($nodes * $queue->subresource->nodegpus)); //$queue->totalcores;
						}
						elseif ($unit == 'sus')
						{
						}
					}
					$line[] = $l;

					if ($queue->maxjobsqueued)
					{
						$line[] = "GrpSubmitJobs=" . $queue->maxjobsqueued;
					}
					if ($queue->maxjobsrunuser)
					{
						$line[] = "MaxJobs=" . $queue->maxjobsrunuser;
					}
					if ($queue->maxjobsqueueduser)
					{
						$line[] = "MaxSubmitJobs=" . $queue->maxjobsqueueduser;
					}
					if ($queue->walltime)
					{
						$line[] = "MaxWallDurationPerJob=" . ($queue->walltime / 60);
					}
					if ($queue->priority)
					{
						$line[] = "Priority=" . $queue->priority;
					}

					if (count($queue->qos))
					{
						$line[] = "QOS='+" . implode(',+', $queue->qos->pluck('name')->toArray()) . "'";
					}

					$out[] = implode(':', $line);

					$users[] = "Parent - '" . $queue->name . "'";

					// System queue - add all admins
					if ($queue->groupid <= 0)
					{
						foreach ($admin_users as $username)
						{
							// User - 'aliaga':Partition='gilbreth-g':DefaultAccount='partner':Fairshare=1:GrpTRES=cpu=128:GrpSubmitJobs=12000:MaxSubmitJobs=5000:MaxWallDurationPerJob=20160:Priority=1000
							$uline = ["User - '" . $username . "'"];
							$uline[] = "Partition='" . $scheduler->resource->rolename . "-" . $queue->cluster . "'";
							$uline[] = "DefaultAccount='partner'";
							$uline[] = "AdminLevel='Administrator'";
							$uline[] = "Fairshare=1";
							$uline[] = $l;
							if ($queue->maxjobsqueued)
							{
								$uline[] = "GrpSubmitJobs=" . $queue->maxjobsqueued;
							}
							if ($queue->maxjobsrunuser)
							{
								$uline[] = "MaxJobs=" . $queue->maxjobsrunuser;
							}
							if ($queue->maxjobsqueueduser)
							{
								$uline[] = "MaxSubmitJobs=" . $queue->maxjobsqueueduser;
							}
							if ($queue->walltime)
							{
								$uline[] = "MaxWallDurationPerJob=" . ($queue->walltime / 60);
							}
							if ($queue->priority)
							{
								$uline[] = "Priority=" . $queue->priority;
							}

							$users[] = implode(':', $uline);
						}
					}

					foreach ($queue->users as $queueuser)
					{
						if (!$queueuser->user)
						{
							continue;
						}
						//User - 'aliaga':Partition='gilbreth-g':DefaultAccount='partner':Fairshare=1:GrpTRES=cpu=128:GrpSubmitJobs=12000:MaxSubmitJobs=5000:MaxWallDurationPerJob=20160:Priority=1000
						$uline = ["User - '" . $queueuser->user->username . "'"];
						$uline[] = "Partition='" . $scheduler->resource->rolename . "-" . $queue->cluster . "'";
						$uline[] = "DefaultAccount='partner'";
						if (in_array($queueuser->user->username, $admin_users))
						{
							$uline[] = "AdminLevel='Administrator'";
						}
						$uline[] = "Fairshare=1";
						$uline[] = $l;
						if ($queue->maxjobsqueued)
						{
							$uline[] = "GrpSubmitJobs=" . $queue->maxjobsqueued;
						}
						if ($queue->maxjobsrunuser)
						{
							$uline[] = "MaxJobs=" . $queue->maxjobsrunuser;
						}
						if ($queue->maxjobsqueueduser)
						{
							$uline[] = "MaxSubmitJobs=" . $queue->maxjobsqueueduser;
						}
						if ($queue->walltime)
						{
							$uline[] = "MaxWallDurationPerJob=" . ($queue->walltime / 60); // Minutes
						}
						if ($queue->priority)
						{
							$uline[] = "Priority=" . $queue->priority;
						}

						$users[] = implode(':', $uline);
					}
				}

				$out = array_merge($out, $users);

				$filename = $scheduler->resource->rolename . '.cfg';

				$headers = array(
					'Content-type' => 'text/plain',
					'Content-Disposition' => 'attachment; filename=' . $filename,
					'Pragma' => 'no-cache',
					'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
					'Expires' => '0',
					'Last-Modified' => gmdate('D, d M Y H:i:s') . ' GMT'
				);

				$callback = function() use ($out)
				{
					$file = fopen('php://output', 'w');

					foreach ($out as $datum)
					{
						fputs($file, $datum . "\n");
					}
					fclose($file);
				};

				return response()->streamDownload($callback, $filename, $headers);
			}
		}

		return new AllocationResourceCollection($queues);
	}

	/**
	 * Create an allocation
	 *
	 * @apiMethod POST
	 * @apiUri    /allocations
	 * @apiAuthorization  true
	 * @apiResponse {
	 * 		"201": {
	 * 			"description": "Successful entry creation"
	 * 		},
	 * 		"401": {
	 * 			"description": "Unauthorized"
	 * 		},
	 * 		"409": {
	 * 			"description": "Invalid data"
	 * 		}
	 * }
	 * @param  Request  $request
	 * @return Response
	 */
	public function create(Request $request)
	{
		$data = $request->all();

		event($event = new AllocationCreate($data));

		return new JsonResource($event->response);
	}

	/**
	 * Update an allocation
	 *
	 * @apiMethod PUT
	 * @apiUri    /allocations/{id}
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"201": {
	 * 			"description": "Successful entry creation"
	 * 		},
	 * 		"401": {
	 * 			"description": "Unauthorized"
	 * 		},
	 * 		"409": {
	 * 			"description": "Invalid data"
	 * 		}
	 * }
	 * @param   integer  $id
	 * @param   Request  $request
	 * @return  Response
	 */
	public function update($id, Request $request)
	{
		$data = $request->all();

		event($event = new AllocationUpdate($id, $data));

		return new JsonResource($event->response);
	}

	/**
	 * Delete an allocation
	 *
	 * @apiMethod DELETE
	 * @apiUri    /allocations/{id}
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"204": {
	 * 			"description": "Successful entry deletion"
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		}
	 * }
	 * @param   integer  $id
	 * @return  Response
	 */
	public function delete($id)
	{
		event($event = new AllocationDelete($id));

		return response()->json(null, 204);
	}
}
