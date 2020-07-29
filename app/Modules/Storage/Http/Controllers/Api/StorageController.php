<?php

namespace App\Modules\Storage\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Modules\Storage\Models\StorageResource;

class StorageController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @apiMethod GET
	 * @apiUri    /storage
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
	 * 		"name":          "sort",
	 * 		"description":   "Field to sort results by.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "created",
	 * 		"allowedValues": "id, name, datetimecreated, datetimeremoved, parentid"
	 * }
	 * @apiParameter {
	 * 		"name":          "sort_dir",
	 * 		"description":   "Direction to sort results by.",
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
			// Paging
			'limit'    => $request->input('limit', config('list_limit', 20)),
			// Sorting
			'order'     => $request->input('order', 'name'),
			'order_dir' => $request->input('order_dir', 'asc')
		);

		// Get records
		$query = StorageResource::query();

		if ($filters['state'] != '*')
		{
			if ($filters['state'] == 'active')
			{
				$query->where('datetimeremoved', '=', '0000-00-00 00:00:00');
			}
			elseif ($filters['state'] == 'inactive')
			{
				$query->where('datetimeremoved', '!=', '0000-00-00 00:00:00');
			}
		}

		if ($filters['search'])
		{
			if (is_numeric($filters['search']))
			{
				$query->where('id', '=', $filters['search']);
			}
			else
			{
				$query->where('name', 'like', '%' . $filters['search'] . '%');
			}
		}

		$rows = $query
			->withCount('directories')
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'])
			->appends($filters);

		return new ResourceCollection($rows);
	}

	/**
	 * Create a resource
	 *
	 * @apiMethod POST
	 * @apiUri    /storage
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
			'path' => 'required|string|max:255',
			'parentresourceid' => 'nullable|integer',
			'import' => 'nullable|integer',
			'importhostname' => 'nullable|in:0,1',
			'autouserdir' => 'nullable|in:0,1',
			'defaultquotaspace' => 'nullable|integer',
			'defaultquotafile' => 'nullable|integer',
			'getquotatypeid' => 'nullable|integer',
			'createtypeid' => 'nullable|integer',
		]);

		$row = new StorageResource;
		$row->fill($data);

		// Make sure name is sane
		if (!preg_match("/^([a-zA-Z0-9]+\.?[\-_ ]*)*[a-zA-Z0-9]$/", $row->name))
		{
			return response()->json(['message' => trans('Field `name` has invalid format')], 415);
		}

		if ($row->parentresourceid)
		{
			if (!$row->resource)
			{
				return response()->json(['message' => trans('Invalid `parentresourceid`')], 415);
			}
		}

		if ($row->getquotatypeid)
		{
			if (!$row->quotaType)
			{
				return response()->json(['message' => trans('Invalid `getquotatypeid`')], 415);
			}
		}

		if ($row->createtypeid)
		{
			if (!$row->createType)
			{
				return response()->json(['message' => trans('Invalid `createtypeid`')], 415);
			}
		}

		$row->save();
		$row->directories_count = $row->directories()->count();

		return new JsonResource($row);
	}

	/**
	 * Read a resource
	 *
	 * @apiMethod POST
	 * @apiUri    /storage/{id}
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
		$row = StorageResource::findOrFail($id);
		$row->directories_count = $row->directories()->count();

		return new JsonResource($row);
	}

	/**
	 * Update a resource
	 *
	 * @apiMethod PUT
	 * @apiUri    /storage/{id}
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
		$row = StorageResource::findOrFail($id);

		$request->validate([
			'name' => 'nullable|string|max:32',
			'path' => 'nullable|string|max:255',
			'parentresourceid' => 'nullable|integer',
			'import' => 'nullable|integer',
			'importhostname' => 'nullable|in:0,1',
			'autouserdir' => 'nullable|in:0,1',
			'defaultquotaspace' => 'nullable|integer',
			'defaultquotafile' => 'nullable|integer',
			'getquotatypeid' => 'nullable|integer',
			'createtypeid' => 'nullable|integer',
		]);

		$row->fill($request->all());

		if ($request->has('parentresourceid'))
		{
			if (!$row->resource)
			{
				return response()->json(['message' => trans('Invalid `parentresourceid`')], 415);
			}
		}

		if ($request->has('getquotatypeid'))
		{
			if (!$row->quotaType)
			{
				return response()->json(['message' => trans('Invalid `getquotatypeid`')], 415);
			}
		}

		if ($request->has('createtypeid'))
		{
			if (!$row->createType)
			{
				return response()->json(['message' => trans('Invalid `createtypeid`')], 415);
			}
		}

		$row->save();
		$row->directories_count = $row->directories()->count();

		return new JsonResource($row);
	}

	/**
	 * Delete a storage directory
	 *
	 * @apiMethod DELETE
	 * @apiUri    /storage/{id}
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
		$row = Directory::findOrFail($id);

		if ($row->directories()->count() > 0)
		{
			return response()->json(['message' => trans('Storage Resource is not empty')], 409);
		}

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
		}

		return response()->json(null, 204);
	}
}
