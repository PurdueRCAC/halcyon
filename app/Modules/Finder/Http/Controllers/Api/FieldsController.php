<?php

namespace App\Modules\Finder\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Finder\Models\Field;

/**
 * Fields
 *
 * @apiUri    /finder/fields
 */
class FieldsController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /finder/fields
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "status",
	 * 		"description":   "Published status",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "limit",
	 * 		"description":   "Number of result per page.",
	 * 		"type":          "integer",
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
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "label",
	 * 			"enum": [
	 * 				"id",
	 * 				"name",
	 * 				"label",
	 * 				"created_at",
	 * 				"deleted_at"
	 * 			]
	 * 		}
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
	 * @param   Request  $request
	 * @return  Response
	 */
	public function index(Request $request)
	{
		$filters = array(
			'search'   => $request->input('search', ''),
			'status'   => $request->input('status', 1),
			// Paging
			'limit'    => $request->input('limit', config('list_limit', 20)),
			'page'     => $request->input('page', 1),
			// Sorting
			'order'     => $request->input('order', Field::$orderBy),
			'order_dir' => $request->input('order_dir', Field::$orderDir)
		);

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Field::$orderDir;
		}

		$query = Field::query();

		if ($filters['search'])
		{
			$filters['search'] = strtolower((string)$filters['search']);

			$query->where('name', 'like', '%' . $filters['search'] . '%');
		}

		if ($filters['status'])
		{
			$query->where('status', '=', $filters['status']);
		}

		$rows = $query
			->withCount('services')
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'])
			->appends(array_filter($filters));

		$rows->each(function ($row, $key)
		{
			$row->api = route('api.finder.fields.read', ['id' => $row->id]);
		});

		return new ResourceCollection($rows);
	}

	/**
	 * Create a new entry
	 *
	 * @apiMethod POST
	 * @apiUri    /finder/fields
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "name",
	 * 		"description":   "Field name",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 150
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "label",
	 * 		"description":   "Field label",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 150
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "weight",
	 * 		"description":   "Field weight",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "status",
	 * 		"description":   "Published state of the record",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   1,
	 * 			"enum": [
	 * 				0,
	 * 				1
	 * 			]
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"201": {
	 * 			"description": "Successful entry creation",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": {
	 * 						"id": 4,
	 * 						"name": "cost",
	 * 						"label": "Cost",
	 * 						"weight": 3,
	 * 						"status": 1,
	 * 						"created_at": "2021-04-28T11:34:01.000000Z",
	 * 						"updated_at": "2021-04-28T11:34:01.000000Z",
	 * 						"deleted_at": null,
	 * 						"services_count": 15,
	 * 						"api": "https://example.org/api/finder/fields/4"
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
	 * @return Response
	 */
	public function create(Request $request)
	{
		$request->validate([
			'label' => 'required|string|max:150',
			'name' => 'nullable|string|max:150',
			'status' => 'nullable|integer',
			'weight' => 'nullable|integer'
		]);

		$row = new Field;
		$row->label = $request->input('label');
		$row->name = $request->input('name');
		$row->status = $request->input('status', 1);

		if ($request->has('weight'))
		{
			$row->weight = $request->input('weight');
		}

		if (!$row->name)
		{
			$row->name = $row->label;
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.create failed')], 500);
		}

		$row->api = route('api.finder.fields.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /finder/fields/{id}
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
	 * 						"id": 4,
	 * 						"name": "cost",
	 * 						"label": "Cost",
	 * 						"weight": 3,
	 * 						"status": 1,
	 * 						"created_at": "2021-04-28T11:34:01.000000Z",
	 * 						"updated_at": "2021-04-28T11:34:01.000000Z",
	 * 						"deleted_at": null,
	 * 						"services_count": 15,
	 * 						"api": "https://example.org/api/finder/fields/4"
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		}
	 * }
	 * @param  int  $id
	 * @return Response
	 */
	public function read($id)
	{
		$row = Field::findOrFail($id);
		$row->api = route('api.finder.fields.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /finder/fields/{id}
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "name",
	 * 		"description":   "Field name",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 150
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "label",
	 * 		"description":   "Field label",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 150
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "weight",
	 * 		"description":   "Field weight",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "status",
	 * 		"description":   "Published state of the record",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   1,
	 * 			"enum": [
	 * 				0,
	 * 				1
	 * 			]
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"202": {
	 * 			"description": "Successful entry modification",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": {
	 * 						"id": 4,
	 * 						"name": "cost",
	 * 						"label": "Cost",
	 * 						"weight": 3,
	 * 						"status": 1,
	 * 						"created_at": "2021-04-28T11:34:01.000000Z",
	 * 						"updated_at": "2021-04-28T11:34:01.000000Z",
	 * 						"deleted_at": null,
	 * 						"services_count": 15,
	 * 						"api": "https://example.org/api/finder/fields/4"
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
	 * @return  Response
	 */
	public function update(Request $request, $id)
	{
		$request->validate([
			'label' => 'nullable|string|max:150',
			'name' => 'nullable|string|max:150',
			'status' => 'nullable|integer',
			'weight' => 'nullable|integer'
		]);

		$row = Field::findOrFail($id);

		if ($request->has('label'))
		{
			$row->label = $request->input('label');
		}

		if ($request->has('name'))
		{
			$row->name = $request->input('name');
		}

		if ($request->has('weight'))
		{
			$row->weight = $request->input('weight');
		}

		if ($request->has('status'))
		{
			$row->status = $request->input('status');
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.create failed')], 500);
		}

		$row->api = route('api.finder.fields.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /finder/fields/{id}
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
		$row = Field::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
		}

		return response()->json(null, 204);
	}
}
