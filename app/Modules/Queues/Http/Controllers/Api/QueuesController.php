<?php

namespace App\Modules\Queues\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Modules\Queues\Http\Resources\QueueResourceCollection;
use App\Modules\Queues\Http\Resources\QueueResource;
use App\Modules\Queues\Models\Queue;
use App\Modules\Queues\Models\Walltime;
//use App\Modules\Queues\Models\Type;
//use App\Modules\Queues\Models\Scheduler;
//use App\Modules\Queues\Models\SchedulerPolicy;
//use App\Modules\Resources\Models\Subresource;
use App\Modules\Resources\Models\Child;
use App\Modules\Resources\Models\Asset;

/**
 * Queues
 *
 * @apiUri    /queues
 */
class QueuesController extends Controller
{
	/**
	 * Display a listing of the queue.
	 *
	 * @apiMethod GET
	 * @apiUri    /queues
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "expand",
	 * 		"description":   "Comma-separated list of associated objects, such as group and resource, to include. Members, purchases, and loans are always included.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   null,
	 * 			"enum": [
	 * 				"all",
	 * 				"group",
	 * 				"resource",
	 * 				"subresource",
	 * 				"schedulerpolicy",
	 * 				"scheduler"
	 * 			]
	 * 		}
	 * }
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
	 * 				"queuetype",
	 * 				"schedulerid",
	 * 				"subresourceid",
	 * 				"schedulerpolicyid",
	 * 				"cluster"
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
			'search'    => $request->input('search', ''),
			'state'     => $request->input('state', 'enabled'),
			'type'      => $request->input('type', 0),
			'scheduler' => $request->input('scheduler', 0),
			'resource'  => $request->input('resource', 0),
			'subresource' => $request->input('subresource', 0),
			'group'     => $request->input('group', 0),
			'class'     => $request->input('class'),
			// Paging
			'limit'     => $request->input('limit', config('list_limit', 20)),
			'page'      => $request->input('page', 1),
			// Sorting
			'order'     => $request->input('order', Queue::$orderBy),
			'order_dir' => $request->input('order_dir', Queue::$orderDir)
		);

		if (!in_array($filters['order'], ['id', 'name', 'queuetype', 'datetimecreated', 'datetimeremoved', 'schedulerid', 'subresourceid', 'schedulerpolicyid', 'cluster']))
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
			->with('scheduler')
			->leftJoin($c, $c . '.subresourceid', $q . '.subresourceid')
			->leftJoin($r, $r . '.id', $c . '.resourceid')
			->whereNull($r . '.datetimeremoved')
			->withTrashed();

		if ($filters['state'] == 'trashed')
		{
			$query->whereNotNull($q . '.datetimeremoved');
		}
		elseif ($filters['state'] == 'enabled')
		{
			$query
				->whereNull($q . '.datetimeremoved')
				->where($q . '.enabled', '=', 1);
		}
		elseif ($filters['state'] == 'disabled')
		{
			$query
				->whereNull($q . '.datetimeremoved')
				->where($q . '.enabled', '=', 0);
		}
		else
		{
			$query
				->whereNull($q . '.datetimeremoved');
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
	 * @apiUri    /queues
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
	 * 			"type":      "string",
	 * 			"maxLength": 5
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "nodememmin",
	 * 		"description":   "Node memory max",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 5
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
	 * @apiResponse {
	 * 		"201": {
	 * 			"description": "Successful entry creation"
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
		$rules = [
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
			'defaultwalltime'   => 'nullable|numeric',
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
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$queue = new Queue;
		foreach ($rules as $key => $rule)
		{
			if ($request->has($key))
			{
				$queue->{$key} = $request->input($key);
			}
		}
		$queue->aclgroups = $queue->aclgroups ?: '';
		$queue->cluster = $queue->cluster ?: '';

		$exists = Queue::query()
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

		$walltime = Walltime::query()
			->where('queueid', '=', $queue->id)
			->orderBy('id', 'asc')
			->first();
		if (!$walltime)
		{
			$walltime = new Walltime;
		}
		$walltime->queueid = $queue->id;
		$walltime->walltime = intval(floatval($request->input('maxwalltime')) * 60 * 60);
		$walltime->datetimestart = $queue->datetimecreated;
		$walltime->save();

		if ($request->input('queueclass') == 'standby')
		{
			$size = new Size;
			$size->queueid = $queue->id;
			$size->corecount = 20000;
			$size->datetimestart = $queue->datetimecreated;
			$size->save();
		}

		return new QueueResource($queue);
	}

	/**
	 * Read a queue
	 *
	 * @apiMethod GET
	 * @apiUri    /queues/{id}
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
	 * 		"in":            "query",
	 * 		"name":          "expand",
	 * 		"description":   "Comma-separated list of associated objects, such as group and resource, to include. Members, purchases, and loans are always included.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   null,
	 * 			"enum": [
	 * 				"all",
	 * 				"group",
	 * 				"resource",
	 * 				"subresource",
	 * 				"schedulerpolicy",
	 * 				"scheduler"
	 * 			]
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"200": {
	 * 			"description": "Successful entry read"
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
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
	 * @apiUri    /queues/{id}
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
	 * 			"type":      "string",
	 * 			"maxLength": 5
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "nodememmin",
	 * 		"description":   "Node memory max",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 5
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
	 * @apiResponse {
	 * 		"204": {
	 * 			"description": "Successful entry modification"
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
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
		$rules = [
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
			'defaultwalltime'   => 'nullable|numeric',
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
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$queue = Queue::findOrFail($id);
		//$queue->update($request->all());
		foreach ($rules as $key => $rule)
		{
			if ($request->has($key))
			{
				$queue->{$key} = $request->input($key);
			}
		}
		$queue->save();

		return new QueueResource($queue);
	}

	/**
	 * Delete a queue
	 *
	 * @apiMethod DELETE
	 * @apiUri    /queues/{id}
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
		$queue = Queue::findOrFail($id);

		if (!$queue->trashed())
		{
			$queue->delete();
		}

		return response()->json(null, 204);
	}
}
