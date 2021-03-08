<?php

namespace App\Modules\News\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Modules\News\Models\Type;

/**
 * Types
 *
 * @apiUri    /api/news/types
 */
class TypesController extends Controller
{
	/**
	 * Display a listing of news article types
	 *
	 * @apiMethod GET
	 * @apiUri    /api/news/types
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "tagresources",
	 * 		"description":   "Filter by types that allow articles to tag resources",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "location",
	 * 		"description":   "Filter by types that allow articles to set location",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "future",
	 * 		"description":   "Filter by types that allow articles to set future",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "ongoing",
	 * 		"description":   "Filter by types that allow articles to set ongoing",
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
	 * 		"name":          "order",
	 * 		"description":   "Field to sort results by.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "datetimecreated",
	 * 			"enum": [
	 * 				"id",
	 * 				"motd",
	 * 				"datetimecreated",
	 * 				"datetimeremoved"
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
	 * @apiResponse {
	 * 		"200": {
	 * 			"description": "Successful entries lookup",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": {
	 * 						"data": [{
	 * 						"id":            "1",
	 * 						"name":          "Examples",
	 * 						"tagresources":  0,
	 * 						"tagusers":      1,
	 * 						"location":      1,
	 * 						"future":        1,
	 * 						"calendar":      1,
	 * 						"url":           1,
	 * 						"api":           "https://example.com/api/news/types/1"
	 * 					},{
	 * 						"id":            "2",
	 * 						"name":          "Outages and Maintenance",
	 * 						"tagresources":  1,
	 * 						"tagusers":      0,
	 * 						"location":      0,
	 * 						"future":        1,
	 * 						"calendar":      1,
	 * 						"url":           0,
	 * 						"api":           "https://example.com/api/news/types/2"
	 * 					}],
	 * 					"links": {
	 * 					        "first": "https://example.com/api/news/types?limit=20&order=name&order_dir=asc&page=1",
	 * 					        "last": "https://example.com/api/news/types?limit=20&order=name&order_dir=asc&page=1",
	 * 					        "prev": null,
	 * 					        "next": null
	 * 					    },
	 * 					    "meta": {
	 * 					        "current_page": 1,
	 * 					        "from": 1,
	 * 					        "last_page": 1,
	 * 					        "path": "https://example.com/api/news/types",
	 * 					        "per_page": 20,
	 * 					        "to": 2,
	 * 					        "total": 2
	 * 					    }
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"415": {
	 * 			"description": "Invalid data"
	 * 		}
	 * }
	 * @param  Request  $request
	 * @return Response
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			'tagresources' => null,
			'location'  => null,
			'future'    => null,
			'ongoing'   => null,
			'limit'     => config('list_limit', 20),
			'order'     => Type::$orderBy,
			'order_dir' => Type::$orderDir,
		);

		foreach ($filters as $key => $default)
		{
			$val = $request->input($key);
			$val = !is_null($val) ? $val : $default;

			$filters[$key] = $val;
		}

		if (!in_array($filters['order'], ['id', 'name']))
		{
			$filters['order'] = Type::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Type::$orderDir;
		}

		$query = Type::query();

		if ($filters['search'])
		{
			$query->where('name', 'like', '%' . $filters['search'] . '%');
		}

		if (!is_null($filters['tagresources']))
		{
			$query->where('tagresources', '=', $filters['tagresources']);
		}

		if (!is_null($filters['location']))
		{
			$query->where('location', '=', $filters['location']);
		}

		if (!is_null($filters['future']))
		{
			$query->where('future', '=', $filters['future']);
		}

		if (!is_null($filters['ongoing']))
		{
			$query->where('ongoing', '=', $filters['ongoing']);
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'])
			->appends(array_filter($filters));

		$rows->each(function ($item, $key)
		{
			$item->api = route('api.news.types.read', ['id' => $item->id]);
		});

		return new ResourceCollection($rows);
	}

	/**
	 * Create a news article type
	 *
	 * @apiMethod POST
	 * @apiUri    /api/news/types
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "comment",
	 * 		"description":   "The comment being made",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "name",
	 * 		"description":   "The name of the type",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 32
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "tagresources",
	 * 		"description":   "Allow articles to tag resources",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0,
	 * 			"enum": [
	 * 				0,
	 * 				1
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "tagusers",
	 * 		"description":   "Allow articles to tag users",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0,
	 * 			"enum": [
	 * 				0,
	 * 				1
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "location",
	 * 		"description":   "Allow articles to set location",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0,
	 * 			"enum": [
	 * 				0,
	 * 				1
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "future",
	 * 		"description":   "Allow articles to set future",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0,
	 * 			"enum": [
	 * 				0,
	 * 				1
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "ongoing",
	 * 		"description":   "Allow articles to set ongoing",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0,
	 * 			"enum": [
	 * 				0,
	 * 				1
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "url",
	 * 		"description":   "A URL associated with the news article",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0,
	 * 			"enum": [
	 * 				0,
	 * 				1
	 * 			]
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"200": {
	 * 			"description": "Successful entry creation",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": {
	 * 						"id":            "1",
	 * 						"name":          "Examples",
	 * 						"tagresources":  0,
	 * 						"tagusers":      1,
	 * 						"location":      1,
	 * 						"future":        1,
	 * 						"calendar":      1,
	 * 						"url":           1,
	 * 						"api":           "https://example.com/api/news/types/1"
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"415": {
	 * 			"description": "Invalid data"
	 * 		}
	 * }
	 * @param   Request  $request
	 * @return  Response
	 */
	public function create(Request $request)
	{
		$request->validate([
			'name' => 'required|string|max:32',
			'tagresources' => 'nullable|boolean',
			'location' => 'nullable|boolean',
			'future' => 'nullable|boolean',
			'ongoing' => 'nullable|boolean',
			'tagusers' => 'nullable|boolean',
			'calendar' => 'nullable|boolean',
			'url' => 'nullable|url',
		]);

		$row = new Type($request->all());

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.creation failed')], 500);
		}

