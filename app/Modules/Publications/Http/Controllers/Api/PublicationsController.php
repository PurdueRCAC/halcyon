<?php

namespace App\Modules\Publications\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Modules\Publications\Models\Type;
use App\Modules\Publications\Models\Publication;
use Carbon\Carbon;

/**
 * Publications
 *
 * @apiUri    /publications
 */
class PublicationsController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /publications
	 * @apiParameter {
	 * 		"name":          "client_id",
	 * 		"description":   "Client (admin = 1|site = 0) ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "search",
	 * 		"description":   "A word or phrase to search for.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       ""
	 * }
	 * @apiParameter {
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
	 * 		"name":          "page",
	 * 		"description":   "Number of where to start returning results.",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       1
	 * }
	 * @apiParameter {
	 * 		"name":          "order",
	 * 		"description":   "Field to sort results by.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "datetimecreated",
	 * 		"allowedValues": "id, motd, datetimecreated, datetimeremoved"
	 * }
	 * @apiParameter {
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
	 * @param  Request  $request
	 * @return Response
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			'state'     => 'published',
			'year'      => '*',
			'type'      => 0,
			// Paging
			'limit'     => config('list_limit', 20),
			// Sorting
			'order'     => Publication::$orderBy,
			'order_dir' => Publication::$orderDir,
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->input($key, $default);
		}

		if (!in_array($filters['order'], ['id', 'title', 'type_id', 'author', 'journal', 'booktitle', 'series', 'published_at', 'state']))
		{
			$filters['order'] = Publication::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Publication::$orderDir;
		}

		// Get records
		$query = Publication::query();

		if ($filters['search'])
		{
			if (is_numeric($filters['search']))
			{
				$query->where('id', '=', $filters['search']);
			}
			else
			{
				$filters['search'] = strtolower((string)$filters['search']);

				$query->where(function ($where) use ($filters)
				{
					$where->where('author', 'like', '%' . $filters['search'] . '%')
						->orWhere('title', 'like', '%' . $filters['search'] . '%');
				});
			}
		}

		if ($filters['state'] == 'published')
		{
			$query->where('state', '=', 1);
		}
		elseif ($filters['state'] == 'unpublished')
		{
			$query->where('state', '=', 0);
		}
		elseif ($filters['state'] == 'trashed')
		{
			$query->onlyTrashed();
		}

		if ($filters['type'] && $filters['type'] != '*')
		{
			$query->where('type_id', '=', $filters['type']);
		}

		if ($filters['year'] && $filters['year'] != '*')
		{
			$query->where('published_at', '>', $filters['year'] . '-01-01 00:00:00')
				->where('published_at', '<', Carbon::parse($filters['year'])->modify('+1 year')->format('Y') . '-01-01 00:00:00');
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'])
			->appends(array_filter($filters))
			->each(function($item, $key)
			{
				$item->api = route('api.publications.read', ['id' => $item->id]);
			});

		return new ResourceCollection($rows);
	}

	/**
	 * Create a new entry
	 *
	 * @apiMethod POST
	 * @apiUri    /publications
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "title",
	 * 		"description":   "Menu title",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "description",
	 * 		"description":   "A description of the menu",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "client_id",
	 * 		"description":   "Client (admin = 1|site = 0) ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "menutype",
	 * 		"description":   "A short alias for the menu. If none provided, one will be generated from the title.",
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
	 * 						"menutype": "about",
	 * 						"title": "About",
	 * 						"description": "About Side Menu",
	 * 						"client_id": 0,
	 * 						"created_at": null,
	 * 						"updated_at": null,
	 * 						"deleted_at": null,
	 * 						"items_count": 12,
	 * 						"api": "https://example.org/api/publications/2"
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"409": {
	 * 			"description": "Invalid data"
	 * 		}
	 * }
	 * @return Response
	 */
	public function create(Request $request)
	{
		$rules = [
			'type_id' => 'required|integer|min:1',
			'title' => 'required|string|max:500',
			'author' => 'nullable|string|max:3000',
			'editor' => 'nullable|string|max:3000',
			'url' => 'nullable|string|max:2083',
			'series' => 'nullable|string|max:255',
			'booktitle' => 'nullable|string|max:1000',
			'edition' => 'nullable|string|max:100',
			'chapter' => 'nullable|string|max:40',
			'issuetitle' => 'nullable|string|max:255',
			'journal' => 'nullable|string|max:255',
			'issue' => 'nullable|string|max:40',
			'volume' => 'nullable|string|max:40',
			'number' => 'nullable|string|max:40',
			'pages' => 'nullable|string|max:40',
			'publisher' => 'nullable|string|max:500',
			'address' => 'nullable|string|max:300',
			'institution' => 'nullable|string|max:500',
			'organization' => 'nullable|string|max:500',
			'school' => 'nullable|string|max:200',
			'crossref' => 'nullable|string|max:100',
			'isbn' => 'nullable|string|max:50',
			'doi' => 'nullable|string|max:255',
			'note' => 'nullable|string|max:2000',
			'state' => 'nullable|integer',
			'published_at' => 'nullable|datetime',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = new Publication();
		foreach ($rules as $key => $rule)
		{
			if ($request->has($key))
			{
				$row->$key = $request->input($key);
			}
		}
		if ($request->has('year'))
		{
			$row->published_at = $request->input('year') . '-' . $request->input('month', '01') . ' -01 00:00:00';
		}

		if (!$row->type)
		{
			return response()->json(['message' => trans('publications::publications.invalid.type')], 415);
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.save failed')], 500);
		}

		$row->api = route('api.publications.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /publications/{id}
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
	 * 						"menutype": "about",
	 * 						"title": "About",
	 * 						"description": "About Side Menu",
	 * 						"client_id": 0,
	 * 						"created_at": null,
	 * 						"updated_at": null,
	 * 						"deleted_at": null,
	 * 						"items_count": 12,
	 * 						"api": "https://example.org/api/publications/2"
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
	public function read(int $id)
	{
		$row = Publication::findOrFail((int)$id);

		$row->api = route('api.publications.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /publications/{id}
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
	 * 		"description":   "Menu title",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "description",
	 * 		"description":   "A description of the menu",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "client_id",
	 * 		"description":   "Client (admin = 1|site = 0) ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "menutype",
	 * 		"description":   "A short alias for the menu. If none provided, one will be generated from the title.",
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
	 * 						"menutype": "about",
	 * 						"title": "About",
	 * 						"description": "About Side Menu",
	 * 						"client_id": 0,
	 * 						"created_at": null,
	 * 						"updated_at": null,
	 * 						"deleted_at": null,
	 * 						"items_count": 12,
	 * 						"api": "https://example.org/api/publications/2"
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
	 * @param   integer $id
	 * @return  Response
	 */
	public function update(Request $request, int $id)
	{
		$rules = [
			'type_id' => 'nullable|integer|min:1',
			'title' => 'nullable|string|max:500',
			'author' => 'nullable|string|max:3000',
			'editor' => 'nullable|string|max:3000',
			'url' => 'nullable|string|max:2083',
			'series' => 'nullable|string|max:255',
			'booktitle' => 'nullable|string|max:1000',
			'edition' => 'nullable|string|max:100',
			'chapter' => 'nullable|string|max:40',
			'issuetitle' => 'nullable|string|max:255',
			'journal' => 'nullable|string|max:255',
			'issue' => 'nullable|string|max:40',
			'volume' => 'nullable|string|max:40',
			'number' => 'nullable|string|max:40',
			'pages' => 'nullable|string|max:40',
			'publisher' => 'nullable|string|max:500',
			'address' => 'nullable|string|max:300',
			'institution' => 'nullable|string|max:500',
			'organization' => 'nullable|string|max:500',
			'school' => 'nullable|string|max:200',
			'crossref' => 'nullable|string|max:100',
			'isbn' => 'nullable|string|max:50',
			'doi' => 'nullable|string|max:255',
			'note' => 'nullable|string|max:2000',
			'state' => 'nullable|integer',
			'published_at' => 'nullable|datetime',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = Publication::findOrFail($id);
		foreach ($rules as $key => $rule)
		{
			if ($request->has($key))
			{
				$row->$key = $request->input($key);
			}
		}
		if ($request->has('year'))
		{
			$row->published_at = $request->input('year') . '-' . $request->input('month', '01') . ' -01 00:00:00';
		}

		if (!$row->type)
		{
			return response()->json(['message' => trans('publications::publications.invalid.type')], 415);
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.save failed')], 500);
		}

		$row->api = route('api.publications.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /publications/{id}
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
	public function delete(int $id)
	{
		$row = Publication::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
		}

		return response()->json(null, 204);
	}
}
