<?php

namespace App\Modules\News\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Validator;
use App\Modules\News\Models\Association;
use Carbon\Carbon;

/**
 * Article Registrants
 *
 * @apiUri    /news/associations
 */
class AssociationsController extends Controller
{
	/**
	 * Display a listing of news article types
	 *
	 * @apiMethod GET
	 * @apiUri    /news/associations
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
	 * @return ResourceCollection
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = array(
			'assoctype' => null,
			'associd'   => null,
			'newsid'    => null,
			'limit'     => config('list_limit', 20),
			'order'     => 'datetimecreated',
			'order_dir' => 'asc',
		);

		foreach ($filters as $key => $default)
		{
			$val = $request->input($key);
			$val = !is_null($val) ? $val : $default;

			$filters[$key] = $val;
		}

		if (!in_array($filters['order'], ['id', 'newsid', 'assoctype', 'associd', 'datetimecreated']))
		{
			$filters['order'] = 'datetimecreated';
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = 'asc';
		}

		$query = Association::query();

		if ($filters['assoctype'])
		{
			$query->where('assoctype', '=', $filters['assoctype']);
		}

		if ($filters['associd'])
		{
			$query->where('associd', '=', $filters['associd']);
		}

		if ($filters['newsid'])
		{
			$query->where('newsid', '=', $filters['newsid']);
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'])
			->appends(array_filter($filters));

		$rows->each(function ($item, $key)
		{
			$item->api = route('api.news.associations.read', ['id' => $item->id]);
		});

		return new ResourceCollection($rows);
	}

	/**
	 * Create a news associations
	 *
	 * @apiMethod POST
	 * @apiUri    /news/associations
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "associd",
	 * 		"description":   "The association ID",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "assoctype",
	 * 		"description":   "The association type",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 255
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "newsid",
	 * 		"description":   "The news article ID",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "comment",
	 * 		"description":   "Comment / notes",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 2000
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"200": {
	 * 			"description": "Successful entry creation",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": {
	 * 						"id":        1,
	 * 						"associd":   1234,
	 * 						"assoctype": "user",
	 * 						"newsid":    1,
	 * 						"comment":   "Examples",
	 * 						"api":       "https://example.com/api/news/associations/1"
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"415": {
	 * 			"description": "Invalid data"
	 * 		}
	 * }
	 * @param   Request  $request
	 * @return  Response|JsonResource
	 */
	public function create(Request $request)
	{
		$rules = [
			'associd'   => 'required|integer',
			'assoctype' => 'required|string|max:255',
			'newsid'    => 'required|integer',
			'comment'   => 'nullable|string|max:2000',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = new Association;
		$row->newsid = $request->input('newsid');
		$row->assoctype = $request->input('assoctype');
		$row->associd = $request->input('associd');
		$row->comment = $request->input('comment');

		if (!$row->article)
		{
			return response()->json(['message' => trans('Invalid news ID')], 415);
		}

		if ($row->assoctype == 'user')
		{
			$now = Carbon::now();

			$endregistration = Carbon::parse($row->article->datetimenews);

			if ($end_reg = config('module.news.end_registration'))
			{
				$endregistration = $endregistration->modify($end_reg);

				if ($now->getTimestamp() >= $endregistration->getTimestamp())
				{
					return response()->json(['message' => trans('news::news.registration is closed')], 403);
				}
			}
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.creation failed')], 500);
		}

		$row->api = route('api.news.associations.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Read a news associations
	 *
	 * @apiMethod GET
	 * @apiUri    /news/associations/{id}
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
	 * 						"id":        1,
	 * 						"associd":   1234,
	 * 						"assoctype": "user",
	 * 						"newsid":    1,
	 * 						"comment":   "Examples",
	 * 						"api":       "https://example.com/api/news/associations/1"
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		}
	 * }
	 * @param  integer  $id
	 * @return JsonResource
	 */
	public function read($id)
	{
		$row = Association::findOrFail((int)$id);

		$row->api = route('api.news.associations.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Update a news association
	 *
	 * @apiMethod PUT
	 * @apiUri    /news/associations/{id}
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
	 * 		"name":          "associd",
	 * 		"description":   "The association ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "assoctype",
	 * 		"description":   "The association type",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 255
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "newsid",
	 * 		"description":   "The news article ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "comment",
	 * 		"description":   "Comment / notes",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 2000
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"200": {
	 * 			"description": "Successful entry modification",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": {
	 * 						"id":        1,
	 * 						"associd":   1234,
	 * 						"assoctype": "user",
	 * 						"newsid":    1,
	 * 						"comment":   "Examples",
	 * 						"api":       "https://example.com/api/news/associations/1"
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
	 * @return  Response|JsonResource
	 */
	public function update(Request $request, $id)
	{
		$rules = [
			'associd'   => 'nullable|integer',
			'assoctype' => 'nullable|string|max:255',
			'newsid'    => 'nullable|integer',
			'comment'   => 'nullable|string|max:2000',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = Association::findOrFail($id);

		if ($request->has('newsid'))
		{
			$row->newsid = $request->input('newsid');

			if (!$row->article)
			{
				return response()->json(['message' => trans('Invalid news ID')], 415);
			}
		}
		if ($request->has('assoctype'))
		{
			$row->assoctype = $request->input('assoctype');
		}
		if ($request->has('associd'))
		{
			$row->associd = $request->input('associd');
		}
		if ($request->has('comment'))
		{
			$row->comment = $request->input('comment');
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.creation failed')], 500);
		}

		$row->api = route('api.news.associations.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Delete a news associations
	 *
	 * @apiMethod DELETE
	 * @apiUri    /news/associations/{id}
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
		$row = Association::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $row->id])]);
		}

		return response()->json(null, 204);
	}
}
