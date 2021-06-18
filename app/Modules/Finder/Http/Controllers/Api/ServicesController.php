<?php

namespace App\Modules\Finder\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Finder\Models\Field;
use App\Modules\Finder\Models\Service;
use App\Modules\Finder\Models\ServiceField;

/**
 * Services
 *
 * @apiUri    /api/finder/services
 */
class ServicesController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /api/finder/services
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
	 * 			"default":   25
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
	 * 		"name":          "state",
	 * 		"description":   "Published or unpublished state or entries.",
	 * 		"type":          "string",
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
	 * 		"name":          "order",
	 * 		"description":   "Field to sort results by.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "title",
	 * 			"enum": [
	 * 				"id",
	 * 				"title",
	 * 				"summary",
	 * 				"created_at"
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
			'search'    => $request->input('search', ''),
			'state'    => $request->input('state', 'published'),
			// Paging
			'limit'     => $request->input('limit', config('list_limit', 20)),
			'page'     => $request->input('page', 1),
			// Sorting
			'order'     => $request->input('order', Service::$orderBy),
			'order_dir' => $request->input('order_dir', Service::$orderDir)
		);

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Service::$orderDir;
		}

		$query = Service::query();

		if ($filters['search'])
		{
			if (is_numeric($filters['search']))
			{
				$query->where('id', '=', $filters['search']);
			}
			else
			{
				$query->where(function($where) use ($filters)
				{
					$search = strtolower((string)$filters['search']);

					$where->where('title', 'like', '% ' . $search . '%')
						->orWhere('title', 'like', $search . '%');
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
			$row->api = route('api.finder.services.read', ['id' => $row->id]);
		});

		return new ResourceCollection($rows);
	}

	/**
	 * Create a new entry
	 *
	 * @apiMethod POST
	 * @apiUri    /api/finder/services
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "title",
	 * 		"description":   "Service title",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 255
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "summary",
	 * 		"description":   "Short description of the service",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 1200
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "status",
	 * 		"description":   "Published or unpublished status",
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
	 * @param   Request  $request
	 * @return Response
	 */
	public function create(Request $request)
	{
		$request->validate([
			'title' => 'required|string|max:255',
			'summary' => 'nullable|string|max:1200',
			'status' => 'nullable|integer'
		]);

		$row = new Service;
		$row->title = $request->input('title');
		$row->summary = $request->input('summary');
		if ($request->has('status'))
		{
			$row->status = $request->input('status');
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.create failed')], 500);
		}

		if ($request->has('fields'))
		{
			$sfields = $request->input('fields');

			foreach ($sfields as $name => $value)
			{
				$field = Field::findByName($name);

				if (!$field)
				{
					continue;
				}

				$fs = new ServiceField;
				$fs->service_id = $service->id;
				$fs->field_id = $field->id;
				$fs->value = $value;
				$fs->save();
			}
		}

		$row->api = route('api.finder.services.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /api/finder/services/{id}
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @param  integer  $id
	 * @return Response
	 */
	public function read($id)
	{
		$row = Service::findOrFail($id);
		$row->api = route('api.finder.services.read', ['id' => $row->id]);
		$row->fields;

		return new JsonResource($row);
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /api/finder/services/{id}
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "title",
	 * 		"description":   "Service title",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 255
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "summary",
	 * 		"description":   "Short description of the service",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 1200
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "status",
	 * 		"description":   "Published or unpublished status",
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
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "fields",
	 * 		"description":   "Fields for a service",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "array"
	 * 		}
	 * }
	 * @param   Request $request
	 * @param   integer $id
	 * @return  Response
	 */
	public function update(Request $request, $id)
	{
		$request->validate([
			'title' => 'nullable|string|max:255',
			'summary' => 'nullable|string|max:1200',
			'status' => 'nullable|integer',
			'fields' => 'nullable|array',
		]);

		$row = Service::findOrFail($id);

		if ($request->has('title'))
		{
			$row->title = $request->input('title');
		}

		if ($request->has('summary'))
		{
			$row->summary = $request->input('summary');
		}

		if ($request->has('status'))
		{
			$row->status = $request->input('status');
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.create failed')], 500);
		}

		if ($request->has('fields'))
		{
			$sfields = $request->input('fields');

			foreach ($sfields as $name => $value)
			{
				$field = Field::findByName($name);

				if (!$field)
				{
					continue;
				}

				$fs = ServiceField::findByServiceAndField($service->id, $field->id);

				if (!$fs || ! $fs->id)
				{
					$fs = new ServiceField;
				}

				$fs->service_id = $service->id;
				$fs->field_id = $field->id;
				$fs->value = $value;
				$fs->save();
			}
		}

		$row->api = route('api.finder.services.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /api/finder/services/{id}
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
	 * @param   integer  $id
	 * @return  Response
	 */
	public function delete($id)
	{
		$row = Service::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
		}

		return response()->json(null, 204);
	}
}
