<?php

namespace App\Modules\Resources\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Resources\Entities\Asset;
use App\Modules\Resources\Http\Resources\AssetResourceCollection;
use App\Modules\Resources\Http\Resources\AssetResource;

class ResourcesController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @apiMethod GET
	 * @apiUri    /resources
	 * @apiParameter {
	 * 		"name":          "limit",
	 * 		"description":   "Number of result to return.",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       25
	 * }
	 * @apiParameter {
	 * 		"name":          "page",
	 * 		"description":   "Number of where to start returning results.",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       0
	 * }
	 * @apiParameter {
	 * 		"name":          "search",
	 * 		"description":   "A word or phrase to search for.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       ""
	 * }
	 * @apiParameter {
	 * 		"name":          "order",
	 * 		"description":   "Field to order results by.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 *      "default":       "created",
	 * 		"allowedValues": "id, name, datetimecreated, datetimeremoved, parentid"
	 * }
	 * @apiParameter {
	 * 		"name":          "order_dir",
	 * 		"description":   "Direction to order results by.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "desc",
	 * 		"allowedValues": "asc, desc"
	 * }
	 * @return Response
	 */
	public function index(Request $request)
	{
		$filters = array(
			'search'   => $request->input('search', ''),
			'state'    => $request->input('state', 'active'),
			'type'     => $request->input('type', null),
			// Paging
			'limit'    => $request->input('limit', config('list_limit', 20)),
			//'start' => $request->input('limitstart', 0),
			// Sorting
			'order'     => $request->input('order', 'name'),
			'order_dir' => $request->input('order_dir', 'asc')
		);

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = 'asc';
		}

		$query = Asset::query();

		if ($filters['search'])
		{
			$query->where('name', 'like', '%' . $filters['search'] . '%');
		}

		if ($filters['state'])
		{
			if ($filters['state'] == 'all')
			{
				$query->withTrashed();
				//$query->where('datetimeremoved', '=', '0000-00-00 00:00:00');
			}
			elseif ($filters['state'] == 'inactive')
			{
				$query->onlyTrashed();
				//$query->where('datetimeremoved', '!=', '0000-00-00 00:00:00');
			}
		}

		if (is_numeric($filters['type']))
		{
			$query->where('resourcetype', '=', $filters['type']);
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'])
			->appends(array_filter($filters));

		return new AssetResourceCollection($rows);
	}

	/**
	 * Create a resource
	 *
	 * @apiMethod POST
	 * @apiUri    /resources
	 * @apiParameter {
	 *      "name":          "name",
	 *      "description":   "The name of the resource type",
	 *      "type":          "string",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @return Response
	 */
	public function create(Request $request)
	{
		$request->validate([
			'name' => 'required|string|max:32',
			'parentid' => 'required|integer|min:1',
			'batchsystem' => 'required|integer|min:1',
			'resourcetype' => 'required|integer|min:1',
			'producttype' => 'nullable|integer',
			'rolename' => 'nullable|string',
			'listname' => 'nullable|string',
		]);

		$exist = Asset::findByName($request->input('name'));

		if ($exist)
		{
			return new AssetResource($exist);
		}

		$row = Asset::create($request->all());

		return new AssetResource($row);
	}

	/**
	 * Read a resource
	 *
	 * @apiMethod POST
	 * @apiUri    /resources/{id}
	 * @apiParameter {
	 *      "name":          "id",
	 *      "description":   "The ID of the resource type",
	 *      "type":          "integer",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @return  Response
	 */
	public function read($id)
	{
		$row = Asset::findOrFail($id);

		return new AssetResource($row);
	}

	/**
	 * Update a resource
	 *
	 * @apiMethod PUT
	 * @apiUri    /resources/{id}
	 * @apiParameter {
	 *      "name":          "id",
	 *      "description":   "The ID of the resource type",
	 *      "type":          "integer",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @apiParameter {
	 *      "name":          "name",
	 *      "description":   "The name of the resource type",
	 *      "type":          "string",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @return  Response
	 */
	public function update($id, Request $request)
	{
		$request->validate([
			'name' => 'nullable|string|max:32',
			'parentid' => 'nullable|integer|min:1',
			'batchsystem' => 'nullable|integer|min:1',
			'resourcetype' => 'nullable|integer|min:1',
			'producttype' => 'nullable|integer',
			'rolename' => 'nullable|string',
			'listname' => 'nullable|string',
		]);

		$row = Asset::findOrFail($id);
		$row->fill($request->all());

		if ($row->name != $row->getOriginal('name'))
		{
			$exist = Asset::findByName($request->input('name'));

			if ($exist && $exist->id != $row->id)
			{
				return response()->json(['message' => trans('Entry already exists for name `:name`', ['name' => $row->name])], 415);
			}
		}

		$row->save();

		return new AssetResource($row);
	}

	/**
	 * Delete a resource
	 *
	 * @apiMethod DELETE
	 * @apiUri    /resources/{id}
	 * @apiParameter {
	 *      "name":          "id",
	 *      "description":   "The ID of the resource type",
	 *      "type":          "integer",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @return  Response
	 */
	public function delete($id)
	{
		$row = Asset::findOrFail($id);

		if (!$row->trashed())
		{
			if (!$row->delete())
			{
				return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
			}
		}

		return response()->json(null, 204);
	}
}
