<?php

namespace App\Modules\Software\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Modules\Software\Models\Type;
use App\Modules\Software\Models\Application;
use App\Modules\Software\Models\Version;
use App\Modules\Software\Models\VersionResource;
use Carbon\Carbon;

/**
 * Applications
 *
 * @apiUri    /software
 */
class ApplicationsController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /software
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"name":          "type_id",
	 * 		"description":   "Filter by application type ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   "0"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"name":          "resource_id",
	 * 		"description":   "Filter by resource ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   "0"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"name":          "state",
	 * 		"description":   "Filter by state, if permissions allow. Otherwise, only accepted value is published.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "published",
	 * 			"enum": [
	 * 				"published",
	 * 				"unpublished",
	 * 				"trashed",
	 * 				"all"
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"name":          "search",
	 * 		"description":   "A word or phrase to search for.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"name":          "limit",
	 * 		"description":   "Number of result per page.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   20
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"name":          "page",
	 * 		"description":   "Number of where to start returning results.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   1
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"name":          "order",
	 * 		"description":   "Field to sort results by.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "published_at",
	 * 			"enum": [
	 * 				"id",
	 * 				"title",
	 * 				"type_id",
	 * 				"author",
	 * 				"journal",
	 * 				"booktitle",
	 * 				"series",
	 * 				"state",
	 * 				"published_at"
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
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
	 * @param  Request  $request
	 * @return ResourceCollection
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			'state'     => 'published',
			'resource_id' => 0,
			'type_id'   => 0,
			// Paging
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			// Sorting
			'order'     => Application::$orderBy,
			'order_dir' => Application::$orderDir,
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->input($key, $default);
		}

		if (!in_array($filters['order'], ['id', 'title', 'type_id', 'state', 'alias']))
		{
			$filters['order'] = Application::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Application::$orderDir;
		}

		if (!auth()->user() || !auth()->user()->can('manage software'))
		{
			$filters['state'] = 'published';
		}

		// Get records
		$a = (new Application)->getTable();

		$query = Application::query()
			->select($a . '.*');

		if ($filters['state'] == 'published')
		{
			$query->where($a . '.state', '=', 1);
		}
		elseif ($filters['state'] == 'unpublished')
		{
			$query->where($a . '.state', '=', 0);
		}
		elseif ($filters['state'] == 'trashed')
		{
			$query->onlyTrashed();
		}
		elseif ($filters['state'] == 'all')
		{
			$query->withTrashed();
		}

		if ($filters['search'])
		{
			$query->whereSearch($filters['search']);
		}

		if ($filters['type_id'])
		{
			$query->where($a . '.type_id', '=', $filters['type_id']);
		}

		if ($filters['resource_id'])
		{
			$v = (new Version)->getTable();
			$r = (new VersionResource)->getTable();

			$query->join($v, $v . '.application_id', $a . '.id')
				->join($r, $r . '.version_id', $v . '.id')
				->where($r . '.resource_id', '=', $filters['resource_id'])
				->groupBy($a . '.id');
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page'])
			->appends(array_filter($filters))
			->each(function($item, $key)
			{
				$item->api = route('api.software.read', ['id' => $item->id]);
			});

		return new ResourceCollection($rows);
	}

	/**
	 * Create a new entry
	 *
	 * @apiMethod POST
	 * @apiUri    /software
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "type_id",
	 * 		"description":   "Type ID",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "title",
	 * 		"description":   "Title",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 255
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "alias",
	 * 		"description":   "Alias",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 255
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "description",
	 * 		"description":   "Short description",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "content",
	 * 		"description":   "Detailed info",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"201": {
	 * 			"description": "Successful entry creation",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": {
	 * 						"id": 2,
	 * 						"alias": "gcc",
	 * 						"title": "GCC",
	 * 						"description": "GCC compiler",
	 * 						"content": "<p>Detailed info here.</p>",
	 * 						"type_id": 1,
	 * 						"created_at": "2023-09-20 14:23:01",
	 * 						"updated_at": null,
	 * 						"deleted_at": null,
	 * 						"api": "https://example.org/api/software/2"
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"409": {
	 * 			"description": "Invalid data"
	 * 		}
	 * }
	 * @param  Request $request
	 * @return JsonResponse|JsonResource
	 */
	public function create(Request $request)
	{
		$rules = [
			'type_id' => 'required|integer|min:1',
			'title' => 'required|string|max:255',
			'alias' => 'nullable|string|max:255',
			'description' => 'nullable|string|max:500',
			'content' => 'nullable|string',
			'state' => 'nullable|integer',
			'access' => 'nullable|integer',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = new Application();
		$row->state = 1;
		$row->access = 1;
		foreach ($rules as $key => $rule)
		{
			if ($request->has($key))
			{
				$row->$key = $request->input($key);
			}
		}
		if (!$row->alias)
		{
			$row->alias = $row->title;
		}

		if (!$row->type)
		{
			return response()->json(['message' => trans('software::software.invalid.type')], 415);
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.save failed')], 500);
		}

		$row->api = route('api.software.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /software/{id}
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
	 * 						"id": 2,
	 * 						"alias": "gcc",
	 * 						"title": "GCC",
	 * 						"description": "GCC compiler",
	 * 						"content": "<p>Detailed info here.</p>",
	 * 						"type_id": 1,
	 * 						"created_at": "2023-09-20 14:23:01",
	 * 						"updated_at": null,
	 * 						"deleted_at": null,
	 * 						"api": "https://example.org/api/software/2"
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
	public function read(int $id)
	{
		$row = Application::findOrFail((int)$id);

		$row->api = route('api.software.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /software/{id}
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "type_id",
	 * 		"description":   "Type ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "title",
	 * 		"description":   "Title",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 255
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "alias",
	 * 		"description":   "Alias",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 255
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "description",
	 * 		"description":   "Short description",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "content",
	 * 		"description":   "Detailed info",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"202": {
	 * 			"description": "Successful entry modification",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": {
	 * 						"id": 2,
	 * 						"alias": "gcc",
	 * 						"title": "GCC",
	 * 						"description": "GCC compiler",
	 * 						"content": "<p>Detailed info here.</p>",
	 * 						"type_id": 1,
	 * 						"created_at": "2023-09-20 14:23:01",
	 * 						"updated_at": null,
	 * 						"deleted_at": null,
	 * 						"api": "https://example.org/api/software/2"
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
	 * @param   int $id
	 * @return  JsonResponse|JsonResource
	 */
	public function update(Request $request, int $id)
	{
		$rules = [
			'type_id' => 'nullable|integer|min:1',
			'title' => 'nullable|string|max:255',
			'alias' => 'nullable|string|max:255',
			'description' => 'nullable|string|max:500',
			'content' => 'nullable|string',
			'state' => 'nullable|integer',
			'access' => 'nullable|integer',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = Application::findOrFail($id);
		foreach ($rules as $key => $rule)
		{
			if ($request->has($key))
			{
				$row->$key = $request->input($key);
			}
		}
		if (!$row->alias)
		{
			$row->alias = $row->title;
		}

		if (!$row->type)
		{
			return response()->json(['message' => trans('software::software.invalid.type')], 415);
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.save failed')], 500);
		}

		$row->api = route('api.software.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /software/{id}
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
		$row = Application::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
		}

		return response()->json(null, 204);
	}
}
