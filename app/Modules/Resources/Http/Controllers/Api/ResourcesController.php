<?php

namespace App\Modules\Resources\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Resources\Models\Asset;
use App\Modules\Resources\Http\Resources\AssetResourceCollection;
use App\Modules\Resources\Http\Resources\AssetResource;
use App\Modules\Resources\Http\Resources\MemberResourceCollection;
use App\Modules\Users\Models\User;
use App\Modules\Users\Models\Userusername;

/**
 * Resources
 *
 * @apiUri    /api/resources
 */
class ResourcesController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @apiMethod GET
	 * @apiUri    /api/resources
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "limit",
	 * 		"description":   "Number of result to return.",
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
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       0
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "search",
	 * 		"description":   "A word or phrase to search for.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       ""
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "order",
	 * 		"description":   "Field to order results by.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 *      "default":       "created",
	 * 		"allowedValues": "id, name, datetimecreated, datetimeremoved, parentid"
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "order_dir",
	 * 		"description":   "Direction to order results by.",
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
	 * @apiUri    /api/resources
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 *		"name":          "name",
	 * 		"description":   "The name of the resource type",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 32
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "parentid",
	 *      "description":   "Parent resource ID",
	 *      "required":      false,
	 *      "schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "resourcetype",
	 *      "description":   "Resource type ID",
	 *      "required":      true,
	 *      "schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "producttype",
	 *      "description":   "Product type ID",
	 *      "required":      false,
	 *      "schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "rolename",
	 *      "description":   "An alias containing only alpha-numeric characters, dashes, and underscores",
	 *      "required":      false,
	 *      "schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 32
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "listname",
	 *      "description":   "An alias containing only alpha-numeric characters, dashes, and underscores",
	 *      "required":      false,
	 *      "schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 32
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "description",
	 *      "description":   "A short description of the resource",
	 *      "required":      false,
	 *      "schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 2000
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"201": {
	 * 			"description": "Successful entry creation"
	 * 		},
	 * 		"409": {
	 * 			"description": "Invalid data"
	 * 		}
	 * }
	 * @param  Request  $request
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
	 * @apiMethod GET
	 * @apiUri    /api/resources/{id}
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
	 * 			"description": "Successful entry read"
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		}
	 * }
	 * @param   integer  $id
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
	 * @apiUri    /api/resources/{id}
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
	 *      "name":          "name",
	 *      "description":   "The name of the resource type",
	 *      "required":      false,
	 *      "schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 32
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "parentid",
	 *      "description":   "Parent resource ID",
	 *      "required":      false,
	 *      "schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "resourcetype",
	 *      "description":   "Resource type ID",
	 *      "required":      false,
	 *      "schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "producttype",
	 *      "description":   "Product type ID",
	 *      "required":      false,
	 *      "schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "rolename",
	 *      "description":   "An alias containing only alpha-numeric characters, dashes, and underscores",
	 *      "required":      false,
	 *      "schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 32
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "listname",
	 *      "description":   "An alias containing only alpha-numeric characters, dashes, and underscores",
	 *      "required":      false,
	 *      "schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 32
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "description",
	 *      "description":   "A short description of the resource",
	 *      "required":      false,
	 *      "schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 2000
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"204": {
	 * 			"description": "Successful entry modification"
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		},
	 * 		"409": {
	 * 			"description": "Invalid data"
	 * 		}
	 * }
	 * @param   integer  $id
	 * @param   Request  $request
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
			'status' => 'nullable|string',
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
	 * @apiUri    /api/resources/{id}
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

	/**
	 * Read a resource
	 *
	 * @apiMethod GET
	 * @apiUri    /api/resources/{id}
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
	public function members($id)
	{
		$resource = Asset::findOrFail($id);

		$subresources = $resource->subresources()
			->where(function($where)
			{
				$where->whereNull('datetimeremoved')
					->orWhere('datetimeremoved', '=', '0000-00-00 00:00:00');
			})
			->get();

		$userids = array();

		foreach ($subresources as $sub)
		{
			$queues = $sub->queues()
				->withTrashed()
				->where(function($where)
				{
					$where->whereNull('datetimeremoved')
						->orWhere('datetimeremoved', '=', '0000-00-00 00:00:00');
				})
				->get();
				//->pluck('queuid')
				//->toArray();

			foreach ($queues as $queue)
			{
				$userids += $queue->users()
					->withTrashed()
					->whereIsActive()
					->get()
					->pluck('userid')
					->toArray();
			}
		}

		$userids = array_unique($userids);

		$u = (new User)->getTable();
		$uu = (new Userusername)->getTable();

		$rows = User::query()
			->select($u . '.*')
			->join($uu, $uu . '.userid', $u . '.id')
			->where(function($where) use ($uu)
			{
				$where->whereNull($uu . '.dateremoved')
					->orWhere($uu . '.dateremoved', '=', '0000-00-00 00:00:00');
			})
			->whereIn($u . '.id', $userids)
			->get();

		return new MemberResourceCollection($rows);
	}
}
