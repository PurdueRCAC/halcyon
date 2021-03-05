<?php

namespace App\Modules\Queues\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Queues\Models\Scheduler;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Schedulers
 *
 * @apiUri    /api/queues/schedulers
 */
class SchedulersController extends Controller
{
	/**
	 * Display a listing of queue schedulers
	 *
	 * @apiMethod GET
	 * @apiUri    /api/queues/schedulers
	 * @apiAuthorization  true
	 * @apiParameter {
	 *      "name":          "queuesubresourceid",
	 *      "description":   "Filter by queue subresource ID",
	 *      "type":          "integer",
	 *      "required":      false,
	 *      "default":       ""
	 * }
	 * @apiParameter {
	 *      "name":          "batchsystem",
	 *      "description":   "Filter by batchsystem ID",
	 *      "type":          "integer",
	 *      "required":      false,
	 *      "default":       ""
	 * }
	 * @apiParameter {
	 *      "name":          "schedulerpolicyid",
	 *      "description":   "Filter by scheduler policy ID",
	 *      "type":          "integer",
	 *      "required":      false,
	 *      "default":       ""
	 * }
	 * @apiParameter {
	 *      "name":          "limit",
	 *      "description":   "Number of result to return.",
	 *      "type":          "integer",
	 *      "required":      false,
	 *      "default":       25
	 * }
	 * @apiParameter {
	 *      "name":          "page",
	 *      "description":   "Number of where to start returning results.",
	 *      "type":          "integer",
	 *      "required":      false,
	 *      "default":       0
	 * }
	 * @apiParameter {
	 *      "name":          "search",
	 *      "description":   "A word or phrase to search for.",
	 *      "type":          "string",
	 *      "required":      false,
	 *      "default":       ""
	 * }
	 * @apiParameter {
	 *      "name":          "order",
	 *      "description":   "Field to sort results by.",
	 *      "type":          "string",
	 *      "required":      false,
	 *      "default":       "created",
	 *      "allowedValues": "id, name, datetimecreated, datetimeremoved, parentid"
	 * }
	 * @apiParameter {
	 *      "name":          "order_dir",
	 *      "description":   "Direction to sort results by.",
	 *      "type":          "string",
	 *      "required":      false,
	 *      "default":       "desc",
	 *      "allowedValues": "asc, desc"
	 * }
	 * @param   Request  $request
	 * @return Response
	 */
	public function index(Request $request)
	{
		$filters = array(
			'queuesubresourceid' => $request->input('queuesubresourceid'),
			'batchsystem' => $request->input('batchsystem'),
			'schedulerpolicyid' => $request->input('schedulerpolicyid'),
			// Paging
			'limit'    => $request->input('limit', config('list_limit', 20)),
			'page'     => $request->input('page', 1),
			// Sorting
			'order'     => $request->input('order', 'id'),
			'order_dir' => $request->input('order_dir', 'desc')
		);

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = 'asc';
		}

		$query = Scheduler::query();

		if ($filters['queuesubresourceid'])
		{
			$query->where('queuesubresourceid', '=', $filters['queuesubresourceid']);
		}

		if ($filters['batchsystem'])
		{
			$query->where('batchsystem', '=', $filters['batchsystem']);
		}

		if ($filters['schedulerpolicyid'])
		{
			$query->where('schedulerpolicyid', '=', $filters['schedulerpolicyid']);
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page'])
			->appends(array_filter($filters));

		return new ResourceCollection($rows);
	}

