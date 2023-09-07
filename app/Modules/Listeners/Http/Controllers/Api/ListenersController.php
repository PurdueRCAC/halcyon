<?php

namespace App\Modules\Listeners\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Modules\Listeners\Models\Listener;
use App\Modules\Users\Models\User;
use App\Halcyon\Access\Viewlevel;
use App\Modules\Listeners\Http\Resources\ListenerResource;
use App\Modules\Listeners\Http\Resources\ListenerResourceCollection;

/**
 * Listeners
 *
 * @apiUri    /listeners
 */
class ListenersController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /listeners
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "state",
	 * 		"description":   "Listener enabled/disabled state",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "published",
	 * 			"enum": [
	 * 				"published",
	 * 				"unpublished"
	 * 			]
	 * 		}
	 * }
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
	 * 			"default":   "folder",
	 * 			"enum": [
	 * 				"id",
	 * 				"name",
	 * 				"element",
	 * 				"folder",
	 * 				"state",
	 * 				"access",
	 * 				"ordering"
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
	 * @return  ListenerResourceCollection
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			'state'     => '',
			'access'    => 0,
			'folder'  => null,
			'enabled'    => null,
			// Pagination
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			'order'     => Listener::$orderBy,
			'order_dir' => Listener::$orderDir,
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->input($key, $default);
		}

		if (!in_array($filters['order'], ['id', 'title', 'position', 'state', 'access']))
		{
			$filters['order'] = Listener::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Listener::$orderDir;
		}

		$query = Listener::query()
			->where('type', '=', 'listener');

		$p = (new Listener)->getTable();
		$u = (new User)->getTable();
		$a = (new Viewlevel)->getTable();

		$query->select([$p . '.*', $u . '.name AS editor', $a . '.title AS access_level']);

		// Join over the users for the checked out user.
		$query
			->leftJoin($u, $u . '.id', $p . '.checked_out');

		// Join over the access groups.
		$query
			->leftJoin($a, $a . '.id', $p . '.access');

		// Filter by access level.
		if ($filters['access'])
		{
			$query->where($p . '.access', '=', (int) $filters['access']);
		}

		// Filter by published state
		if (is_numeric($filters['state']))
		{
			$query->where($p . '.enabled', '=', (int) $filters['state']);
		}
		elseif ($filters['state'] === '')
		{
			$query->whereIn($p . '.enabled', array(0, 1));
		}

		// Filter by folder.
		if ($filters['folder'])
		{
			$query->where($p . '.folder', '=', $filters['folder']);
		}

		// Filter by search in id
		if (!empty($filters['search']))
		{
			if (stripos($filters['search'], 'id:') === 0)
			{
				$query->where($p . '.extension_id', '=', (int) substr($filters['search'], 3));
			}
			else
			{
				$query->where(function($where) use ($filters)
				{
					$where->where($p . '.name', 'like', '%' . $filters['search'] . '%')
						->orWhere($p . '.element', 'like', '%' . $filters['search'] . '%');
				});
			}
		}

		if ($filters['order'] == 'name')
		{
			$query->orderBy('name', $filters['order_dir']);
			$query->orderBy('ordering', 'asc');
		}
		else if ($filters['order'] == 'ordering')
		{
			$query->orderBy('folder', 'asc');
			$query->orderBy('ordering', $filters['order_dir']);
			$query->orderBy('name', 'asc');
		}
		else
		{
			$query->orderBy($filters['order'], $filters['order_dir']);
			$query->orderBy('name', 'asc');
			$query->orderBy('ordering', 'asc');
		}

		$rows = $query
			->paginate($filters['limit'], ['*'], 'page', $filters['page'])
			->appends(array_filter($filters));

		return new ListenerResourceCollection($rows);
	}

	/**
	 * Create a new entry
	 *
	 * @apiMethod POST
	 * @apiUri    /listeners
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "name",
	 * 		"description":   "Name",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "element",
	 * 		"description":   "Element",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "folder",
	 * 		"description":   "Folder (listener group)",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "enabled",
	 * 		"description":   "Enabled",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "access",
	 * 		"description":   "Access",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   1
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "params",
	 * 		"description":   "List of params",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "array"
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"201": {
	 * 			"description": "Successful entry creation",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": {
	 * 						"id": 10042,
	 * 						"name": "Content - OpenGraph",
	 * 						"type": "listener",
	 * 						"element": "opengraph",
	 * 						"folder": "content",
	 * 						"client_id": 0,
	 * 						"enabled": 1,
	 * 						"access": 1,
	 * 						"protected": 1,
	 * 						"params": [],
	 * 						"checked_out": 61344,
	 * 						"checked_out_time": "2021-11-17T21:58:24.000000Z",
	 * 						"ordering": 11,
	 * 						"updated_at": null,
	 * 						"updated_by": 0,
	 * 						"editor": "Jane Doe",
	 * 						"access_level": "Public",
	 * 						"api": "https://example.org/api/listeners/10042"
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"409": {
	 * 			"description": "Invalid data"
	 * 		}
	 * }
	 * @param   Request  $request
	 * @return  ListenerResource|JsonResponse
	 */
	public function create(Request $request)
	{
		$rules = [
			'name' => 'required|string|max:100',
			'element' => 'required|string|max:100',
			'folder' => 'required|string|max:100',
			'client_id' => 'nullable|integer',
			'enabled' => 'nullable|integer',
			'access' => 'nullable|integer',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = new Listener($request->all());
		$row->client_id = $row->client_id ? 1 : 0;
		$row->type = 'listener';

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.save failed')], 500);
		}

		return new ListenerResource($row);
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /listeners/{id}
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
	 * 		"200": {
	 * 			"description": "Successful entry read",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": {
	 * 						"id": 10042,
	 * 						"name": "Content - OpenGraph",
	 * 						"type": "listener",
	 * 						"element": "opengraph",
	 * 						"folder": "content",
	 * 						"client_id": 0,
	 * 						"enabled": 1,
	 * 						"access": 1,
	 * 						"protected": 1,
	 * 						"params": [],
	 * 						"checked_out": 61344,
	 * 						"checked_out_time": "2021-11-17T21:58:24.000000Z",
	 * 						"ordering": 11,
	 * 						"updated_at": null,
	 * 						"updated_by": 0,
	 * 						"editor": "Jane Doe",
	 * 						"access_level": "Public",
	 * 						"api": "https://example.org/api/listeners/10042"
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		}
	 * }
	 * @param   int  $id
	 * @return  ListenerResource
	 */
	public function read(int $id)
	{
		$row = Listener::findOrFail($id);

		return new ListenerResource($row);
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /listeners/{id}
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
	 * 		"description":   "Name",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "element",
	 * 		"description":   "Element",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "folder",
	 * 		"description":   "Folder (listener group)",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
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
	 * 		"name":          "access",
	 * 		"description":   "Access",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "params",
	 * 		"description":   "List of params",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "array"
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"202": {
	 * 			"description": "Successful entry modification",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": {
	 * 						"id": 10042,
	 * 						"name": "Content - OpenGraph",
	 * 						"type": "listener",
	 * 						"element": "opengraph",
	 * 						"folder": "content",
	 * 						"client_id": 0,
	 * 						"enabled": 1,
	 * 						"access": 1,
	 * 						"protected": 1,
	 * 						"params": [],
	 * 						"checked_out": 61344,
	 * 						"checked_out_time": "2021-11-17T21:58:24.000000Z",
	 * 						"ordering": 11,
	 * 						"updated_at": null,
	 * 						"updated_by": 0,
	 * 						"editor": "Jane Doe",
	 * 						"access_level": "Public",
	 * 						"api": "https://example.org/api/listeners/10042"
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
	 * @param   Request $request
	 * @return  ListenerResource|JsonResponse
	 */
	public function update(Request $request, int $id)
	{
		$rules = [
			'name' => 'nullable|string|max:100',
			'element' => 'nullable|string|max:100',
			'folder' => 'nullable|string|max:100',
			'client_id' => 'nullable|integer',
			'enabled' => 'nullable|integer',
			'access' => 'nullable|integer',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = Listener::findOrFail($id);
		foreach (array_keys($rules) as $key)
		{
			if ($request->has($key))
			{
				$row->{$key} = $request->input($key);
			}
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.save failed')], 500);
		}

		return new ListenerResource($row);
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /listeners/{id}
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
	 * @return  JsonResponse
	 */
	public function delete(int $id)
	{
		$row = Listener::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
		}

		return response()->json(null, 204);
	}
}
