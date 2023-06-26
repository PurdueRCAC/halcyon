<?php

namespace App\Modules\Resources\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Modules\Resources\Models\Type;
use App\Modules\Resources\Models\FacetType;
use App\Modules\Resources\Models\FacetOption;

/**
 * Types
 *
 * @apiUri    /resources/types
 */
class TypesController extends Controller
{
	/**
	 * Display a listing of resource types.
	 *
	 * @apiMethod GET
	 * @apiUri    /resources/types
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "limit",
	 * 		"description":   "Number of result to return.",
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
	 * 			"default":   "name",
	 * 			"enum": [
	 * 				"id",
	 * 				"name",
	 * 				"description"
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
	 * @param  Request  $request
	 * @return ResourceCollection
	 */
	public function index(Request $request)
	{
		$filters = array(
			'search'    => $request->input('search', ''),
			// Paging
			'limit'     => $request->input('limit', config('list_limit', 20)),
			'page'      => $request->input('page', 1),
			// Sorting
			'order'     => $request->input('order', 'name'),
			'order_dir' => $request->input('order_dir', 'asc')
		);

		if (!in_array($filters['order'], ['id', 'name', 'description']))
		{
			$filters['order'] = 'name';
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = 'asc';
		}

		$query = Type::query()
			->withCount('resources');

		if ($filters['search'])
		{
			$query->where('name', 'like', '%' . $filters['search'] . '%');
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page'])
			->appends(array_filter($filters));

		$rows->each(function ($item, $key)
		{
			$item->api = route('api.resources.types.read', ['id' => $item->id]);
		});

		return new ResourceCollection($rows);
	}

	/**
	 * Create a resource type
	 *
	 * @apiMethod POST
	 * @apiUri    /resources/types
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "name",
	 * 		"description":   "The name of the resource type",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 20
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "description",
	 * 		"description":   "A short description of the resource type",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 2000
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "facets",
	 * 		"description":   "A list of facet fields",
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
	 * 						"id": 1,
	 * 						"name": "New type",
	 * 						"resources_count": 0,
	 * 						"api": "https://yourhost/api/resources/types/1"
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"401": {
	 * 			"description": "Unauthorized"
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
		$rules = [
			'name'        => 'required|string|max:20',
			'description' => 'nullable|string|max:2000',
			'facets'      => 'nullable|array',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = Type::create($request->all());

		if (!$row)
		{
			return response()->json(['message' => trans('global.messages.create failed')], 500);
		}

		if ($request->has('facets'))
		{
			$facets = $request->input('facets', []);

			// Collect old fields
			$oldFields = array();
			foreach ($row->facetTypes as $oldField)
			{
				$oldFields[$oldField->id] = $oldField;
			}

			foreach ($facets->fields as $i => $element)
			{
				$field = null;

				$fid = (isset($element->field_id) ? $element->field_id : 0);

				if ($fid && isset($oldFields[$fid]))
				{
					$field = $oldFields[$fid];

					// Remove found fields from the list
					// Anything remaining will be deleted
					unset($oldFields[$fid]);
				}

				$field = $field ?: new FacetType;
				$field->fill([
					'type_id'       => $row->id,
					'type'          => (string) $element->field_type,
					'label'         => (string) $element->label,
					'name'          => (string) $element->name,
					'description'   => (isset($element->field_options->description) ? (string) $element->field_options->description : null),
					//'required'     => (isset($element->required) ? (int) $element->required : 0),
					'ordering'      => ($i + 1),
					'min'           => (isset($element->field_options->min) ? (int) $element->field_options->min : 0),
					'max'           => (isset($element->field_options->max) ? (int) $element->field_options->max : 0),
					'default_value' => (isset($element->field_options->value) ? (string) $element->field_options->value : null),
					'placeholder'   => (isset($element->field_options->placeholder) ? (string) $element->field_options->placeholder : null)
				]);

				if ($field->type == 'dropdown')
				{
					$field->type = 'select';
				}
				if ($field->type == 'paragraph')
				{
					$field->type = 'textarea';
				}

				if (!$field->save())
				{
					continue;
				}

				// Collect old options
				$oldOptions = array();
				foreach ($field->options as $oldOption)
				{
					$oldOptions[$oldOption->id] = $oldOption;
				}

				// Does this field have any set options?
				if (isset($element->field_options->options))
				{
					foreach ($element->field_options->options as $k => $opt)
					{
						$option = null;

						$oid = (isset($opt->field_id) ? $opt->field_id : 0);

						if ($oid && isset($oldOptions[$oid]))
						{
							$option = $oldOptions[$oid];

							// Remove found options from the list
							// Anything remaining will be deleted
							unset($oldOptions[$oid]);
						}

						$option = $option ?: new FacetOption;
						$option->fill([
							'facet_type_id' => $field->id,
							'label'      => (string) $opt->label,
							'value'      => (isset($opt->value)   ? (string) $opt->value : null),
							'checked'    => (isset($opt->checked) ? (int) $opt->checked : 0),
							'ordering'   => ($k + 1),
						]);

						if (!$option->save())
						{
							continue;
						}
					}
				}

				// Remove any options not in the incoming list
				foreach ($oldOptions as $option)
				{
					$option->delete();
				}
			}

			// Remove any fields not in the incoming list
			foreach ($oldFields as $field)
			{
				$field->delete();
			}
		}

		$row->resources_count = $row->resources()->count();
		$row->api = route('api.resources.types.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Read a resource type
	 *
	 * @apiMethod GET
	 * @apiUri    /resources/types/{id}
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
	 * 						"name": "Compute",
	 * 						"resources_count": 34,
	 * 						"api": "https://yourhost/api/resources/types/1"
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		}
	 * }
	 * @param   int  $id
	 * @return  JsonResource
	 */
	public function read($id)
	{
		$row = Type::findOrFail($id);
		$row->resources_count = $row->resources()->count();
		$row->api = route('api.resources.types.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Update a resource type
	 *
	 * @apiMethod PUT
	 * @apiUri    /resources/types/{id}
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
	 * 		"description":   "The name of the resource type",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 20
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "description",
	 * 		"description":   "A short description of the resource type",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 2000
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "facets",
	 * 		"description":   "A list of facet fields",
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
	 * 						"id": 1,
	 * 						"name": "Updated type",
	 * 						"resources_count": 34,
	 * 						"api": "https://yourhost/api/resources/types/1"
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
	 * @param   int  $id
	 * @param   Request  $request
	 * @return  JsonResponse|JsonResource
	 */
	public function update($id, Request $request)
	{
		$rules = [
			'name'        => 'required|string|max:20',
			'description' => 'nullable|string|max:2000',
			'facets'      => 'nullable|array',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = Type::findOrFail($id);

		if (!$row->update($request->all()))
		{
			return response()->json(['message' => trans('global.messages.update failed')], 500);
		}

		if ($request->has('facets'))
		{
			$facets = $request->input('facets', []);

			// Collect old fields
			$oldFields = array();
			foreach ($row->facetTypes as $oldField)
			{
				$oldFields[$oldField->id] = $oldField;
			}

			foreach ($facets->fields as $i => $element)
			{
				$field = null;

				$fid = (isset($element->field_id) ? $element->field_id : 0);

				if ($fid && isset($oldFields[$fid]))
				{
					$field = $oldFields[$fid];

					// Remove found fields from the list
					// Anything remaining will be deleted
					unset($oldFields[$fid]);
				}

				$field = $field ?: new FacetType;
				$field->fill([
					'type_id'       => $row->id,
					'type'          => (string) $element->field_type,
					'label'         => (string) $element->label,
					'name'          => (string) $element->name,
					'description'   => (isset($element->field_options->description) ? (string) $element->field_options->description : null),
					//'required'     => (isset($element->required) ? (int) $element->required : 0),
					'ordering'      => ($i + 1),
					'min'           => (isset($element->field_options->min) ? (int) $element->field_options->min : 0),
					'max'           => (isset($element->field_options->max) ? (int) $element->field_options->max : 0),
					'default_value' => (isset($element->field_options->value) ? (string) $element->field_options->value : null),
					'placeholder'   => (isset($element->field_options->placeholder) ? (string) $element->field_options->placeholder : null)
				]);

				if ($field->type == 'dropdown')
				{
					$field->type = 'select';
				}
				if ($field->type == 'paragraph')
				{
					$field->type = 'textarea';
				}

				if (!$field->save())
				{
					continue;
				}

				// Collect old options
				$oldOptions = array();
				foreach ($field->options as $oldOption)
				{
					$oldOptions[$oldOption->id] = $oldOption;
				}

				// Does this field have any set options?
				if (isset($element->field_options->options))
				{
					foreach ($element->field_options->options as $k => $opt)
					{
						$option = null;

						$oid = (isset($opt->field_id) ? $opt->field_id : 0);

						if ($oid && isset($oldOptions[$oid]))
						{
							$option = $oldOptions[$oid];

							// Remove found options from the list
							// Anything remaining will be deleted
							unset($oldOptions[$oid]);
						}

						$option = $option ?: new FacetOption;
						$option->fill([
							'facet_type_id' => $field->id,
							'label'      => (string) $opt->label,
							'value'      => (isset($opt->value)   ? (string) $opt->value : null),
							'checked'    => (isset($opt->checked) ? (int) $opt->checked : 0),
							'ordering'   => ($k + 1),
						]);

						if (!$option->save())
						{
							continue;
						}
					}
				}

				// Remove any options not in the incoming list
				foreach ($oldOptions as $option)
				{
					$option->delete();
				}
			}

			// Remove any fields not in the incoming list
			foreach ($oldFields as $field)
			{
				$field->delete();
			}
		}

		$row->resources_count = $row->resources()->count();
		$row->api = route('api.resources.types.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Delete a resource type
	 *
	 * @apiMethod DELETE
	 * @apiUri    /resources/types/{id}
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
		$row = Type::findOrFail($id);

		if ($row->resources()->count())
		{
			return response()->json(['message' => trans('resources::resources.errors.type has resources', ['count' => $row->resources()->count()])], 415);
		}

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
		}

		return response()->json(null, 204);
	}
}
