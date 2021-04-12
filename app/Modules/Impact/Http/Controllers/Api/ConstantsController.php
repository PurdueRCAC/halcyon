<?php

namespace App\Modules\Impact\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Impact\Models\Constant;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Impact Constants
 *
 * @apiUri    /api/impact/constants
 */
class ConstantsController extends Controller
{
	/**
	 * Display a listing of constants
	 *
	 * @apiMethod GET
	 * @apiUri    /api/impact/constants
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
			'impacttableid' => 0,
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			'order'     => Constant::$orderBy,
			'order_dir' => Constant::$orderDir,
		);

		foreach ($filters as $key => $default)
		{
			$val = $request->input($key);
			$val = !is_null($val) ? $val : $default;

			$filters[$key] = $val;
		}

		if (!in_array($filters['order'], ['id', 'name', 'value', 'sequence']))
		{
			$filters['order'] = Constant::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Constant::$orderDir;
		}

		$query = Constant::query();

		if ($filters['search'])
		{
			$query->where('name', 'like', '%' . $filters['search'] . '%');
		}

		if ($filters['impacttableid'])
		{
			$query->where('impacttableid', '=', $filters['id']);
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		return new ResourceCollection($rows);
	}

	/**
	 * Create a constant
	 *
	 * @apiMethod POST
	 * @apiUri    /api/impact/constants
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "name",
	 * 		"description":   "Constant name",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "value",
	 * 		"description":   "Value",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "impacttableid",
	 * 		"description":   "Impact table ID",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @param   Request  $request
	 * @return  Response
	 */
	public function create(Request $request)
	{
		$request->validate([
			'name' => 'required|string|max:255',
			'value' => 'required|string|max:255',
			'impacttableid' => 'required|integer',
		]);

		$row = new Constant();
		$row->name = $request->input('name');
		$row->value = $request->input('value');
		$row->impacttableid = $request->input('impacttableid');

		if (!$row->table)
		{
			return response()->json(['message' => trans('impact::impact.error.table not found')], 415);
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.create failed')], 500);
		}

		return new JsonResource($row);
	}

	/**
	 * Retrieve a constant
	 *
	 * @apiMethod GET
	 * @apiUri    /api/impact/constants/{id}
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
		$row = Constant::findOrFail((int)$id);

		return new JsonResource($row);
	}

	/**
	 * Update a constant
	 *
	 * @apiMethod PUT
	 * @apiUri    /api/impact/constants/{id}
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
	 * 		"description":   "Constant name",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "value",
	 * 		"description":   "Value",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "impacttableid",
	 * 		"description":   "Impact table ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @param   Request  $request
	 * @param   integer  $id
	 * @return  Response
	 */
	public function update(Request $request, $id)
	{
		$request->validate([
			'name' => 'required|string|max:255',
			'value' => 'required|string|max:255',
			'impacttableid' => 'required|integer',
		]);

		$row = Constant::findOrFail($id);
		if ($request->has('name'))
		{
			$row->name = $request->input('name');
		}
		if ($request->has('value'))
		{
			$row->value = $request->input('value');
		}
		if ($request->has('impacttableid'))
		{
			$row->impacttableid = $request->input('impacttableid');
		}

		if (!$row->table)
		{
			return response()->json(['message' => trans('impact::impact.error.table not found')], 415);
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.update failed')], 500);
		}

		return new JsonResource($row);
	}

	/**
	 * Delete a constant
	 *
	 * @apiMethod DELETE
	 * @apiUri    /api/impact/constants/{id}
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
		$row = Constant::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
		}

		return response()->json(null, 204);
	}
}
