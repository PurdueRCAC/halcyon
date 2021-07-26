<?php

namespace App\Modules\Queues\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Queues\Events\AllocationCreate;
use App\Modules\Queues\Events\AllocationUpdate;
use App\Modules\Queues\Events\AllocationDelete;

/**
 * Queues
 *
 * @apiUri    /api/allocations
 */
class AllocationsController extends Controller
{
	/**
	 * Display a listing of the queue.
	 *
	 * @apiMethod GET
	 * @apiUri    /api/allocations
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
	 * Create an allocation
	 *
	 * @apiMethod POST
	 * @apiUri    /api/allocations
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "x-project",
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
		$data = $request->all();

		event($event = new AllocationCreate($data));

		return new JsonResource($event->response);
	}

	/**
	 * Read a queue
	 *
	 * @apiMethod GET
	 * @apiUri    /api/allocations/{id}
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
	 * @apiUri    /api/allocations/{id}
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
	 * Delete a queue
	 *
	 * @apiMethod DELETE
	 * @apiUri    /api/allocations/{id}
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
		event($event = new AllocationDelete($id));

		return response()->json(null, 204);
	}
}
