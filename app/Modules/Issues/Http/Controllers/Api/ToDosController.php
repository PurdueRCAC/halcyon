<?php

namespace App\Modules\Issues\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Issues\Models\ToDo;

/**
 * Issue To-Dos
 * 
 * Checklist items that can have a recurrence such as daily, weekly, etc.
 * 
 * @apiUri    /issues/todos
 */
class ToDosController extends Controller
{
	/**
	 * Display a listing of to-dos
	 *
	 * @apiMethod GET
	 * @apiUri    /issues/todos
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "limit",
	 * 		"description":   "Number of result per page.",
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
	 * 		"name":          "search",
	 * 		"description":   "A word or phrase to search for.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
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
	 * 				"userid",
	 * 				"datetimecreated",
	 * 				"datetimeremoved",
	 * 				"recurringtimeperiodid"
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
	 * @return ResourceCollection
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			'id'        => null,
			'recurringtimeperiodid' => null,
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

		$query = ToDo::query();

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

		$rows->each(function($item, $key)
		{
			$item->api = route('api.issues.todos.read', ['id' => $item->id]);
		});

		return new ResourceCollection($rows);
	}

	/**
	 * Create a new to-do
	 *
	 * @apiMethod POST
	 * @apiUri    /issues/todos
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "name",
	 * 		"description":   "Name of the To-Do item",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 255
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "description",
	 * 		"description":   "Description of the To-Do item",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 2000
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "recurringtimeperiodid",
	 * 		"description":   "Recurring timeperiod ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "userid",
	 * 		"description":   "User ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"201": {
	 * 			"description": "Successful entry creation",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": {
	 * 						"id": 1,
	 * 						"userid": 1234,
	 * 						"name": "Check for emails for SSL certs expiring",
	 * 						"description": "",
	 * 						"recurringtimeperiodid": 1,
	 * 						"datetimecreated": "2021-10-25T20:24:30.000000Z",
	 * 						"datetimeremoved": null,
	 * 						"api": "https://example.org/api/issues/todos/1"
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		},
	 * 		"409": {
	 * 			"description": "Invalid data"
	 * 		}
	 * }
	 * @param   Request  $request
	 * @return  JsonResource
	 */
	public function create(Request $request)
	{
		$now = new Carbon();

		$rules = [
			'name' => 'required|string|max:255',
			'description' => 'required|string|max:2000',
			'userid' => 'nullable|integer',
			'recurringtimeperiodid' => 'nullable|integer',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

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
			return response()->json(['message' => trans('global.messages.create failed')], 500);
		}

		$row->api = route('api.issues.todos.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Retrieve a to-do
	 *
	 * @apiMethod GET
	 * @apiUri    /issues/todos/{id}
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
	 * 			"description": "Successful entry creation",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": {
	 * 						"id": 1,
	 * 						"userid": 1234,
	 * 						"name": "Check for emails for SSL certs expiring",
	 * 						"description": "",
	 * 						"recurringtimeperiodid": 1,
	 * 						"datetimecreated": "2021-10-25T20:24:30.000000Z",
	 * 						"datetimeremoved": null,
	 * 						"api": "https://example.org/api/issues/todos/1"
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		}
	 * }
	 * @param  int  $id
	 * @return JsonResource
	 */
	public function read($id)
	{
		$row = ToDo::findOrFail((int)$id);

		$row->api = route('api.issues.todos.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Update a to-do
	 *
	 * @apiMethod PUT
	 * @apiUri    /issues/todos/{id}
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
	 * 		"description":   "Name of the To-Do item",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 255
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "description",
	 * 		"description":   "Description of the To-Do item",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 2000
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "recurringtimeperiodid",
	 * 		"description":   "Recurring timeperiod ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"202": {
	 * 			"description": "Successful entry modification",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": {
	 * 						"id": 1,
	 * 						"userid": 1234,
	 * 						"name": "Check for emails for SSL certs expiring",
	 * 						"description": "",
	 * 						"recurringtimeperiodid": 1,
	 * 						"datetimecreated": "2021-10-25T20:24:30.000000Z",
	 * 						"datetimeremoved": null,
	 * 						"api": "https://example.org/api/issues/todos/1"
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		},
	 * 		"409": {
	 * 			"description": "Invalid data"
	 * 		}
	 * }
	 * @param   Request  $request
	 * @param   int  $id
	 * @return  JsonResource
	 */
	public function update(Request $request, $id)
	{
		$rules = [
			'name' => 'nullable|string|max:255',
			'description' => 'nullable|string|max:2000',
			'recurringtimeperiodid' => 'nullable|integer',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

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
			return response()->json(['message' => trans('global.messages.update failed')], 500);
		}

		$row->api = route('api.issues.todos.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Delete a to-do
	 *
	 * @apiMethod DELETE
	 * @apiUri    /issues/todos/{id}
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
	 * @param   int  $id
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
