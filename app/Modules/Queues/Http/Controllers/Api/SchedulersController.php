<?php

namespace App\Modules\Queues\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Modules\Queues\Models\Scheduler;

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
	 * 		"in":            "query",
	 * 		"name":          "queuesubresourceid",
	 * 		"description":   "Filter by queue subresource ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "batchsystem",
	 * 		"description":   "Filter by batchsystem ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "schedulerpolicyid",
	 * 		"description":   "Filter by scheduler policy ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
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
	 * 			"default":   "id",
	 * 			"enum": [
	 * 				"id",
	 * 				"queuesubresourceid",
	 * 				"batchsystem",
	 * 				"schedulerpolicyid"
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
	 * 			"default":   "asc",
	 * 			"enum": [
	 * 				"asc",
	 * 				"desc"
	 * 			]
	 * 		}
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
	 * 		"name":          "queuesubresourceid",
	 * 		"description":   "Queue subresource ID",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "hostname",
	 * 		"description":   "Hostname",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 64
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "batchsystem",
	 * 		"description":   "Batchsystem ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "schedulerpolocyid",
	 * 		"description":   "Scheduler Policy ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "defaultmaxwalltime",
	 * 		"description":   "Default max wall time",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "datetimedraindown",
	 * 		"description":   "Drain timestamp",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":       "string",
	 * 			"format":    "date-time",
	 * 			"example":   "2021-01-30T09:30:00Z"
	 * 		}
	 * }
	 * @param   Request  $request
	 * @return Response
	 */
	public function create(Request $request)
	{
		$rules = [
			'hostname'                => 'required|string|max:64',
			'queuesubresourceid'      => 'required|integer|min:1',
			'batchsystem'             => 'nullable|integer|min:1',
			'datetimedraindown'       => 'nullable|string',
			'datetimelastimportstart' => 'nullable|string',
			'schedulerpolicyid'       => 'required|integer',
			'defaultmaxwalltime'      => 'required|integer',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = Scheduler::findByHostname($request->input('hostname'));

		if ($row && $row->id)
		{
			return response()->json(['message' => trans('queues::queues.error.entry already exists for hostname', ['hostname' => $request->input('hostname')])], 415);
		}

		//$row = Scheduler::create($request->all());
		$row = new Scheduler;
		foreach ($rules as $key => $rule)
		{
			if ($request->has($key))
			{
				$row->{$key} = $request->input($key);
			}
		}
		$row->save();

		return new JsonResource($row);
	}

	/**
	 * Read a scheduler
	 *
	 * @apiMethod GET
	 * @apiUri    /api/queues/schedulers/{id}
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
	 * 		"name":          "queuesubresourceid",
	 * 		"description":   "Queue subresource ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "hostname",
	 * 		"description":   "Hostname",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 64
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "batchsystem",
	 * 		"description":   "Batchsystem ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "schedulerpolocyid",
	 * 		"description":   "Scheduler Policy ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "defaultmaxwalltime",
	 * 		"description":   "Default max wall time in seconds",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "datetimedraindown",
	 * 		"description":   "Drain timestamp",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date-time",
	 * 			"example":   "2021-01-30T09:30:00Z"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "datetimelastimportstart",
	 * 		"description":   "Last import started timestamp",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date-time",
	 * 			"example":   "2021-01-30T09:30:00Z"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "datetimelastimportstop",
	 * 		"description":   "Last import finished timestamp",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date-time",
	 * 			"example":   "2021-01-30T09:30:00Z"
	 * 		}
	 * }
	 * @param   integer  $id
	 * @param   Request  $request
	 * @return  Response
	 */
	public function update($id, Request $request)
	{
		$row = Scheduler::findOrFail($id);

		$rules = [
			'hostname'                => 'nullable|string|max:64',
			'queuesubresourceid'      => 'nullable|integer|min:1',
			'batchsystem'             => 'nullable|integer|min:1',
			'datetimedraindown'       => 'nullable|date',
			'datetimelastimportstart' => 'nullable|date',
			'datetimelastimportstop'  => 'nullable|date',
			'schedulerpolicyid'       => 'nullable|integer',
			'defaultmaxwalltime'      => 'nullable|integer',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		//$row->update($request->all());
		foreach ($rules as $key => $rule)
		{
			if ($request->has($key))
			{
				$row->{$key} = $request->input($key);
			}
		}
		$row->save();

		return new JsonResource($row);
	}

	/**
	 * Delete a scheduler
	 *
	 * @apiMethod DELETE
	 * @apiUri    /api/queues/schedulers/{id}
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
		$row = Scheduler::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
		}

		return response()->json(null, 204);
	}
}
