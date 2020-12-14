<?php

namespace App\Modules\Issues\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Issues\Models\ToDo;

/**
 * Issue To-Dos
 *
 * @apiUri    /api/issues/todos
 */
class ToDosController extends Controller
{
	/**
	 * Display a listing of issues
	 *
	 * @apiMethod GET
	 * @apiUri    /api/issues/todos
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "limit",
	 * 		"description":   "Number of result per page.",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   25
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "page",
	 * 		"description":   "Number of where to start returning results.",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       1
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
	 * 		"default":       "datetimecreated",
	 * 		"allowedValues": "id, motd, datetimecreated, datetimeremoved"
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "order_dir",
	 * 		"description":   "Direction to sort results by.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "desc",
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "asc",
	 * 			"enum": [
	 * 				"asc",
	 * 				"desc"
	 * 			]
	 * 		}
	 * }
	 * @return Response
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			'id'        => null,
			'group'     => null,
			'start'     => null,
			'stop'      => null,
			'people'    => null,
			'resource'  => null,
			'notice'    => '*',
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			'order'     => ToDo::$orderBy,
			'order_dir' => ToDo::$orderDir,
		);

		foreach ($filters as $key => $default)
		{
			$val = $request->input($key);
			$val = !is_null($val) ? $val : $default;

			$filters[$key] = $val;
		}

		if (!in_array($filters['order'], ['id', 'name', 'recurringtimeperiodid', 'datetimecreated']))
		{
			$filters['order'] = ToDo::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = ToDo::$orderDir;
		}

		$query = ToDo::query()
			->withTrashed()
			->whereIsActive();

		if ($filters['search'])
		{
			$query->where(function($where)
			{
				$where->where('name', 'like', '%' . $filters['search'] . '%')
					->orWhere('description', 'like', '%' . $filters['search'] . '%');
			});
		}

		if ($filters['recurringtimeperiodid'])
		{
			$query->where('recurringtimeperiodid', '=', $filters['recurringtimeperiodid']);
		}

		if ($filters['id'])
		{
			$query->where('id', '=', $filters['id']);
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		return new ResourceCollection($rows);
	}

	/**
	 * Create a new issue
	 *
	 * @apiMethod POST
	 * @apiUri    /api/issues/todos
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "datetimecreated",
	 * 		"description":   "Timestamp (YYYY-MM-DD or YYYY-MM-DD hh:mm:ss) of the issue",
	 * 		"type":          "string",
	 * 		"required":      true,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "userid",
	 * 		"description":   "ID of the user creating the entry",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "issuetodo",
	 * 		"description":   "Is this a To-Do item?",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @param   Request  $request
	 * @return  Response
	 */
	public function create(Request $request)
	{
		$now = new Carbon();

		$request->validate([
			'name' => 'required|string|max:255',
			'description' => 'required|string|max:2000',
			'datetimecreated' => 'required|date',
			'userid' => 'nullable|integer',
			'recurringtimeperiodid' => 'nullable|integer',
		]);

		$row = new ToDo();
		$row->datetimecreated = $request->input('datetimecreated', $now->toDateTimeString());
		$row->name = $request->input('name');
		$row->description = $request->input('description');
		$row->userid = $request->input('userid', auth()->user() ? auth()->user()->id : 0);
		$row->recurringtimeperiodid = $request->input('recurringtimeperiodid', 0);

		if ($row->recurringtimeperiodid && !$row->timeperiod)
		{
			return response()->json(['message' => trans('invalid recurring time period')], 415);
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('messages.create failed')], 500);
		}

		return new ApiIssueResource($row);
	}

	/**
	 * Retrieve an issue
	 *
	 * @apiMethod GET
	 * @apiUri    /api/issues/todos/{id}
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @param  integer  $id
	 * @return Response
	 */
	public function read($id)
	{
		$row = ToDo::findOrFail((int)$id);

		return new JsonResource($row);
	}

	/**
	 * Update an issue
	 *
	 * @apiMethod PUT
	 * @apiUri    /api/issues/todos/{id}
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
	 * 		"name":          "datetimecreated",
	 * 		"description":   "Timestamp (YYYY-MM-DD or YYYY-MM-DD hh:mm:ss) of the issue",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "issuetodo",
	 * 		"description":   "Is this a To-Do item?",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @param   Request  $request
	 * @param   integer  $id
	 * @return  Response
	 */
	public function update(Request $request, $id)
	{
		$request->validate([
			'name' => 'nullable|string',
			'description' => 'nullable|string',
			'recurringtimeperiodid' => 'nullable|integer',
		]);

		$row = ToDo::findOrFail($id);
		$row->name = $request->input('name', $row->name);
		$row->description = $request->input('description', $row->description);
		$row->recurringtimeperiodid = $request->input('recurringtimeperiodid', $row->recurringtimeperiodid);

		if (!$row->name)
		{
			return response()->json(['message' =>  '`name` cannot be empty'], 415);
		}

		if ($row->recurringtimeperiodid && !$row->timeperiod)
		{
			return response()->json(['message' => trans('invalid recurring time period')], 415);
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('messages.update failed')], 500);
		}

		return new JsonResource($row);
	}

	/**
	 * Delete an issue
	 *
	 * @apiMethod DELETE
	 * @apiUri    /api/issues/todos/{id}
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
		$row = ToDo::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
		}

		return response()->json(null, 204);
	}
}
