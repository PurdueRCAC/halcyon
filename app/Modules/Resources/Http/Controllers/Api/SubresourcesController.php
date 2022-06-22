<?php

namespace App\Modules\Resources\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Resources\Models\Child;
use App\Modules\Resources\Models\Subresource;
use App\Modules\Resources\Http\Resources\SubresourceResourceCollection;
use App\Modules\Resources\Http\Resources\SubresourceResource;

/**
 * Sub-resources
 *
 * @apiUri    /resources/subresources
 */
class SubresourcesController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @apiMethod GET
	 * @apiUri    /resources/subresources/
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "limit",
	 * 		"description":   "Number of result to return.",
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
	 * 		"description":   "Field to sort results by.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 *      "default":       "created",
	 * 		"allowedValues": "id, name, datetimecreated, datetimeremoved, parentid"
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
	 * @return Response
	 */
	public function index(Request $request)
	{
		$filters = array(
			'search'   => $request->input('search', ''),
			'state'    => $request->input('state', 'active'),
			'resource' => $request->input('resource', null),
			// Paging
			'limit'    => $request->input('limit', config('list_limit', 20)),
			//'start' => $request->input('limitstart', 0),
			// Sorting
			'order'     => $request->input('order', Subresource::$orderBy),
			'order_dir' => $request->input('order_dir', Subresource::$orderDir)
		);

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Subresource::$orderDir;
		}

		// Build query
		$s = (new Subresource)->getTable();

		$query = Subresource::query()
			->with('queues');

		if ($filters['state'] == 'trashed')
		{
			$query->onlyTrashed();
		}
		elseif ($filters['state'] == 'all')
		{
			$query->withTrashed();
		}

		if ($filters['search'])
		{
			if (is_numeric($filters['search']))
			{
				$query->where($s . '.id', '=', $filters['search']);
			}
			else
			{
				$query->where($s . '.name', 'like', '%' . $filters['search'] . '%');
			}
		}

		if ($filters['resource'])
		{
			$c = (new Child)->getTable();

			$query->join($c, $c . '.subresourceid', $s . '.id')
				->where($c . '.resourceid', '=', $filters['resource']);
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'])
			->appends(array_filter($filters));

		return new SubresourceResourceCollection($rows);
	}

	/**
	 * Create a resource
	 *
	 * @apiMethod POST
	 * @apiUri    /resources/subresources/
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "resourceid",
	 *      "description":   "ID of the parent resource",
	 *      "required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "name",
	 *      "description":   "The name of the sub-resource",
	 *      "required":      true,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "cluster",
	 *      "description":   "Cluster name",
	 *      "required":      true,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "nodecores",
	 *      "description":   "Number of node cores",
	 *      "required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "nodemem",
	 *      "description":   "Memory per node",
	 *      "required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "nodegpus",
	 *      "description":   "Number of GPUs per node",
	 *      "required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "nodeattributes",
	 *      "description":   "Node attributes",
	 *      "required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "description",
	 *      "description":   "Short description of the sub-resource",
	 *      "required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "notice",
	 *      "description":   "Notification status",
	 *      "required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 *      "in":            "body",
	 *      "name":          "queuestatus",
	 *      "description":   "Queue status",
	 *      "required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
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
			'name'           => 'required|unique:subresource|max:32',
			'cluster'        => 'required|string|max:32',
			'nodecores'      => 'required|integer',
			'nodemem'        => 'required|string|max:5',
			'nodegpus'       => 'nullable|integer',
			'nodeattributes' => 'nullable|string|max:16',
			'description'    => 'nullable|string|max:255',
			'notice'         => 'nullable|integer'
		]);

		$row = Subresource::create($request->all());

		// Create Resource/Subresource association
		$child = new Child;
		$child->resourceid = $request->input('resourceid');

		if (!$child->resource)
		{
			return response()->json(['message' => trans('resources::assets.invalid resource')], 415);
		}

		$child->subresourceid = $row->id;
		$child->save();

		return new SubresourceResource($row);
	}

	/**
	 * Read a resource
	 *
	 * @apiMethod GET
	 * @apiUri    /resources/subresources/{id}
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
		$row = Subresource::findOrFail($id);

		return new SubresourceResource($row);
	}

	/**
	 * Update a resource
	 *
	 * @apiMethod PUT
	 * @apiUri    /resources/subresources/{id}
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
	 *      "description":   "The name of the sub-resource",
	 *      "required":      false,
	 *      "schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "cluster",
	 *      "description":   "Cluster name",
	 *      "required":      false,
	 *      "schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "nodecores",
	 *      "description":   "Number of node cores",
	 *      "required":      false,
	 *      "schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "nodemem",
	 *      "description":   "Memory per node",
	 *      "required":      false,
	 *      "schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "nodegpus",
	 *      "description":   "Number of GPUs per node",
	 *      "required":      false,
	 *      "schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "nodeattributes",
	 *      "description":   "Node attributes",
	 *      "required":      false,
	 *      "schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "description",
	 *      "description":   "Short description of the sub-resource",
	 *      "required":      false,
	 *      "schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "notice",
	 *      "description":   "Notification status",
	 *      "required":      false,
	 *      "schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "queuestatus",
	 *      "description":   "Queue status",
	 *      "required":      false,
	 *      "schema": {
	 * 			"type":      "integer"
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
			'name'           => 'nullable|string|max:32',
			'cluster'        => 'nullable|string|max:32',
			'nodecores'      => 'nullable|integer',
			'nodemem'        => 'nullable|string|max:5',
			'nodegpus'       => 'nullable|integer',
			'nodeattributes' => 'nullable|string|max:16',
			'description'    => 'nullable|string|max:255',
			'notice'         => 'nullable|integer',
			'queuestatus'    => 'nullable|integer|min:0|max:1',
		]);

		$data = $request->all();

		$queuestatus = null;
		if (isset($data['queuestatus']))
		{
			$queuestatus = $data['queuestatus'];
			unset($data['queuestatus']);
		}

		$row = Subresource::findOrFail($id);
		$row->update($data);

		if (!is_null($queuestatus))
		{
			if ($queuestatus)
			{
				// Start all queues
				$row->startQueues();
			}
			else
			{
				// Stop all queues
				$row->stopQueues();
			}
		}

		return new SubresourceResource($row);
	}

	/**
	 * Delete a resource
	 *
	 * @apiMethod DELETE
	 * @apiUri    /resources/subresources/{id}
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
	public function delete($id)
	{
		$row = Subresource::findOrFail($id);

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
