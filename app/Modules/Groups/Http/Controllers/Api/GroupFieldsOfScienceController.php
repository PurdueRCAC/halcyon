<?php

namespace App\Modules\Groups\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Groups\Models\GroupFieldOfScience;

/**
 * FieldsOfScience
 *
 * @apiUri    /api/groups/{group}/fieldsofscience
 */
class GroupFieldsOfScienceController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /api/groups/{group}/fieldsofscience
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "group",
	 * 		"description":   "Group ID",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
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
	 * 		"name":          "order",
	 * 		"description":   "Field to sort results by.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "datetimecreated",
	 * 			"enum": [
	 * 				"id",
	 * 				"motd",
	 * 				"datetimecreated",
	 * 				"datetimeremoved"
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
	 * @param   integer  $group
	 * @return Response
	 */
	public function index(Request $request, $group)
	{
		$filters = array(
			'groupid' => $group,
			// Paging
			'limit'    => $request->input('limit', config('list_limit', 20)),
			// Sorting
			'order'     => $request->input('order', 'id'),
			'order_dir' => $request->input('order_dir', 'asc')
		);

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = FieldOfScience::$orderDir;
		}

		$query = GroupFieldOfScience::query();

		if ($filters['groupid'])
		{
			$query->where('groupid', '=', $filters['groupid']);
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'])
			->appends(array_filter($filters));

		$rows->each(function ($row, $key)
		{
			$row->api = route('api.groups.groupfieldsofscience.read', ['group' => $row->groupid, 'id' => $row->id]);
		});

		return new ResourceCollection($rows);
	}

	/**
	 * Create a new entry
	 *
	 * @apiMethod POST
	 * @apiUri    /api/groups/{group}/fieldsofscience
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "name",
	 * 		"description":   "FieldOfScience name",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "parentid",
	 * 		"description":   "Parent department ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @param   Request  $request
	 * @param   integer  $group
	 * @return Response
	 */
	public function create(Request $request, $group)
	{
		$request->validate([
			'fieldofscienceid' => 'required|integer',
			'percentage' => 'nullable|integer|max:100'
		]);

		$row = new GroupFieldOfScience;
		$row->groupid = $group;
		$row->fieldofscienceid = $request->input('fieldofscienceid');
		$row->percentage = $request->input('percentage', 100);

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.create failed')], 500);
		}

		$row->api = route('api.groups.groupfieldsofscience.read', ['group' => $row->groupid, 'id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /api/groups/{group}/fieldsofscience/{id}
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @param   integer  $group
	 * @param   integer  $id
	 * @return Response
	 */
	public function read($group, $id)
	{
		$row = GroupFieldOfScience::findOrFail($id);
		$row->api = route('api.groups.groupfieldsofscience.read', ['group' => $row->groupid, 'id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /api/groups/{group}/fieldsofscience/{id}
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
	 * 		"description":   "FieldOfScience name",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "parentid",
	 * 		"description":   "Parent department ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @param   Request $request
	 * @param   integer $group
	 * @param   integer $id
	 * @return  Response
	 */
	public function update(Request $request, $group, $id)
	{
		$request->validate([
			'fieldofscienceid' => 'required|integer',
			'percentage' => 'nullable|integer|max:100'
		]);

		$row = GroupFieldOfScience::findOrFail($id);

		if ($request->has('fieldofscienceid'))
		{
			$row->fieldofscienceid = $request->input('fieldofscienceid');
		}

		if ($request->has('percentage'))
		{
			$row->percentage = $request->input('percentage');
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.create failed')], 500);
		}

		$row->api = route('api.groups.groupfieldsofscience.read', ['group' => $row->groupid, 'id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /api/groups/{group}/fieldsofscience/{id}
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @param   integer  $group
	 * @param   integer  $id
	 * @return  Response
	 */
	public function delete($group, $id)
	{
		$row = GroupFieldOfScience::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
		}

		return response()->json(null, 204);
	}
}
