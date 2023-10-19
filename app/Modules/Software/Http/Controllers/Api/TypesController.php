<?php

namespace App\Modules\Software\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Modules\Software\Models\Type;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Publication types
 *
 * @apiUri    /software/types
 */
class TypesController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /software/types
	 * @apiAuthorization  true
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
	 * 			"default":   "name",
	 * 			"enum": [
	 * 				"id",
	 * 				"name",
	 * 				"alias"
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
			// Paging
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			// Sorting
			'order'     => Type::$orderBy,
			'order_dir' => Type::$orderDir,
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->input($key, $default);
		}

		if (!in_array($filters['order'], ['id', 'name', 'alias']))
		{
			$filters['order'] = Type::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Type::$orderDir;
		}

		// Get records
		$query = Type::query();

		if ($filters['search'])
		{
			$query->whereSearch($filters['search']);
		}

		// Get records
		$rows = $query
			->withCount('applications')
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page'])
			->appends(array_filter($filters));

		$rows->each(function($row, $key)
		{
			$row->api = route('api.software.types.read', ['id' => $row->id]);
		});

		return new ResourceCollection($rows);
	}

	/**
	 * Create a new entry
	 *
	 * @apiMethod POST
	 * @apiUri    /software/types
	 * @apiAuthorization  true
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
	 * @apiResponse {
	 * 		"201": {
	 * 			"description": "Successful entry creation",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": {
	 * 						"id": 9,
	 * 						"title": "Journal",
	 * 						"alias": "journal",
	 * 						"api": "https://example.org/api/software/types/9"
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
		$validator = Validator::make($request->all(), [
			'title'  => 'required|string|max:255',
			'alias' => 'nullable|string|max:255'
		]);

		if ($validator->fails()) //!$request->validated())
		{
			return response()->json(['message' => $validator->messages()->first()], 409);
		}

		$row = new Type;
		$row->title = $request->input('title');
		if ($request->has('alias'))
		{
			$row->alias = $request->input('alias');
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.save failed')], 500);
		}

		$row->api = route('api.software.types.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /software/types/{id}
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
	 * 						"id": 9,
	 * 						"title": "Journal",
	 * 						"alias": "journal",
	 * 						"api": "https://example.org/api/software/types/9"
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		}
	 * }
	 * @param  int $id
	 * @return JsonResource
	 */
	public function read(int $id)
	{
		$row = Type::findOrFail($id);

		$row->api = route('api.software.types.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /software/types/{id}
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
	 * 		"name":          "title",
	 * 		"description":   "Title",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 50
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "alias",
	 * 		"description":   "Alias",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 50
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"202": {
	 * 			"description": "Successful entry modification",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": {
	 * 						"id": 9,
	 * 						"name": "Journal",
	 * 						"alias": "journal",
	 * 						"api": "https://example.org/api/software/types/9"
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
		$validator = Validator::make($request->all(), [
			'title'  => 'nullable|string|max:255',
			'alias' => 'nullable|string|max:255'
		]);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()->first()], 409);
		}

		$row = Type::findOrFail($id);
		if ($request->has('title'))
		{
			$row->title = $request->input('title');
		}
		if ($request->has('alias'))
		{
			$row->alias = $request->input('alias');
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('pages::messages.page failed')], 500);
		}

		$row->api = route('api.software.types.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /software/types/{id}
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
		$row = Type::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
		}

		return response()->json(null, 204);
	}
}
