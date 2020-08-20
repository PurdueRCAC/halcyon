<?php

namespace App\Modules\Resources\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Resources\Entities\Type;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Types
 *
 * @apiUri    /api/resources/types
 */
class TypesController extends Controller
{
	/**
	 * Display a listing of resource types.
	 *
	 * @apiMethod GET
	 * @apiUri    /api/resources/types
	 * @apiParameter {
	 * 		"in":            "query",
	 *      "name":          "limit",
	 *      "description":   "Number of result to return.",
	 *      "type":          "integer",
	 *      "required":      false,
	 *      "default":       25
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 *      "name":          "page",
	 *      "description":   "Number of where to start returning results.",
	 *      "type":          "integer",
	 *      "required":      false,
	 *      "default":       0
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 *      "name":          "search",
	 *      "description":   "A word or phrase to search for.",
	 *      "type":          "string",
	 *      "required":      false,
	 *      "default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 *      "name":          "sort",
	 *      "description":   "Field to sort results by.",
	 *      "type":          "string",
	 *      "required":      false,
	 *      "default":       "created",
	 *      "allowedValues": "id, name, datetimecreated, datetimeremoved, parentid"
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 *      "name":          "sort_dir",
	 *      "description":   "Direction to sort results by.",
	 *      "type":          "string",
	 *      "required":      false,
	 *      "default":       "desc",
	 *      "allowedValues": "asc, desc"
	 * }
	 * @return Response
	 */
	public function index(Request $request)
	{
		$filters = array(
			'search'   => $request->input('search', ''),
			// Paging
			'limit'    => $request->input('limit', config('list_limit', 20)),
			//'start' => $request->input('limitstart', 0),
			// Sorting
			'sort'     => $request->input('sort', 'name'),
			'sort_dir' => $request->input('sort_dir', 'asc')
		);

		if (!in_array($filters['sort_dir'], ['asc', 'desc']))
		{
			$filters['sort_dir'] = 'asc';
		}

		$query = Type::query()
			->withCount('resources');

		if ($filters['search'])
		{
			$query->where('name', 'like', '%' . $filters['search'] . '%');
		}

		$rows = $query
			->orderBy($filters['sort'], $filters['sort_dir'])
			->paginate($filters['limit'])
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
	 * @apiUri    /api/resources/types
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "name",
	 *      "description":   "The name of the resource type",
	 *      "type":          "string",
	 *      "required":      true,
	 *      "default":       null
	 * }
	 * @apiResponse {
	 *     "data": {
	 *         "id": 3,
	 *         "name": "New type",
	 *         "resources_count": 34,
	 *         "api": "https://yourhost/api/resources/types/3"
	 *     }
	 * }
	 * @return Response
	 */
	public function create(Request $request)
	{
		$request->validate([
			'name' => 'required|string|max:20'
		]);

		$row = Type::create($request->all());

		if (!$row)
		{
			return response()->json(['message' => trans('messages.create failed')], 500);
		}

		$row->resources_count = $row->resources()->count();
		$row->api = route('api.resources.types.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Read a resource type
	 *
	 * @apiMethod GET
	 * @apiUri    /api/resources/types/{id}
	 * @apiParameter {
	 * 		"in":            "query",
	 *      "name":          "id",
	 *      "description":   "The ID of the resource type",
	 *      "type":          "integer",
	 *      "required":      true,
	 *      "default":       null
	 * }
	 * @apiResponse {
	 *     "data": {
	 *         "id": 1,
	 *         "name": "Compute",
	 *         "resources_count": 34,
	 *         "api": "https://yourhost/api/resources/types/1"
	 *     }
	 * }
	 * @return  Response
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
	 * @apiUri    /api/resources/types/{id}
	 * @apiParameter {
	 * 		"in":            "query",
	 *      "name":          "id",
	 *      "description":   "The ID of the resource type",
	 *      "type":          "integer",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "name",
	 *      "description":   "The name of the resource type",
	 *      "type":          "string",
	 *      "required":      true,
	 *      "default":       null
	 * }
	 * @apiResponse {
	 *     "data": {
	 *         "id": 3,
	 *         "name": "Updated type",
	 *         "resources_count": 34,
	 *         "api": "https://yourhost/api/resources/types/3"
	 *     }
	 * }
	 * @return  Response
	 */
	public function update($id, Request $request)
	{
		$request->validate([
			'name' => 'required|string|max:20'
		]);

		$row = Type::findOrFail($id);

		if (!$row->update($request->all()))
		{
			return response()->json(['message' => trans('messages.update failed')], 500);
		}

		$row->resources_count = $row->resources()->count();
		$row->api = route('api.resources.types.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Delete a resource type
	 *
	 * @apiMethod DELETE
	 * @apiUri    /api/resources/types/{id}
	 * @apiParameter {
	 * 		"in":            "query",
	 *      "name":          "id",
	 *      "description":   "The ID of the resource type",
	 *      "type":          "integer",
	 *      "required":      true,
	 *      "default":       null
	 * }
	 * @return  Response
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
