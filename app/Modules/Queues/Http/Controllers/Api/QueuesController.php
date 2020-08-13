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
//use App\Modules\Resources\Entities\Subresource;
use App\Modules\Resources\Entities\Child;
use App\Modules\Resources\Entities\Asset;

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
	 * 		"name":          "limit",
	 * 		"description":   "Number of result to return.",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       25
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "page",
	 * 		"description":   "Number of where to start returning results.",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       0
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "search",
	 * 		"description":   "A word or phrase to search for.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       ""
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "order",
	 * 		"description":   "Field to sort results by.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "created",
	 * 		"allowedValues": "id, name, datetimecreated, datetimeremoved, parentid"
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "order_dir",
	 * 		"description":   "Direction to sort results by.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "desc",
	 * 		"allowedValues": "asc, desc"
	 * }
	 * @param   Request  $request
	 * @return Response
	 */
	public function index(Request $request)
	{
		$filters = array(
			'search'   => $request->input('search', ''),
			'state'    => $request->input('state', 'enabled'),
			'type'     => $request->input('type', 0),
			'scheduler' => $request->input('scheduler', 0),
			'resource' => $request->input('resource', 0),
			'class' => $request->input('class'),
			// Paging
			'limit'    => $request->input('limit', config('list_limit', 20)),
			//'start' => $request->input('limitstart', 0),
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
			->whereNull($r . '.datetimeremoved');

		if ($filters['state'] == 'trashed')
		{
			$query->onlyTrashed();
			//$query->where($q . '.datetimeremoved', '!=', '0000-00-00 00:00:00');
		}
		elseif ($filters['state'] == 'enabled')
		{
			$query//->where('datetimeremoved', '=', '0000-00-00 00:00:00')
				->where($q . '.enabled', '=', 1);
		}
		else
		{
			$query//->where('datetimeremoved', '=', '0000-00-00 00:00:00')
				->where($q . '.enabled', '=', 0);
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
			->paginate($filters['limit'])
			->appends(array_filter($filters));

		return new QueueResourceCollection($rows);
	}

	/**
	 * Create a queue
	 *
	 * @apiMethod POST
	 * @apiUri    /api/queues
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "name",
	 *      "description":   "The name of the queue type",
	 *      "type":          "string",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @param   Request  $request
	 * @return Response
	 */
	public function create(Request $request)
	{
		$request->validate([
			'name' => 'required|string|max:64'
		]);

		$queue = Queue::create($request->all());
		/*$queue = new Queue([
			'name'         => $request->input('name'),
			'parentid'     => $request->input('parentid'),
			'rolename'     => $request->input('rolename'),
			'listname'     => $request->input('listname'),
			'queuetype' => $request->input('queuetype'),
			'producttype'  => $request->input('producttype')
		]);

		if ($queue->schedulerid && !$queue->scheduler)
		{
			abort(415, trans('Invalid scheduler ID'));
		}

		if ($queue->groupid && !$queue->group)
		{
			abort(415, trans('Invalid group ID'));
		}

		if ($queue->schedulerpolicyid && !$queue->schedulerPolicy)
		{
			abort(415, trans('Invalid group ID'));
		}

		if (!$queue->save())
		{
			abort(415, $queue->getError());
		}*/

		return new QueueResource($queue);
	}

	/**
	 * Read a queue
	 *
	 * @apiMethod GET
	 * @apiUri    /api/queues/{id}
	 * @apiParameter {
	 * 		"in":            "query",
	 *      "name":          "id",
	 *      "description":   "The ID of the queue type",
	 *      "type":          "integer",
	 *      "required":      true,
	 *      "default":       ""
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
	 * @apiParameter {
	 * 		"in":            "query",
	 *      "name":          "id",
	 *      "description":   "The ID of the queue type",
	 *      "type":          "integer",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "name",
	 *      "description":   "The name of the queue type",
	 *      "type":          "string",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @param   integer  $id
	 * @param   Request  $request
	 * @return  Response
	 */
	public function update($id, Request $request)
	{
		$request->validate([
			'name' => 'nullable|string|max:64',
			'datetimelastseen' => 'nullable|date'
		]);

		$queue = Queue::findOrFail($id);
		$queue->update($request->all());

		/*$queue = Queue::findOrFail($id);
		$queue->set([
			'name'         => $request->get('name'),
			'parentid'     => $request->get('parentid'),
			'rolename'     => $request->get('rolename'),
			'listname'     => $request->get('listname'),
			'queuetype' => $request->get('queuetype'),
			'producttype'  => $request->get('producttype')
		]);

		$queue->save();*/

		//event(new QueueUpdated($queue));

		return new QueuesQueue($queue);
	}

	/**
	 * Delete a queue
	 *
	 * @apiMethod DELETE
	 * @apiUri    /api/queues/{id}
	 * @apiParameter {
	 * 		"in":            "query",
	 *      "name":          "id",
	 *      "description":   "The ID of the queue type",
	 *      "type":          "integer",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @param   integer  $id
	 * @return  Response
	 */
	public function delete($id)
	{
		$queue = Queue::findOrFail($id);

		if (!$queue->isTrashed())
		{
			$queue->softDeletes();
		}

		return response()->json(null, 204);
	}
}
