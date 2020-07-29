<?php

namespace App\Modules\Resources\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Modules\Resources\Entities\Batchsystem;

class BatchsystemsController extends Controller
{
	/**
	 * Display a listing of batchsystems.
	 *
	 * @apiMethod GET
	 * @apiUri    /resources/batchsystems
	 * @apiParameter {
	 *      "name":          "limit",
	 *      "description":   "Number of result to return.",
	 *      "type":          "integer",
	 *      "required":      false,
	 *      "default":       25
	 * }
	 * @apiParameter {
	 *      "name":          "page",
	 *      "description":   "Number of where to start returning results.",
	 *      "type":          "integer",
	 *      "required":      false,
	 *      "default":       0
	 * }
	 * @apiParameter {
	 *      "name":          "search",
	 *      "description":   "A word or phrase to search for.",
	 *      "type":          "string",
	 *      "required":      false,
	 *      "default":       ""
	 * }
	 * @apiParameter {
	 *      "name":          "sort",
	 *      "description":   "Field to sort results by.",
	 *      "type":          "string",
	 *      "required":      false,
	 *      "default":       "created",
	 *      "allowedValues": "id, name, datetimecreated, datetimeremoved, parentid"
	 * }
	 * @apiParameter {
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

		$query = Batchsystem::query();

		if ($filters['search'])
		{
			$query->where('name', 'like', '%' . $filters['search'] . '%');
		}

		$rows = $query
			->withCount('resources')
			->orderBy($filters['sort'], $filters['sort_dir'])
			->paginate($filters['limit'])
			->appends(array_filter($filters));

		$rows->each(function ($item, $key)
		{
			$item->api = route('api.resources.batchsystems.read', ['id' => $item->id]);
		});

		return new ResourceCollection($rows);
	}

	/**
	 * Create a batchsystem
	 *
	 * @apiMethod POST
	 * @apiUri    /resources/batchsystems
	 * @apiParameter {
	 *      "name":          "name",
	 *      "description":   "The name of the batchsystem",
	 *      "type":          "string",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @return Response
	 */
	public function create(Request $request)
	{
		$request->validate([
			'name' => 'required|max:16'
		]);

		$row = Batchsystem::create($request->all());

		if (!$row)
		{
			return response()->json(['message' => trans('messages.create failed')], 500);
		}

		$row->resources_count = $row->resources()->count();
		$row->api = route('api.resources.batchsystems.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Read a batchsystem
	 *
	 * @apiMethod POST
	 * @apiUri    /resources/batchsystems/{id}
	 * @apiParameter {
	 *      "name":          "id",
	 *      "description":   "The ID of the batchsystem",
	 *      "type":          "integer",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @return  Response
	 */
	public function read($id)
	{
		$row = Batchsystem::findOrFail($id);

		$row->resources_count = $row->resources()->count();
		$row->api = route('api.resources.batchsystems.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Update a batchsystem
	 *
	 * @apiMethod PUT
	 * @apiUri    /resources/batchsystems/{id}
	 * @apiParameter {
	 *      "name":          "id",
	 *      "description":   "The ID of the batchsystem",
	 *      "type":          "integer",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @apiParameter {
	 *      "name":          "name",
	 *      "description":   "The name of the batchsystem",
	 *      "type":          "string",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @return  Response
	 */
	public function update($id, Request $request)
	{
		$request->validate([
			'name' => 'required|max:16'
		]);

		$row = Batchsystem::findOrFail($id);

		if (!$row->update($request->all()))
		{
			return response()->json(['message' => trans('messages.update failed')], 500);
		}

		$row->resources_count = $row->resources()->count();
		$row->api = route('api.resources.batchsystems.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Delete a batchsystem
	 *
	 * @apiMethod DELETE
	 * @apiUri    /resources/batchsystems/{id}
	 * @apiParameter {
	 *      "name":          "id",
	 *      "description":   "The ID of the batchsystem",
	 *      "type":          "integer",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @return  Response
	 */
	public function delete($id)
	{
		$row = Batchsystem::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
		}

		return response()->json(null, 204);
	}
}
