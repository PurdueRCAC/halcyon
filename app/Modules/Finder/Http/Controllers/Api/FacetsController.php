<?php

namespace App\Modules\Finder\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Finder\Models\Facet;
use App\Modules\Finder\Models\ServiceFacet;

/**
 * Facets
 *
 * @apiUri    /finder/facets
 */
class FacetsController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /finder/facets
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "parentid",
	 * 		"description":   "Parent department ID",
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
	 * @return  ResourceCollection
	 */
	public function index(Request $request)
	{
		$filters = array(
			'state' => $request->input('state', 'published'),
			'search'   => $request->input('search', ''),
			'parent' => $request->input('parent', 0),
			// Paging
			'limit'    => $request->input('limit', config('list_limit', 20)),
			// Sorting
			'order'     => $request->input('order', Facet::$orderBy),
			'order_dir' => $request->input('order_dir', Facet::$orderDir)
		);

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Facet::$orderDir;
		}

		$query = Facet::query()
			->where('parent', '=', $filters['parent']);

		if ($filters['search'])
		{
			if (is_numeric($filters['search']))
			{
				$query->where($g . '.id', '=', $filters['search']);
			}
			else
			{
				$filters['search'] = strtolower((string)$filters['search']);

				$query->where(function ($where) use ($filters, $g)
				{
					$where->where($g . '.name', 'like', '%' . $filters['search'] . '%')
						->orWhere($g . '.description', 'like', '%' . $filters['search'] . '%');
				});
			}
		}

		if ($filters['state'] == 'published')
		{
			$query->where('status', '=', 1);
		}
		elseif ($filters['state'] == 'unpublished')
		{
			$query->where('status', '=', 0);
		}
		elseif ($filters['state'] == 'trashed')
		{
			$query->withTrashed();
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'])
			->appends(array_filter($filters));

		$rows->each(function ($row, $key)
		{
			$row->api = route('api.finder.facets.read', ['id' => $row->id]);
		});

		return new ResourceCollection($rows);
	}

	/**
	 * Create a new entry
	 *
	 * @apiMethod POST
	 * @apiUri    /finder/facets
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "name",
	 * 		"description":   "Facet name",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "parentid",
	 * 		"description":   "Parent department ID",
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
	 * 						"name": "Is your dataset restricted to U.S. persons only?",
	 * 						"control_type": "radio",
	 * 						"parent": 0,
	 * 						"weight": 1,
	 * 						"status": 1,
	 * 						"description": "<p>Export controlled data (ITAR/EAR) are restricted by law to U.S. persons only. This means that non-U.S. persons including international students or individuals from foreign countries are barred from accessing this data.</p>\r\n\r\n<p>Contact <a href=\"https://yourinstitution/research/regulatory-affairs/\";>Regulatory Affairs</a> for more information.</p>\r\n",
	 * 						"created_at": "2021-04-28T12:27:39.000000Z",
	 * 						"updated_at": "2021-04-28T12:27:39.000000Z",
	 * 						"deleted_at": null,
	 * 						"api": "https://example.org/api/finder/facets/11"
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"409": {
	 * 			"description": "Invalid data"
	 * 		}
	 * }
	 * @param  Request  $request
	 * @return JsonResponse|JsonResource
	 */
	public function create(Request $request)
	{
		$request->validate([
			'name' => 'required|string|max:150',
			'control_type' => 'required|string|max:150',
			'description' => 'nullable|string',
			'parent' => 'nullable|integer',
			'status' => 'nullable|integer',
		]);

		$row = new Facet;
		$row->control_type = $request->input('control_type');
		$row->name = $request->input('name');
		$row->parent = $request->input('parent', 0);
		$row->status = $request->input('status', 1);

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.create failed')], 500);
		}

		if ($request->has('choices'))
		{
			$choices = $request->input('choices', []);

			// Add new or update choices
			foreach ($choices as $choice)
			{
				$c = Facet::find($choice['id']);

				if (!$c || !$c->id)
				{
					$c = new Facet;
				}

				$c->parent = $row->id;
				$c->name = $choice['name'];
				$c->status = 1;
				$c->save();

				if (!empty($choice['matches']))
				{
					// Add new matches
					foreach ($choice['matches'] as $service_id)
					{
						$match = new ServiceFacet;
						$match->service_id = $service_id;
						$match->facet_id = $c->id;
						$match->save();
					}
				}
			}
		}

		$row->api = route('api.finder.facets.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /finder/facets/{id}
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
	 * 						"id": 1,
	 * 						"name": "Is your dataset restricted to U.S. persons only?",
	 * 						"control_type": "radio",
	 * 						"parent": 0,
	 * 						"weight": 1,
	 * 						"status": 1,
	 * 						"description": "<p>Export controlled data (ITAR/EAR) are restricted by law to U.S. persons only. This means that non-U.S. persons including international students or individuals from foreign countries are barred from accessing this data.</p>\r\n\r\n<p>Contact <a href=\"https://yourinstitution/research/regulatory-affairs/\";>Regulatory Affairs</a> for more information.</p>\r\n",
	 * 						"created_at": "2021-04-28T12:27:39.000000Z",
	 * 						"updated_at": "2021-04-28T12:27:39.000000Z",
	 * 						"deleted_at": null,
	 * 						"api": "https://example.org/api/finder/facets/11"
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
		$row = Facet::findOrFail($id);
		$row->api = route('api.finder.facets.read', ['id' => $row->id]);
		$row->choices;

		return new JsonResource($row);
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /finder/facets/{id}
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
	 * 		"description":   "Facet name",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "parentid",
	 * 		"description":   "Parent department ID",
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
	 * 						"name": "Is your dataset restricted to U.S. persons only?",
	 * 						"control_type": "radio",
	 * 						"parent": 0,
	 * 						"weight": 1,
	 * 						"status": 1,
	 * 						"description": "<p>Export controlled data (ITAR/EAR) are restricted by law to U.S. persons only. This means that non-U.S. persons including international students or individuals from foreign countries are barred from accessing this data.</p>\r\n\r\n<p>Contact <a href=\"https://yourinstitution/research/regulatory-affairs/\";>Regulatory Affairs</a> for more information.</p>\r\n",
	 * 						"created_at": "2021-04-28T12:27:39.000000Z",
	 * 						"updated_at": "2021-04-28T12:27:39.000000Z",
	 * 						"deleted_at": null,
	 * 						"api": "https://example.org/api/finder/facets/11"
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
	public function update(Request $request, $id)
	{
		$request->validate([
			'name' => 'nullable|string|max:150',
			'control_type' => 'nullable|string|max:150',
			'description' => 'nullable|string',
			'parent' => 'nullable|integer',
			'status' => 'nullable|integer',
			'choices' => 'nullable|array',
		]);

		$row = Facet::findOrFail($id);

		if ($request->has('parent'))
		{
			$row->parent = $request->input('parent');
		}

		if ($name = $request->input('name'))
		{
			$row->name = $name;
		}

		if ($request->has('description'))
		{
			$row->description = $request->input('description');
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.create failed')], 500);
		}

		$old = $row->facets;
		$current = array();

		if ($request->has('choices'))
		{
			$choices = $request->input('choices', []);

			// Add new or update choices
			foreach ($choices as $choice)
			{
				$c = Facet::find($choice['id']);

				if (!$c || !$c->id)
				{
					$c = new Facet;
				}

				$c->parent = $row->id;
				$c->name = $choice['name'];
				$c->status = 1;
				$c->save();

				$current[] = $c->id;

				$oldmatches = $c->services;
				$currentmatches = array();

				if (!empty($choice['matches']))
				{
					// Add new matches
					foreach ($choice['matches'] as $service_id)
					{
						$match = ServiceFacet::findByServiceAndFacet($service_id, $c->id);

						if (!$match || !$match->id)
						{
							$match = new ServiceFacet;
							$match->service_id = $service_id;
							$match->facet_id = $c->id;
							$match->save();
						}

						$currentmatches[] = $service_id;
					}
				}

				// Remove any previous matches not in the new dataset
				foreach ($oldmatches as $om)
				{
					if (!in_array($om->service_id, $currentmatches))
					{
						$om->delete();
					}
				}
			}

			// Remove any previous choices not in the new dataset
			foreach ($old as $o)
			{
				if (!in_array($o->id, $current))
				{
					$o->delete();
				}
			}
		}

		$row->api = route('api.finder.facets.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /finder/facets/{id}
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
	public function delete($id)
	{
		$row = Facet::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
		}

		return response()->json(null, 204);
	}
}
