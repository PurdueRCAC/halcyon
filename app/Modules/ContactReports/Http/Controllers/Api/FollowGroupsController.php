<?php

namespace App\Modules\ContactReports\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Validator;
use App\Modules\Groups\Models\Member as GroupUser;
use Carbon\Carbon;

/**
 * Follow Groups
 *
 * @apiUri    /api/contactreports/followgroups
 */
class FollowGroupsController extends Controller
{
	/**
	 * Display a listing of contact reports followgroups
	 *
	 * @apiMethod GET
	 * @apiUri    /api/contactreports/followgroups
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "contactreportid",
	 * 		"description":   "ID of contact report",
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
	 * @param  Request $request
	 * @return Response
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = array(
			'groupid' => 0,
			'userid' => 0,
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			'order'     => GroupUser::$orderBy,
			'order_dir' => GroupUser::$orderDir,
		);

		foreach ($filters as $key => $default)
		{
			$val = $request->input($key);
			$val = !is_null($val) ? $val : $default;

			$filters[$key] = $val;
		}

		if (!in_array($filters['order'], ['id', 'userid', 'datecreated', 'targetuserid']))
		{
			$filters['order'] = GroupUser::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = GroupUser::$orderDir;
		}

		$query = GroupUser::query()
			->where('membertype', '=', 10);

		if ($filters['userid'])
		{
			$query->where('userid', '=', $filters['userid']);
		}

		if ($filters['groupid'])
		{
			$query->where('groupid', '=', $filters['groupid']);
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		return new ResourceCollection($rows);
	}

	/**
	 * Create a contact report comment
	 *
	 * @apiMethod POST
	 * @apiUri    /api/contactreports/followusers
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "comment",
	 * 		"description":   "The comment being made",
	 * 		"type":          "string",
	 * 		"required":      true,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "contactreportid",
	 * 		"description":   "ID of the contact report",
	 * 		"type":          "integer",
	 * 		"required":      true,
	 * 		"default":       null
	 * }
	 * @param   Request  $request
	 * @return  Response
	 */
	public function create(Request $request)
	{
		$rules = [
			'groupid' => 'required|integer|min:1',
			'userid' => 'nullable|integer',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = new Follow();
		$row->groupid = $request->input('groupid');
		$row->userid = $request->input('userid', auth()->user() ? auth()->user()->id : 0);
		$row->membertype = 10;
		$row->datecreated = Carbon::now()->toDateTimeString();

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.create failed')], 500);
		}

		return new JsonResource($row);
	}

	/**
	 * Retrieve a contact report comment
	 *
	 * @apiMethod GET
	 * @apiUri    /api/contactreports/followusers/{id}
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
		$item = GroupUser::findOrFail((int)$id);

		return new JsonResource($item);
	}

	/**
	 * Update a contact report comment
	 *
	 * @apiMethod PUT
	 * @apiUri    /api/contactreports/followusers/{id}
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
	 * 		"name":          "comment",
	 * 		"description":   "The comment being made",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "contactreportid",
	 * 		"description":   "ID of the contact report",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @param   Request  $request
	 * @param   integer  $id
	 * @return  Response
	 */
	public function update(Request $request, $id)
	{
		$rules = [
			'groupid' => 'nullable|integer',
			'userid' => 'nullable|integer',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = GroupUser::findOrFail($id);
		if ($request->has('groupid'))
		{
			$row->groupid = $request->input('groupid');
		}
		if ($request->has('userid'))
		{
			$row->userid = $request->input('userid');
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.update failed')], 500);
		}

		return new JsonResource($row);
	}

	/**
	 * Delete a contact report comment
	 *
	 * @apiMethod DELETE
	 * @apiUri    /api/contactreports/followusers/{id}
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
		$row = GroupUser::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $row->id])], 500);
		}

		return response()->json(null, 204);
	}
}
