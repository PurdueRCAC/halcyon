<?php

namespace App\Modules\Groups\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Groups\Models\Group;
use App\Modules\Groups\Models\Member;
use App\Modules\Users\Models\User;

/**
 * Group Members
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
	 * @apiAuthorization  true
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
	 * 		"name":          "owneruserid",
	 * 		"description":   "Owner user ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       0
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "unixgroup",
	 * 		"description":   "Unix group name",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       ""
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "unixid",
	 * 		"description":   "Unix ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       0
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "deptnumber",
	 * 		"description":   "Organization department ID",
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
	 * 		"default":       "name",
	 * 		"allowedValues": "id, name, owneruserid, unixgroup, unixid, deptnumber"
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "order_dir",
	 * 		"description":   "Direction to sort results by.",
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
	 * @param   Request  $request
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
			'order'     => $request->input('order', Member::$orderBy),
			'order_dir' => $request->input('order_dir', Member::$orderDir)
		);

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Member::$orderDir;
		}

		$query = Member::query();

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

		return new ResourceCollection($rows);
	}

	/**
	 * Create a new entry
	 *
	 * @apiMethod POST
	 * @apiUri    /api/groups/members
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "name",
	 * 		"description":   "Group name",
	 * 		"type":          "string",
	 * 		"required":      true,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "unixgroup",
	 * 		"description":   "Unix group name",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "unixid",
	 * 		"description":   "Unix ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       0
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "deptnumber",
	 * 		"description":   "Organization department ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       0
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "githuborgname",
	 * 		"description":   "Github organization name",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @param   Request  $request
	 * @return  Response
	 */
	public function create(Request $request)
	{
		$rules = [
			'groupid' => 'required|integer',
			'userid' => 'required',
			'membertype' => 'nullable|integer',
			'userrequestid' => 'nullable|integer',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$groupid = $request->input('groupid');
		$userid  = $request->input('userid');

		if (!is_numeric($userid))
		{
			//$user = User::findByUsername($userid);

			//if (!$user || !$user->id)
			//{
				$user = User::createFromUsername($userid);
			//}

			if ($user && $user->id)
			{
				$userid = $user->id;
			}
		}

		//$row = Member::findByGroupAndUser($groupid, $userid);
		$row = Member::query()
			->withTrashed()
			->where('groupid', '=', $groupid)
			->where('userid', '=', $userid)
			->first();

		if (!$row)
		{
			$row = new Member;
		}
		elseif ($row->isTrashed())
		{
			$row->forceRestore(['dateremoved']);
		}

		$row->groupid = $groupid;

		if (!$row->group)
		{
			return response()->json(['message' => trans('groups::groups.error.invalid group id')], 415);
		}

		$row->userid = $userid;

		if (!$row->user)
		{
			return response()->json(['message' => trans('groups::groups.error.invalid user id' . $request->input('userid'))], 415);
		}

		// Do not allow non-admins to remove himself as an owner.
		// This would just invite too many potential problems.
		if ($row->isManager()
		 && $request->has('membertype')
		 && $request->input('membertype') != 2
		 && !auth()->user()->can('manage groups'))
		{
			if ($row->userid == auth()->user()->id)
			{
				return response()->json(['message' => trans('groups::groups.error.cannot remove self as owner')], 409);
			}
		}

		$row->membertype = $request->input('membertype', 1);

		if ($row->isManager())
		{
			$row->notice = 21;
		}

		// Do we have any owners?
		// If not, there's no one else to notify
		if (count($row->group->managers) == 0)
		{
			$row->notice = 0;
		}

		$row->userrequestid = $request->input('userrequestid', 0);

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.create failed')], 500);
		}

		$row->api = route('api.groups.members.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /api/groups/members/{id}
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
	 * @param  integer  $id
	 * @return Response
	 */
	public function read($id)
	{
		$row = Member::findOrFail($id);
		$row->api = route('api.groups.members.read', ['id' => $row->id]);
		$row->user;

		return new JsonResource($row);
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /api/groups/members/{id}
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
	 * 		"description":   "Group name",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "unixgroup",
	 * 		"description":   "Unix group name",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "unixid",
	 * 		"description":   "Unix ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "deptnumber",
	 * 		"description":   "Organization department ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "githuborgname",
	 * 		"description":   "Github organization name",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @param   Request $request
	 * @param   integer  $id
	 * @return  Response
	 */
	public function update(Request $request, $id)
	{
		$rules = [
			'membertype' => 'nullable|integer',
			'userrequestid' => 'nullable|integer',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = Member::findOrFail($id);

		if ($request->has('membertype'))
		{
			// Do not allow non-admins to remove himself as an owner.
			// This would just invite too many potential problems.
			if ($row->isManager()
			 && $request->input('membertype') != 2
			 && !auth()->user()->can('manage groups'))
			{
				if ($row->userid == auth()->user()->id)
				{
					return response()->json(['message' => trans('Cannot remove self as owner of a group')], 409);
				}
			}

			$row->membertype = $request->input('membertype');
		}

		if ($request->has('userrequestid'))
		{
			$row->userrequestid = $request->input('userrequestid');
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.create failed')], 500);
		}

		$row->api = route('api.groups.members.read', ['id' => $row->id]);
		$row->user;

		return new JsonResource($row);
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /api/groups/members/{id}
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
		$row = Member::findOrFail($id);

		if (!$row->isTrashed())
		{
			if (!$row->delete())
			{
				return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
			}
		}

		return response()->json(null, 204);
	}
}
