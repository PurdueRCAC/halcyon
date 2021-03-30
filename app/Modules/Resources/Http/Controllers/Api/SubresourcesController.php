<?php

namespace App\Modules\Resources\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Resources\Entities\Child;
use App\Modules\Resources\Entities\Subresource;
use App\Modules\Resources\Http\Resources\SubresourceResourceCollection;
use App\Modules\Resources\Http\Resources\SubresourceResource;

/**
 * Sub-resources
 *
 * @apiUri    /api/resources/subresources
 */
class SubresourcesController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @apiMethod GET
	 * @apiUri    /api/resources/subresources/
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

		$query = Subresource::query();

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
	 * @apiUri    /api/resources/subresources/
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "name",
	 *      "description":   "The name of the sub-resource",
	 *      "type":          "string",
	 *      "required":      true,
	 *      "default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "cluster",
	 *      "description":   "Cluster name",
	 *      "type":          "string",
	 *      "required":      true,
	 *      "default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "nodecores",
	 *      "description":   "Number of node cores",
	 *      "type":          "integer",
	 *      "required":      false,
	 *      "default":       0
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "nodemem",
	 *      "description":   "Memory per node",
	 *      "type":          "string",
	 *      "required":      false,
	 *      "default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "nodegpus",
	 *      "description":   "Number of GPUs per node",
	 *      "type":          "integer",
	 *      "required":      false,
	 *      "default":       0
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "nodeattributes",
	 *      "description":   "Node attributes",
	 *      "type":          "string",
	 *      "required":      false,
	 *      "default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "description",
	 *      "description":   "Short description of the sub-resource",
	 *      "type":          "string",
	 *      "required":      false,
	 *      "default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "notice",
	 *      "description":   "Notification status",
	 *      "type":          "integer",
	 *      "required":      false,
	 *      "default":       0
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "queuestatus",
	 *      "description":   "Queue status",
	 *      "type":          "integer",
	 *      "required":      false,
	 *      "default":       0
	 * }
	 * @return Response
	 */
	public function create(Request $request)
	{
		$request->validate([
			'name'           => 'required|unique:subresource|max:32',
			'cluster'        => 'nullable|string|max:32',
			'nodecores'      => 'nullable|integer',
			'nodemem'        => 'nullable|string|max:5',
			'nodegpus'       => 'nullable|integer',
			'nodeattributes' => 'nullable|string|max:16',
			'description'    => 'nullable|string|max:255',
			'notice'         => 'nullable|integer',
		]);

		$row = Subresource::create($request->all());

		return new SubresourceResource($row);
	}

	/**
	 * Read a resource
	 *
	 * @apiMethod GET
	 * @apiUri    /api/resources/subresources/{id}
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
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
	 * @apiUri    /api/resources/subresources/{id}
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
	 *      "type":          "string",
	 *      "required":      false,
	 *      "default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "cluster",
	 *      "description":   "Cluster name",
	 *      "type":          "string",
	 *      "required":      false,
	 *      "default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "nodecores",
	 *      "description":   "Number of node cores",
	 *      "type":          "integer",
	 *      "required":      false,
	 *      "default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "nodemem",
	 *      "description":   "Memory per node",
	 *      "type":          "string",
	 *      "required":      false,
	 *      "default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "nodegpus",
	 *      "description":   "Number of GPUs per node",
	 *      "type":          "integer",
	 *      "required":      false,
	 *      "default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "nodeattributes",
	 *      "description":   "Node attributes",
	 *      "type":          "string",
	 *      "required":      false,
	 *      "default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "description",
	 *      "description":   "Short description of the sub-resource",
	 *      "type":          "string",
	 *      "required":      false,
	 *      "default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "notice",
	 *      "description":   "Notification status",
	 *      "type":          "integer",
	 *      "required":      false,
	 *      "default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "queuestatus",
	 *      "description":   "Queue status",
	 *      "type":          "integer",
	 *      "required":      false,
	 *      "default":       null
	 * }
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
	 * @apiUri    /api/resources/subresources/{id}
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
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
