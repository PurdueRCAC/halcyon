<?php

namespace App\Modules\Groups\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Groups\Models\Group;
use App\Modules\Groups\Models\UnixGroup;
use App\Modules\Groups\Models\UnixGroupMember;
use App\Modules\Groups\Events\UnixGroupMemberCreating;
use App\Modules\Groups\Events\UnixGroupMemberCreated;
use App\Modules\Users\Models\User;
use App\Modules\Users\Models\UserUsername;

/**
 * Unix group members
 *
 * @apiUri    /unixgroups/members
 */
class UnixGroupMembersController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /unixgroups/members
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "search",
	 * 		"description":   "A word or phrase to search for.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "unixgroupid",
	 * 		"description":   "Unix Group ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "userid",
	 * 		"description":   "User ID",
	 * 		"required":      false,
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
	 * 			"default":   20
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
	 * @return Response
	 */
	public function index(Request $request)
	{
		$filters = array(
			'search'   => $request->input('search'),
			'unixgroupid'   => $request->input('groupid'),
			'userid' => $request->input('userid'),
			// Paging
			'limit'     => $request->input('limit', config('list_limit', 20)),
			// Sorting
			'order'     => $request->input('order', UnixGroupMember::$orderBy),
			'order_dir' => $request->input('order_dir', UnixGroupMember::$orderDir)
		);

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = UnixGroupMember::$orderDir;
		}

		$query = UnixGroupMember::query();

		if ($filters['search'])
		{
			$filters['search'] = strtolower((string)$filters['search']);

			$g = (new UnixGroupMember)->getTable();
			$u = (new User)->getTable();
			$uu = (new UserUsername)->getTable();

			$query->select($g . '.*', $u . '.name', $uu . '.username');
			$query->join($u, $u . '.id', $g . '.userid');
			$query->join($uu, $uu . '.userid', $g . '.userid');
			$query->where(function($where) use ($u, $uu, $filters)
			{
				$where->where($u . '.name', 'like', '%' . $filters['search'] . '%')
					->orWhere($uu . '.username', 'like', '%' . $filters['search'] . '%');
			});
		}

		if ($filters['unixgroupid'])
		{
			$query->where('unixgroupid', '=', $filters['unixgroupid']);
		}

		if ($filters['userid'])
		{
			$query->where('userid', '=', $filters['userid']);
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'])
			->appends(array_filter($filters));

		$rows->map(function($row, $key)
		{
			$row->api = route('api.unixgroups.members.read', ['id' => $row->id]);
		});

		return new ResourceCollection($rows);
	}

	/**
	 * Create a new entry
	 *
	 * @apiMethod POST
	 * @apiUri    /unixgroups/members
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "unixgroupid",
	 * 		"description":   "Unix group ID",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "userid",
	 * 		"description":   "User ID or username",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
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
	 * @apiResponse {
	 * 		"201": {
	 * 			"description": "Successful entry creation",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": {
	 * 						"id": 2,
	 * 						"unixgroupid": 1,
	 * 						"userid": 1234,
	 * 						"datetimecreated": "2021-02-03T18:36:02.000000Z",
	 * 						"datetimeremoved": null,
	 * 						"notice": 0,
	 * 						"username": "janedoe",
	 * 						"name": "Jane Doe",
	 * 						"api": "https://example.org/api/unixgroups/members/39372"
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"409": {
	 * 			"description": "Invalid data"
	 * 		}
	 * }
	 * @param   Request  $request
	 * @return Response
	 */
	public function create(Request $request)
	{
		$rules = [
			'unixgroupid' => 'required|integer',
			'userid' => 'required',
			'notice' => 'nullable|integer',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$userid = $request->input('userid');

		if (!is_numeric($userid))
		{
			$user = User::createFromUsername($userid);

			if ($user && $user->id)
			{
				$userid = $user->id;
			}
		}

		// Check to see if groups.unixgroup (base) is set
		$unixgroup = UnixGroup::findOrFail($request->input('unixgroupid'));

		$row = UnixGroupMember::query()
			->withTrashed()
			->where('unixgroupid', '=', $request->input('unixgroupid'))
			->where('userid', '=', $userid)
			->get()
			->first();

		// Set notice state
		$restore = false;
		if ($row)
		{
			if ($row->trashed())
			{
				$row->restore();

				$restore = true;
			}
			// Nothing to do, we are cancelling a removal
			$row->notice = 0;
		}
		else
		{
			$row = new UnixGroupMember;
			$row->unixgroupid = $request->input('unixgroupid');
			$row->userid = $userid;
			$row->notice = 2;
		}

		// Look up the current username of the user being granted access.
		$user = User::find($row->userid);

		if (!$user || !$user->id || $user->trashed())
		{
			return response()->json(['message' => trans('groups::groups.user not found')], 409);
		}

		if (!$unixgroup->group->isManager(auth()->user())
		 && !auth()->user()->can('manage groups'))
		{
			// Call other checks to see if the user is allowed to be added
			event($event = new UnixGroupMemberCreating($row));

			if ($event->restricted)
			{
				return response()->json(['message' => trans('groups::groups.user not authorized')], 403);
			}
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.create failed')], 500);
		}

		if ($restore)
		{
			event(new UnixGroupMemberCreated($row));
		}

		// Check to see if another unix group by the same name exists
		//
		// This is a catch for a loophole condition that allowed for multiple
		// unix groups by the same name. In such a case, only ONE should have
		// a unixgid.
		$altunixgroup = UnixGroup::query()
			->where('longname', '=', $unixgroup->longname)
			->where('id', '!=', $unixgroup->id)
			->first();

		if ($altunixgroup && (!$unixgroup->unixgid || !$altunixgroup->unixgid))
		{
			$altrow = UnixGroupMember::query()
				->withTrashed()
				->where('unixgroupid', '=', $altunixgroup->id)
				->where('userid', '=', $row->userid)
				->get()
				->first();

			if (!$altrow)
			{
				$altrow = new UnixGroupMember;
				$altrow->unixgroupid = $altunixgroup->id;
				$altrow->userid = $row->userid;
				$altrow->save();
			}
			else
			{
				if ($altrow->trashed())
				{
					$altrow->restore();
				}
				$altrow->notice = 0;
				$altrow->save();
			}
		}

		$row->api = route('api.unixgroups.members.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /unixgroups/members/{id}
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
	 * 		"200": {
	 * 			"description": "Successful entry read",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": {
	 * 						"id": 2,
	 * 						"unixgroupid": 1,
	 * 						"userid": 1234,
	 * 						"datetimecreated": "2021-02-03T18:36:02.000000Z",
	 * 						"datetimeremoved": null,
	 * 						"notice": 0,
	 * 						"username": "janedoe",
	 * 						"name": "Jane Doe",
	 * 						"api": "https://example.org/api/unixgroups/members/39372"
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		}
	 * }
	 * @param   integer $id
	 * @return Response
	 */
	public function read(int $id)
	{
		$row = UnixGroupMember::findOrFail($id);
		$row->api = route('api.unixgroups.members.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /unixgroups/members/{id}
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
	 * 		"name":          "notice",
	 * 		"description":   "Notice state",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"202": {
	 * 			"description": "Successful entry modification",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": {
	 * 						"id": 2,
	 * 						"unixgroupid": 1,
	 * 						"userid": 1234,
	 * 						"datetimecreated": "2021-02-03T18:36:02.000000Z",
	 * 						"datetimeremoved": null,
	 * 						"notice": 0,
	 * 						"username": "janedoe",
	 * 						"name": "Jane Doe",
	 * 						"api": "https://example.org/api/unixgroups/members/39372"
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		},
	 * 		"409": {
	 * 			"description": "Invalid data"
	 * 		}
	 * }
	 * @param   Request $request
	 * @param   integer $id
	 * @return  Response
	 */
	public function update(Request $request, int $id)
	{
		$rules = [
			'notice' => 'nullable|integer'
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = UnixGroupMember::findOrFail($id);

		if (!$row->unixgroup->group->isManager(auth()->user())
		 && !auth()->user()->can('manage groups'))
		{
			return response()->json(['message' => trans('groups::groups.user not authorized')], 403);
		}

		if ($request->has('notice'))
		{
			$row->notice = $request->input('notice');
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.create failed')], 500);
		}

		$row->api = route('api.unixgroups.members.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /unixgroups/members/{id}
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
	 * @param   integer $id
	 * @return  Response
	 */
	public function delete(int $id)
	{
		$row = UnixGroupMember::find($id);

		if ($row)
		{
			if (!$row->delete())
			{
				return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
			}
		}

		return response()->json(null, 204);
	}
}
