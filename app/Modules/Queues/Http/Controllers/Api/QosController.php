<?php

namespace App\Modules\Queues\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Modules\Queues\Models\Qos;

/**
 * Quality of Service
 *
 * @apiUri    /queues/qos
 */
class QosController extends Controller
{
	/**
	 * Display a listing of QoS
	 *
	 * @apiMethod GET
	 * @apiUri    /queues/qos
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
	 * 			"default":   "name",
	 * 			"enum": [
	 * 				"id",
	 * 				"name",
	 * 				"schedulerid",
	 * 				"datetimestart",
	 * 				"datetimestop"
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
			'priority'    => $request->input('priority'),
			'search'      => $request->input('search'),
			// Paging
			'limit'       => $request->input('limit', config('list_limit', 20)),
			'page'        => $request->input('page', 1),
			// Sorting
			'order'       => $request->input('order', 'name'),
			'order_dir'   => $request->input('order_dir', 'desc')
		);

		if (!in_array($filters['order'], ['id', 'name', 'description', 'priority']))
		{
			$filters['order'] = 'name';
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = 'asc';
		}

		$query = Qos::query();

		if ($filters['search'])
		{
			$query->where('name', 'like', '%' . $filters['search'] . '%');
		}

		if ($filters['priority'])
		{
			$query->where('priority', '=', $filters['priority']);
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page'])
			->appends(array_filter($filters));

		return new ResourceCollection($rows);
	}

	/**
	 * Create a QoS
	 *
	 * @apiMethod POST
	 * @apiUri    /queues/qos
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "name",
	 * 		"description":   "Entry name",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 255
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "description",
	 * 		"description":   "Entry description",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "priority",
	 * 		"description":   "The act of 'stopping' one or more 'low-priority' jobs to let a 'high-priority' job run.",
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
	 * @param   Request  $request
	 * @return Response
	 */
	public function create(Request $request)
	{
		$rules = [
			'name' => 'required|string|max:255',
			'description' => 'nullable|string',
			'max_jobs_pa' => 'nullable|integer',
			'max_jobs_per_user' => 'nullable|integer',
			'max_jobs_accrue_pa' => 'nullable|integer',
			'max_jobs_accrue_pu' => 'nullable|integer',
			'min_prio_thresh' => 'nullable|integer',
			'max_submit_jobs_pa' => 'nullable|integer',
			'max_submit_jobs_per_user' => 'nullable|integer',
			'max_tres_pa' => 'nullable|string',
			'max_tres_pj' => 'nullable|string',
			'max_tres_pn' => 'nullable|string',
			'max_tres_pu' => 'nullable|string',
			'max_tres_mins_pj' => 'nullable|integer',
			'max_tres_run_mins_pa' => 'nullable|integer',
			'max_tres_run_mins_pu' => 'nullable|integer',
			'min_tres_pj' => 'nullable|string',
			'max_wall_duration_per_job' => 'nullable|integer',
			'grp_jobs' => 'nullable|integer',
			'grp_jobs_accrue' => 'nullable|integer',
			'grp_submit_jobs' => 'nullable|integer',
			'grp_tres' => 'nullable|string',
			'grp_tres_mins' => 'nullable|integer',
			'grp_tres_run_mins' => 'nullable|integer',
			'grp_wall' => 'nullable|integer',
			'preempt' => 'nullable|string',
			'preempt_mode' => 'nullable|integer',
			'preempt_exempt_time' => 'nullable|integer',
			'priority' => 'nullable|integer',
			'usage_factor' => 'nullable|string',
			'usage_thres' => 'nullable|string',
			'limit_factor' => 'nullable|string',
			'grace_time' => 'nullable|integer',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		//$row = Qos::create($request->all());
		$row = new Qos;
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
	 * Read a QoS
	 *
	 * @apiMethod GET
	 * @apiUri    /queues/qos/{id}
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
		$row = Qos::findOrFail($id);

		return new JsonResource($row);
	}

	/**
	 * Update a queue scheduler policy
	 *
	 * @apiMethod PUT
	 * @apiUri    /queues/qos/{id}
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
	 * 		"name":          "name",
	 * 		"description":   "Entry name",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 255
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "description",
	 * 		"description":   "Entry description",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "priority",
	 * 		"description":   "The act of 'stopping' one or more 'low-priority' jobs to let a 'high-priority' job run.",
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
			'name' => 'nullable|string|max:255',
			'description' => 'nullable|string',
			'max_jobs_pa' => 'nullable|integer',
			'max_jobs_per_user' => 'nullable|integer',
			'max_jobs_accrue_pa' => 'nullable|integer',
			'max_jobs_accrue_pu' => 'nullable|integer',
			'min_prio_thresh' => 'nullable|integer',
			'max_submit_jobs_pa' => 'nullable|integer',
			'max_submit_jobs_per_user' => 'nullable|integer',
			'max_tres_pa' => 'nullable|string',
			'max_tres_pj' => 'nullable|string',
			'max_tres_pn' => 'nullable|string',
			'max_tres_pu' => 'nullable|string',
			'max_tres_mins_pj' => 'nullable|integer',
			'max_tres_run_mins_pa' => 'nullable|integer',
			'max_tres_run_mins_pu' => 'nullable|integer',
			'min_tres_pj' => 'nullable|string',
			'max_wall_duration_per_job' => 'nullable|integer',
			'grp_jobs' => 'nullable|integer',
			'grp_jobs_accrue' => 'nullable|integer',
			'grp_submit_jobs' => 'nullable|integer',
			'grp_tres' => 'nullable|string',
			'grp_tres_mins' => 'nullable|integer',
			'grp_tres_run_mins' => 'nullable|integer',
			'grp_wall' => 'nullable|integer',
			'preempt' => 'nullable|string',
			'preempt_mode' => 'nullable|integer',
			'preempt_exempt_time' => 'nullable|integer',
			'priority' => 'nullable|integer',
			'usage_factor' => 'nullable|string',
			'usage_thres' => 'nullable|string',
			'limit_factor' => 'nullable|string',
			'grace_time' => 'nullable|integer',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = Qos::findOrFail($id);

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
	 * Delete a QoS
	 *
	 * @apiMethod DELETE
	 * @apiUri    /queues/qos/{id}
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
		$row = Qos::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
		}

		return response()->json(null, 204);
	}
}
