<?php

namespace App\Modules\Courses\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Courses\Models\Member;
use App\Modules\Courses\Http\Resources\MemberResource;
use App\Modules\Courses\Http\Resources\MemberResourceCollection;
use App\Modules\Users\Models\User;

/**
 * Account Members
 *
 * @apiUri    /api/courses/members
 */
class MembersController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /courses/members
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "classaccountid",
	 * 		"description":   "Class account ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "membertype",
	 * 		"description":   "Member type ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "userid",
	 * 		"description":   "User ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "notice",
	 * 		"description":   "Notice status",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "search",
	 * 		"description":   "A word or phrase to search for.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "search"
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
	 * 		"required":      false,
	 * 		"default":       "name",
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "name",
	 * 			"enum": [
	 * 				"id",
	 * 				"name",
	 * 				"owneruserid",
	 * 				"unixgroup",
	 * 				"unixid",
	 * 				"deptnumber"
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "order_dir",
	 * 		"description":   "Direction to sort results by.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "asc",
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
	 * @return ResourceCollection
	 */
	public function index(Request $request)
	{
		$filters = array(
			'classaccountid'   => $request->input('classaccountid', 0),
			'userid'   => $request->input('userid', 0),
			'membertype'   => $request->input('membertype', 0),
			// Paging
			'limit'    => $request->input('limit', config('list_limit', 20)),
			// Sorting
			'order'     => $request->input('order', Member::$orderBy),
			'order_dir' => $request->input('order_dir', Member::$orderDir)
		);

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Member::$orderDir;
		}

		$query = Member::query();

		if ($filters['classaccountid'])
		{
			$query->where('classaccountid', '=', $filters['classaccountid']);
		}

		if ($filters['userid'])
		{
			$query->where('userid', '=', $filters['userid']);
		}

		if ($filters['membertype'])
		{
			$query->where('membertype', '=', $filters['membertype']);
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit']);

		$rows->appends(array_filter($filters));

		return new MemberResourceCollection($rows);
	}

	/**
	 * Create a new entry
	 *
	 * @apiMethod POST
	 * @apiUri    /courses/members
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "classaccountid",
	 * 		"description":   "Class account ID",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "membertype",
	 * 		"description":   "Member type ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "userid",
	 * 		"description":   "User ID",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "datetimestart",
	 * 		"description":   "Datetime (YYYY-MM-DD hh:mm:ss) user enrollment starts",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date-time"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "datetimestop",
	 * 		"description":   "Datetime (YYYY-MM-DD hh:mm:ss) user enrollment stops",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date-time"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "notice",
	 * 		"description":   "Notice state",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @param  Request  $request
	 * @return JsonResource
	 */
	public function create(Request $request)
	{
		$request->validate([
			'classaccountid' => 'required|integer',
			'userid' => 'required',
			'membertype' => 'nullable|integer',
		]);

		$classaccountid = $request->input('classaccountid');
		$userid  = $request->input('userid');

		if (!is_numeric($userid))
		{
			$user = User::createFromUsername($userid);

			if ($user && $user->id)
			{
				$userid = $user->id;
			}
		}

		$exists = Member::findByAccountAndUser($classaccountid, $userid);

		if ($exists)
		{
			return new MemberResource($exists);
		}

		$row = new Member;
		$row->classaccountid = $classaccountid;
		$row->userid = $userid;
		$row->membertype = $request->input('membertype', 1);
		$row->notice = 1;
		$row->datetimestart = $row->account->datetimestart;
		$row->datetimestop = $row->account->datetimestop;

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.create failed')], 500);
		}

		return new MemberResource($row);
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /courses/members/{id}
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
	 * @return  JsonResource
	 */
	public function read($id)
	{
		$row = Member::findOrFail($id);

		return new MemberResource($row);
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /courses/members/{id}
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
	 * 		"name":          "classaccountid",
	 * 		"description":   "Class account ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "membertype",
	 * 		"description":   "Member type ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "userid",
	 * 		"description":   "User ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "datetimestart",
	 * 		"description":   "Datetime (YYYY-MM-DD hh:mm:ss) user enrollment starts",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date-time"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "datetimestop",
	 * 		"description":   "Datetime (YYYY-MM-DD hh:mm:ss) user enrollment stops",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date-time"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "notice",
	 * 		"description":   "Notice state",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @param   Request $request
	 * @param   integer  $id
	 * @return  JsonResource
	 */
	public function update(Request $request, $id)
	{
		$request->validate([
			'classaccountid' => 'nullable|integer',
			'userid' => 'nullable|integer',
			'membertype' => 'nullable|integer',
			'notice' => 'nullable|integer',
			'datetimestart' => 'nullable|date',
			'datetimestop' => 'nullable|date',
		]);

		$row = Member::findOrFail($id);

		if ($classaccountid = $request->input('classaccountid'))
		{
			$row->classaccountid = $classaccountid;
		}

		if ($userid = $request->input('userid'))
		{
			$row->userid = $userid;
		}

		if ($membertype = $request->input('membertype'))
		{
			$row->membertype = $membertype;
		}

		if ($notice = $request->input('notice'))
		{
			$row->notice = $notice;
		}

		if ($datetimestart = $request->input('datetimestart'))
		{
			$row->datetimestart = $datetimestart;
		}

		if ($datetimestop = $request->input('datetimestop'))
		{
			$row->datetimestop = $datetimestop;
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.create failed')], 500);
		}

		return new MemberResource($row);
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /courses/members/{id}
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
		$row = Member::findOrFail($id);

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