		$row->api = route('api.news.types.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Read a news article type
	 *
	 * @apiMethod GET
	 * @apiUri    /api/news/types/{id}
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
	 * 						"id":            "1",
	 * 						"name":          "Examples",
	 * 						"tagresources":  0,
	 * 						"tagusers":      1,
	 * 						"location":      1,
	 * 						"future":        1,
	 * 						"calendar":      1,
	 * 						"url":           1,
	 * 						"api":           "https://example.com/api/news/types/1"
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		}
	 * }
	 * @param  integer  $id
	 * @return Response
	 */
	public function read($id)
	{
		$row = Type::findOrFail((int)$id);

		$row->api = route('api.news.types.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Update a news article type
	 *
	 * @apiMethod PUT
	 * @apiUri    /api/news/types/{id}
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
	 * 		"description":   "The name of the type",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 32
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "tagresources",
	 * 		"description":   "Allow articles to tag resources",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"enum": [
	 * 				0,
	 * 				1
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "tagusers",
	 * 		"description":   "Allow articles to tag users",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"enum": [
	 * 				0,
	 * 				1
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "location",
	 * 		"description":   "Allow articles to set location",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"enum": [
	 * 				0,
	 * 				1
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "future",
	 * 		"description":   "Allow articles to set future",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"enum": [
	 * 				0,
	 * 				1
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "ongoing",
	 * 		"description":   "Allow articles to set ongoing",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"enum": [
	 * 				0,
	 * 				1
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "url",
	 * 		"description":   "A URL associated with the news article",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"enum": [
	 * 				0,
	 * 				1
	 * 			]
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"200": {
	 * 			"description": "Successful entry modification",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": {
	 * 						"id":            "1",
	 * 						"name":          "Examples",
	 * 						"tagresources":  0,
	 * 						"tagusers":      1,
	 * 						"location":      1,
	 * 						"future":        1,
	 * 						"calendar":      1,
	 * 						"url":           1,
	 * 						"api":           "https://example.com/api/news/types/1"
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		}
	 * }
	 * @param   Request  $request
	 * @param   integer  $id
	 * @return  Response
	 */
	public function update(Request $request, $id)
	{
		$request->validate([
			'name' => 'nullable|string|max:32',
			'tagresources' => 'nullable|boolean',
			'location' => 'nullable|boolean',
			'future' => 'nullable|boolean',
			'ongoing' => 'nullable|boolean',
			'tagusers' => 'nullable|boolean',
			'calendar' => 'nullable|boolean',
			'url' => 'nullable|url',
		]);

		$row = Type::findOrFail($id);
		$row->fill($request->all());

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.creation failed')], 500);
		}

		$row->api = route('api.news.types.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Delete a news article type
	 *
	 * @apiMethod DELETE
	 * @apiUri    /api/news/types/{id}
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
		$row = Type::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $row->id])]);
		}

		return response()->json(null, 204);
	}
}
