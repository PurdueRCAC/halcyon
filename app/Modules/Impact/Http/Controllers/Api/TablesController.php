<?php

namespace App\Modules\Impact\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Impact\Models\Table;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Impact Tables
 *
 * @apiUri    /api/impact/tables
 */
class TablesController extends Controller
{
	/**
	 * Display a listing of tables
	 *
	 * @apiMethod GET
	 * @apiUri    /api/impact/tables
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
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       1
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "order",
	 * 		"description":   "Field to sort results by.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "datetimecreated",
	 * 		"allowedValues": "id, motd, datetimecreated, datetimeremoved"
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
	 * @param  Request  $request
	 * @return Response
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			'order'     => Table::$orderBy,
			'order_dir' => Table::$orderDir,
		);

		foreach ($filters as $key => $default)
		{
			$val = $request->input($key);
			$val = !is_null($val) ? $val : $default;

			$filters[$key] = $val;
		}

		if (!in_array($filters['order'], ['id', 'name', 'columnname', 'sequence', 'updatekey']))
		{
			$filters['order'] = Table::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Table::$orderDir;
		}

		$query = Table::query();

		if ($filters['search'])
		{
			$query->where(function($where)
			{
				$where->where('name', 'like', '%' . $filters['search'] . '%')
					->orWhere('columnname', 'like', '%' . $filters['search'] . '%');
			});
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		return new ResourceCollection($rows);
	}

	/**
	 * Create a new table
	 *
	 * @apiMethod POST
	 * @apiUri    /api/impact/tables
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "name",
	 * 		"description":   "Table name",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "columnname",
	 * 		"description":   "Column name",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "updatekey",
	 * 		"description":   "Update key",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @param   Request  $request
	 * @return  Response
	 */
	public function create(Request $request)
	{
		$request->validate([
			'name' => 'required|string|max:255',
			'columnname' => 'nullable|string|max:255',
			'updatekey' => 'nullable|string|max:255',
		]);

		$row = new Table();
		$row->name = $request->input('name');
		if ($request->has('columnname'))
		{
			$row->columnname = $request->input('columnname');
		}
		if ($request->has('updatekey'))
		{
			$row->updatekey = $request->input('updatekey');
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.create failed')], 500);
		}

		return new JsonResource($row);
	}

	/**
	 * Retrieve an issue
	 *
	 * @apiMethod GET
	 * @apiUri    /api/impact/tables/{id}
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
		$row = Table::findOrFail((int)$id);

		return new JsonResource($row);
	}

	/**
	 * Update an issue
	 *
	 * @apiMethod PUT
	 * @apiUri    /api/impact/tables/{id}
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
	 * 		"description":   "Table name",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "columnname",
	 * 		"description":   "Column name",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "updatekey",
	 * 		"description":   "Update key",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @param   Request  $request
	 * @param   integer  $id
	 * @return  Response
	 */
	public function update(Request $request, $id)
	{
		$request->validate([
			'name' => 'nullable|string|max:255',
			'columnname' => 'nullable|string|max:255',
			'updatekey' => 'nullable|string|max:255',
		]);

		$row = Table::findOrFail($id);
		if ($request->has('name'))
		{
			$row->name = $request->input('name');
		}
		if ($request->has('columnname'))
		{
			$row->columnname = $request->input('columnname');
		}
		if ($request->has('updatekey'))
		{
			$row->updatekey = $request->input('updatekey');
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.update failed')], 500);
		}

		return new JsonResource($row);
	}

	/**
	 * Delete an issue
	 *
	 * @apiMethod DELETE
	 * @apiUri    /api/impact/tables/{id}
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
		$row = Table::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
		}

		return response()->json(null, 204);
	}
}
