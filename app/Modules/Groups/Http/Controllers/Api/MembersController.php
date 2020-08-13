<?php

namespace App\Modules\Groups\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Groups\Models\Group;
use App\Modules\Groups\Models\Member;

/**
 * Members
 *
 * @apiUri    /api/groups/members
 */
class MembersController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /api/groups/members
	 * @apiParameter {
	 * 		"name":          "limit",
	 * 		"description":   "Number of result per page.",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       25
	 * }
	 * @apiParameter {
	 * 		"name":          "page",
	 * 		"description":   "Number of where to start returning results.",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       1
	 * }
	 * @apiParameter {
	 * 		"name":          "owneruserid",
	 * 		"description":   "Owner user ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       0
	 * }
	 * @apiParameter {
	 * 		"name":          "unixgroup",
	 * 		"description":   "Unix group name",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       ""
	 * }
	 * @apiParameter {
	 * 		"name":          "unixid",
	 * 		"description":   "Unix ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       0
	 * }
	 * @apiParameter {
	 * 		"name":          "deptnumber",
	 * 		"description":   "Organization department ID",
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
	 * 		"description":   "Field to sort results by.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "name",
	 * 		"allowedValues": "id, name, owneruserid, unixgroup, unixid, deptnumber"
	 * }
	 * @apiParameter {
	 * 		"name":          "order_dir",
	 * 		"description":   "Direction to sort results by.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "asc",
	 * 		"allowedValues": "asc, desc"
	 * }
	 * @return Response
	 */
	public function index(Request $request)
	{
		$filters = array(
			'search'   => $request->input('search', ''),
			'groupid'   => $request->input('groupid', 0),
			'userid'   => $request->input('userid', 0),
			'userrequestid'   => $request->input('userrequestid', 0),
			'membertype'   => $request->input('membertype', 0),
			// Paging
			'limit'    => $request->input('limit', config('list_limit', 20)),
			//'start' => $request->input('limitstart', 0),
			// Sorting
			'order'     => $request->input('order', Group::$orderBy),
			'order_dir' => $request->input('order_dir', Group::$orderDir)
		);

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Group::$orderDir;
		}

		$query = Group::query();

		if ($filters['search'])
		{
			$filters['search'] = strtolower((string)$filters['search']);

			$query->where('name', 'like', '%' . $filters['search'] . '%');
		}

		if ($filters['groupid'])
		{
			$query->where('groupid', '=', $filters['groupid']);
		}

		if ($filters['userid'])
		{
			$query->where('userid', '=', $filters['userid']);
		}

		if ($filters['membertype'])
		{
			$query->where('membertype', '=', $filters['membertype']);
		}

		if ($filters['userrequestid'])
		{
			$query->where('userrequestid', '=', $filters['userrequestid']);
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit']);

		$rows->appends(array_filter($filters));

		$rows->each(function ($item, $key)
		{
			$item->api = route('api.groups.members.read', ['id' => $item->id]);
			$item->user;
		});

		return $rows;
	}

	/**
	 * Create a new entry
	 *
	 * @apiMethod POST
	 * @apiUri    /api/groups/members
	 * @apiParameter {
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"type":          "integer",
	 * 		"required":      true,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "name",
	 * 		"description":   "Group name",
	 * 		"type":          "string",
	 * 		"required":      true,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "unixgroup",
	 * 		"description":   "Unix group name",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "unixid",
	 * 		"description":   "Unix ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       0
	 * }
	 * @apiParameter {
	 * 		"name":          "deptnumber",
	 * 		"description":   "Organization department ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       0
	 * }
	 * @apiParameter {
	 * 		"name":          "githuborgname",
	 * 		"description":   "Github organization name",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @return Response
	 */
	public function create(Request $request)
	{
		$request->validate([
			'groupid' => 'required|integer',
			'userid' => 'required|integer',
			'membertype' => 'nullable|integer',
			'userrequestid' => 'nullable|integer',
		]);

		$groupid = $request->input('groupid');
		$userid  = $request->input('userid');

		$exists = Member::findByGroupAndUser($groupid, $userid);

		if ($exists)
		{
			return $exists; //response()->json(['message' => trans('groups::groups.group membership already exists')], 415);
		}

		$row = new Member;
		$row->groupid = $groupid;
		$row->userid = $userid;
		$row->membertype = $request->input('membertype', 1);
		$row->userrequestid = $request->input('userrequestid', 0);

		if (!$row->save())
		{
			return response()->json(['message' => trans('messages.create failed')], 500);
		}

		return $row;
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /api/groups/members/{id}
	 * @apiParameter {
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"type":          "integer",
	 * 		"required":      true,
	 * 		"default":       null
	 * }
	 * @return Response
	 */
	public function read($id)
	{
		$row = Member::findOrFail($id);
		$row->api = route('api.groups.members.read', ['id' => $row->id]);
		$row->user;

		return $row;
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /api/groups/members/{id}
	 * @apiParameter {
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"type":          "integer",
	 * 		"required":      true,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "name",
	 * 		"description":   "Group name",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "unixgroup",
	 * 		"description":   "Unix group name",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "unixid",
	 * 		"description":   "Unix ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "deptnumber",
	 * 		"description":   "Organization department ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "githuborgname",
	 * 		"description":   "Github organization name",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @param   Request $request
	 * @return  Response
	 */
	public function update(Request $request, $id)
	{
		$request->validate([
			'membertype' => 'nullable|integer',
			'userrequestid' => 'nullable|integer',
		]);

		$row = Member::findOrFail($id);
		$row->membertype = $request->input('membertype');
		$row->userrequestid = $request->input('userrequestid');

		if (!$row->save())
		{
			return response()->json(['message' => trans('messages.create failed')], 500);
		}

		return $row;
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /api/groups/members/{id}
	 * @apiParameter {
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"type":          "integer",
	 * 		"required":      true,
	 * 		"default":       null
	 * }
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
