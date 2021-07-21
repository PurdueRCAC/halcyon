<?php

namespace App\Modules\Queues\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Queues\Http\Resources\QueueResourceCollection;
use App\Modules\Queues\Http\Resources\QueueResource;
use App\Modules\Queues\Models\Queue;
//use App\Modules\Queues\Models\Type;
//use App\Modules\Queues\Models\Scheduler;
//use App\Modules\Queues\Models\SchedulerPolicy;
//use App\Modules\Resources\Models\Subresource;
use App\Modules\Resources\Models\Child;
use App\Modules\Resources\Models\Asset;

/**
 * Queues
 *
 * @apiUri    /api/queues
 */
class QueuesController extends Controller
{
	/**
	 * Display a listing of the queue.
	 *
	 * @apiMethod GET
	 * @apiUri    /api/queues
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "state",
	 * 		"description":   "Queue state.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "enabled",
	 * 			"enum": [
	 * 				"enabled",
	 * 				"disabled",
	 * 				"trashed"
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "type",
	 * 		"description":   "Queue type ID.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "scheduler",
	 * 		"description":   "Scheduler type ID.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "resource",
	 * 		"description":   "Resource ID.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "subresource",
	 * 		"description":   "Subresource ID.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "search",
	 * 		"description":   "A word or phrase to search for.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "limit",
	 * 		"description":   "Number of result to return.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   20
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "page",
	 * 		"description":   "Number of where to start returning results.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   1
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "order",
	 * 		"description":   "Field to sort results by.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "datetimecreated",
	 * 			"enum": [
	 * 				"id",
	 * 				"name",
	 * 				"datetimecreated",
	 * 				"datetimeremoved",
	 * 				"parentid"
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "order_dir",
	 * 		"description":   "Direction to sort results by.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "desc",
	 * 			"enum": [
	 * 				"asc",
	 * 				"desc"
	 * 			]
	 * 		}
	 * }
	 * @param   Request  $request
	 * @return  Response
	 */
	public function index(Request $request)
	{
		$filters = array(
			'search'   => $request->input('search', ''),
			'state'    => $request->input('state', 'enabled'),
			'type'     => $request->input('type', 0),
			'scheduler' => $request->input('scheduler', 0),
			'resource' => $request->input('resource', 0),
			'subresource' => $request->input('subresource', 0),
			'group' => $request->input('group', 0),
			'class' => $request->input('class'),
			// Paging
			'limit'    => $request->input('limit', config('list_limit', 20)),
			'page'     => $request->input('page', 1),
			// Sorting
			'order'     => $request->input('order', Queue::$orderBy),
			'order_dir' => $request->input('order_dir', Queue::$orderDir)
		);

		if (!in_array($filters['order'], ['id', 'name', 'state', 'type', 'parent']))
		{
			$filters['order'] = Queue::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Queue::$orderDir;
		}

		// Build query
		$q = (new Queue)->getTable();
		$c = (new Child)->getTable();
		$r = (new Asset)->getTable();

		$query = Queue::query()
			->select($q . '.*')
			->join($c, $c . '.subresourceid', $q . '.subresourceid')
			->join($r, $r . '.id', $c . '.resourceid')
			->where(function($where) use ($r)
			{
				$where->whereNull($r . '.datetimeremoved')
					->orWhere($r . '.datetimeremoved', '=', '0000-00-00 00:00:00');
			})
			->withTrashed();

		if ($filters['state'] == 'trashed')
		{
			$query->where(function($where) use ($q)
			{
				$where->whereNotNull($q . '.datetimeremoved')
					->where($q . '.datetimeremoved', '!=', '0000-00-00 00:00:00');
			});
		}
		elseif ($filters['state'] == 'enabled')
		{
			$query
				->where(function($where) use ($q)
				{
					$where->whereNull($q . '.datetimeremoved')
						->orWhere($q . '.datetimeremoved', '=', '0000-00-00 00:00:00');
				})
				->where($q . '.enabled', '=', 1);
		}
		elseif ($filters['state'] == 'disabled')
		{
			$query
				->where(function($where) use ($q)
				{
					$where->whereNull($q . '.datetimeremoved')
						->orWhere($q . '.datetimeremoved', '=', '0000-00-00 00:00:00');
				})
				->where($q . '.enabled', '=', 0);
		}
		else
		{
			$query
				->where(function($where) use ($q)
				{
					$where->whereNull($q . '.datetimeremoved')
						->orWhere($q . '.datetimeremoved', '=', '0000-00-00 00:00:00');
				});
		}

		if ($filters['type'] > 0)
		{
			$query->where($q . '.queuetype', '=', (int)$filters['type']);
		}

		if ($filters['scheduler'])
		{
			$query->where($q . '.schedulerid', '=', (int)$filters['scheduler']);
		}

		if ($filters['resource'])
		{
			$query->where($r . '.id', '=', (int)$filters['resource']);
		}

		if ($filters['subresource'])
		{
			$query->where($q . '.subresourceid', '=', (int)$filters['subresource']);
		}

		if ($filters['group'])
		{
			$query->where($q . '.groupid', '=', (int)$filters['group']);
		}

		if ($filters['class'] == 'system')
		{
			$query->where($q . '.groupid', '<=', 0);
		}
		elseif ($filters['class'] == 'owner')
		{
			$query->where($q . '.groupid', '>', 0);
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page'])
			->appends(array_filter($filters));

		return new QueueResourceCollection($rows);
	}

	/**
	 * Create a queue
	 *
	 * @apiMethod POST
	 * @apiUri    /api/queues
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "schedulerid",
	 * 		"description":   "Scheduler ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "subresourceid",
	 * 		"description":   "Subresource ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "name",
	 * 		"description":   "The name of the queue",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 64
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "groupid",
	 * 		"description":   "Group ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "queuetype",
	 * 		"description":   "Queue type",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "automatic",
	 * 		"description":   "Automatic",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "free",
	 * 		"description":   "Free",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "schedulerpolicyid",
	 * 		"description":   "Scheduler Policy ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "enabled",
	 * 		"description":   "Enabled",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "started",
	 * 		"description":   "Started",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "reservation",
	 * 		"description":   "Reservation",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "cluster",
	 * 		"description":   "Cluster",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 32
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "priority",
	 * 		"description":   "Priority",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "defaultwalltime",
	 * 		"description":   "Default walltime",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "maxjobsqueued",
	 * 		"description":   "Max Jobs Queued",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "maxjobsqueueduser",
	 * 		"description":   "Max Jobs Queued User",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "maxjobsrun",
	 * 		"description":   "Max Jobs Run",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "maxjobsrunuser",
	 * 		"description":   "Max Jobs Run User",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "maxjobscore",
	 * 		"description":   "Max Jobs Core",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "nodecoresdefault",
	 * 		"description":   "Default nodes core",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "nodecoresmin",
	 * 		"description":   "Nodes core min",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "nodecoresmax",
	 * 		"description":   "Nodes core max",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "nodememmin",
	 * 		"description":   "Node memory min",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "nodememmin",
	 * 		"description":   "Node memory max",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "aclusersenabled",
	 * 		"description":   "ACL Users enabled",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "aclgroups",
	 * 		"description":   "Comma-separated list of ACL Groups",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 255
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "maxijobfactor",
	 * 		"description":   "Max ijob factor",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "maxijobuserfactor",
	 * 		"description":   "Max ijob user factor",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @param   Request  $request
	 * @return Response
	 */
	public function create(Request $request)
	{
		$request->validate([
			'schedulerid'       => 'nullable|integer',
			'subresourceid'     => 'nullable|integer',
			'name'              => 'required|string|max:64',
			'groupid'           => 'nullable|integer',
			'queuetype'         => 'required|integer',
			'automatic'         => 'nullable|integer',
			'free'              => 'nullable|integer',
			'schedulerpolicyid' => 'nullable|integer',
			'enabled'           => 'nullable|integer',
			'started'           => 'nullable|integer',
			'reservation'       => 'nullable|integer',
			'cluster'           => 'nullable|string|max:32',
			'priority'          => 'nullable|integer',
			'defaultwalltime'   => 'nullable|integer',
			'maxjobsqueued'     => 'nullable|integer',
			'maxjobsqueueduser' => 'nullable|integer',
			'maxjobsrun'        => 'nullable|integer',
			'maxjobsrunuser'    => 'nullable|integer',
			'maxjobscore'       => 'nullable|integer',
			'nodecoresdefault'  => 'nullable|integer',
			'nodecoresmin'      => 'nullable|integer',
			'nodecoresmax'      => 'nullable|integer',
			'nodememmin'        => 'nullable|string|max:5',
			'nodememmax'        => 'nullable|string|max:5',
			'aclusersenabled'   => 'nullable|integer',
			'aclgroups'         => 'nullable|string|max:255',
			'maxijobfactor'     => 'nullable|integer',
			'maxijobuserfactor' => 'nullable|integer',
		]);

		$queue = new Queue;
		$queue->fill($request->all());

		$queue->cluster = $queue->cluster ?: '';

		$exists = Queue::query()
			->withTrashed()
			->whereIsActive()
			->where('name', '=', $queue->name)
			->where('schedulerid', '=', $queue->schedulerid)
			->first();

		if ($exists)
		{
			return response()->json(trans('queues::queues.error.queue already exists'), 409);
		}

		if ($queue->schedulerid && !$queue->scheduler)
		{
			return response()->json(trans('queues::queues.error.invalid scheduler id'), 409);
		}

		if ($queue->subresourceid && !$queue->subresource)
		{
			return response()->json(trans('queues::queues.error.invalid subresource id'), 409);
		}

		if ($queue->groupid && !$queue->group)
		{
			return response()->json(trans('queues::queues.error.invalid group id'), 409);
		}

		if ($queue->schedulerpolicyid && !$queue->schedulerPolicy)
		{
			return response()->json(trans('queues::queues.error.invalid schedulerpolicy id'), 409);
		}

		if (!$queue->save())
		{
			return response()->json(trans('queues::queues.error.creation failed'), 500);
		}

		return new QueueResource($queue);
	}

	/**
	 * Read a queue
	 *
	 * @apiMethod GET
	 * @apiUri    /api/queues/{id}
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @param   integer  $id
	 * @return  Response
	 */
	public function read($id)
	{
		$queue = Queue::findOrFail($id);

		return new QueueResource($queue);
	}

	/**
	 * Update a queue
	 *
	 * @apiMethod PUT
	 * @apiUri    /api/queues/{id}
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
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "schedulerid",
	 * 		"description":   "Scheduler ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "subresourceid",
	 * 		"description":   "Subresource ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "name",
	 * 		"description":   "The name of the queue",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 64
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "groupid",
	 * 		"description":   "Group ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "queuetype",
	 * 		"description":   "Queue type",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "automatic",
	 * 		"description":   "Automatic",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "free",
	 * 		"description":   "Free",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "schedulerpolicyid",
	 * 		"description":   "Scheduler Policy ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "enabled",
	 * 		"description":   "Enabled",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "started",
	 * 		"description":   "Started",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "reservation",
	 * 		"description":   "Reservation",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "cluster",
	 * 		"description":   "Cluster",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 32
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "priority",
	 * 		"description":   "Priority",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "defaultwalltime",
	 * 		"description":   "Default walltime",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "maxjobsqueued",
	 * 		"description":   "Max Jobs Queued",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "maxjobsqueueduser",
	 * 		"description":   "Max Jobs Queued User",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "maxjobsrun",
	 * 		"description":   "Max Jobs Run",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "maxjobsrunuser",
	 * 		"description":   "Max Jobs Run User",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "maxjobscore",
	 * 		"description":   "Max Jobs Core",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "nodecoresdefault",
	 * 		"description":   "Default nodes core",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "nodecoresmin",
	 * 		"description":   "Nodes core min",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "nodecoresmax",
	 * 		"description":   "Nodes core max",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "nodememmin",
	 * 		"description":   "Node memory min",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "nodememmin",
	 * 		"description":   "Node memory max",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "aclusersenabled",
	 * 		"description":   "ACL Users enabled",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "aclgroups",
	 * 		"description":   "Comma-separated list of ACL Groups",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 255
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "maxijobfactor",
	 * 		"description":   "Max ijob factor",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "maxijobuserfactor",
	 * 		"description":   "Max ijob user factor",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @param   integer  $id
	 * @param   Request  $request
	 * @return  Response
	 */
	public function update($id, Request $request)
	{
		$request->validate([
			'schedulerid'       => 'nullable|integer',
			'subresourceid'     => 'nullable|integer',
			'name'              => 'nullable|string|max:64',
			'groupid'           => 'nullable|integer',
			'queuetype'         => 'nullable|integer',
			'automatic'         => 'nullable|integer',
			'free'              => 'nullable|integer',
			'schedulerpolicyid' => 'nullable|integer',
			'enabled'           => 'nullable|integer',
			'started'           => 'nullable|integer',
			'reservation'       => 'nullable|integer',
			'cluster'           => 'nullable|string|max:32',
			'priority'          => 'nullable|integer',
			'defaultwalltime'   => 'nullable|integer',
			'maxjobsqueued'     => 'nullable|integer',
			'maxjobsqueueduser' => 'nullable|integer',
			'maxjobsrun'        => 'nullable|integer',
			'maxjobsrunuser'    => 'nullable|integer',
			'maxjobscore'       => 'nullable|integer',
			'nodecoresdefault'  => 'nullable|integer',
			'nodecoresmin'      => 'nullable|integer',
			'nodecoresmax'      => 'nullable|integer',
			'nodememmin'        => 'nullable|string|max:5',
			'nodememmax'        => 'nullable|string|max:5',
			'aclusersenabled'   => 'nullable|integer',
			'aclgroups'         => 'nullable|string|max:255',
			'maxijobfactor'     => 'nullable|integer',
			'maxijobuserfactor' => 'nullable|integer',
		]);

		$queue = Queue::findOrFail($id);
		$queue->update($request->all());

		/*$queue = Queue::findOrFail($id);

		$queue->save();*/

		return new QueuesQueue($queue);
	}

	/**
	 * Delete a queue
	 *
	 * @apiMethod DELETE
	 * @apiUri    /api/queues/{id}
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
	 * @param   integer  $id
	 * @return  Response
	 */
	public function delete($id)
	{
		$queue = Queue::findOrFail($id);

		if (!$queue->isTrashed())
		{
			$queue->delete();
		}

		return response()->json(null, 204);
	}
}