	/**
	 * Create a scheduler
	 *
	 * @apiMethod POST
	 * @apiUri    /api/queues/schedulers
	 * @apiAuthorization  true
	 * @apiParameter {
	 *      "name":          "hostname",
	 *      "description":   "Hostname",
	 *      "type":          "string",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @apiParameter {
	 *      "name":          "queuesubresourceid",
	 *      "description":   "Queue subresource ID",
	 *      "type":          "integer",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @apiParameter {
	 *      "name":          "batchsystem",
	 *      "description":   "Batchsystem ID",
	 *      "type":          "integer",
	 *      "required":      false,
	 *      "default":       ""
	 * }
	 * @apiParameter {
	 *      "name":          "Datetime for draining",
	 *      "description":   "The start time. Defaults to now.",
	 *      "type":          "string",
	 *      "required":      false,
	 *      "default":       ""
	 * }
	 * @apiParameter {
	 *      "name":          "datetimelastimportstart",
	 *      "description":   "Datetime for last import",
	 *      "type":          "string",
	 *      "required":      false,
	 *      "default":       ""
	 * }
	 * @apiParameter {
	 *      "name":          "schedulerpolicyid",
	 *      "description":   "Scheduler policy ID",
	 *      "type":          "integer",
	 *      "required":      false,
	 *      "default":       ""
	 * }
	 * @apiParameter {
	 *      "name":          "defaultmaxwalltime",
	 *      "description":   "Default max walltime in seconds",
	 *      "type":          "integer",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @param   Request  $request
	 * @return Response
	 */
	public function create(Request $request)
	{
		$request->validate([
			'hostname' => 'required|string|max:64',
			'queuesubresourceid' => 'required|integer|min:1',
			'batchsystem' => 'nullable|integer|min:1',
			'datetimedraindown' => 'nullable|string',
			'datetimelastimportstart' => 'nullable|string',
			'schedulerpolicyid' => 'nullable|integer',
			'defaultmaxwalltime' => 'required|integer',
		]);

		$row = Scheduler::findByHostname($request->input('hostname'));

		if ($row && $row->id)
		{
			return response()->json(['message' => trans('Entry already exists for `:hostname`', ['hostname' => $request->input('hostname')])], 415);
		}

		$row = Scheduler::create($request->all());

		return new JsonResource($row);
	}

	/**
	 * Read a scheduler
	 *
	 * @apiMethod GET
	 * @apiUri    /api/queues/schedulers/{id}
	 * @apiAuthorization  true
	 * @apiParameter {
	 *      "name":          "id",
	 *      "description":   "The ID of the queue walltime",
	 *      "type":          "integer",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @param   integer  $id
	 * @return  Response
	 */
	public function read($id)
	{
		$row = Scheduler::findOrFail($id);

		return new JsonResource($row);
	}

	/**
	 * Update a scheduler
	 *
	 * @apiMethod PUT
	 * @apiUri    /api/queues/schedulers/{id}
	 * @apiAuthorization  true
	 * @apiParameter {
	 *      "name":          "id",
	 *      "description":   "The ID of the queue walltime",
	 *      "type":          "integer",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @apiParameter {
	 *      "name":          "datetimestart",
	 *      "description":   "The start time",
	 *      "type":          "string",
	 *      "required":      false,
	 *      "default":       ""
	 * }
	 * @apiParameter {
	 *      "name":          "datetimestop",
	 *      "description":   "The stop time",
	 *      "type":          "string",
	 *      "required":      false,
	 *      "default":       ""
	 * }
	 * @param   integer  $id
	 * @param   Request  $request
	 * @return  Response
	 */
	public function update($id, Request $request)
	{
		$row = Scheduler::findOrFail($id);

		$request->validate([
			'datetimestart' => 'nullable|string',
			'datetimestop' => 'nullable|string',
			'walltime' => 'nullable|integer',
		]);

		$row->update($request->all());

		return new JsonResource($row);
	}

	/**
	 * Delete a scheduler
	 *
	 * @apiMethod DELETE
	 * @apiUri    /api/queues/schedulers/{id}
	 * @apiAuthorization  true
	 * @apiParameter {
	 *      "name":          "id",
	 *      "description":   "The ID of the queue walltime",
	 *      "type":          "integer",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @param   integer  $id
	 * @return  Response
	 */
	public function delete($id)
	{
		$row = Scheduler::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
		}

		return response()->json(null, 204);
	}
}
