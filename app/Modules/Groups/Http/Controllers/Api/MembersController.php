<?php

namespace App\Modules\Groups\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Groups\Models\Group;
use App\Modules\Groups\Models\Member;
use App\Modules\Users\Models\UserUsername;
use App\Modules\Users\Models\User;

/**
 * Group Members
 *
 * @apiUri    /groups/members
 */
class MembersController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /groups/members
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "limit",
	 * 		"description":   "Number of result per page.",
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
	 * 		"name":          "groupid",
	 * 		"description":   "Group ID",
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
	 * 		"name":          "membertype",
	 * 		"description":   "Member type",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0,
	 * 			"enum": [
	 * 				"1 (members)",
	 * 				"2 (managers)",
	 * 				"3 (viewers)",
	 * 				"4 (pending)"
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "state",
	 * 		"description":   "State",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "active",
	 * 			"enum": [
	 * 				"active",
	 * 				"trashed",
	 * 				"all"
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "userrequestid",
	 * 		"description":   "User request ID",
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
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "order",
	 * 		"description":   "Field to sort results by.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "name",
	 * 			"enum": [
	 * 				"id",
	 * 				"groupid",
	 * 				"userid",
	 * 				"membertype",
	 * 				"datecreated",
	 * 				"dateremoved"
	 * 			]
	 * 		}
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
	 * @return  ResourceCollection
	 */
	public function index(Request $request)
	{
		$filters = array(
			'search'        => $request->input('search', ''),
			'groupid'       => $request->input('groupid', 0),
			'userid'        => $request->input('userid', 0),
			'userrequestid' => $request->input('userrequestid', 0),
			'membertype'    => $request->input('membertype', 0),
			'state'         => $request->input('state', 'active'),
			// Paging
			'limit'     => $request->input('limit', config('list_limit', 20)),
			'page'      => $request->input('page', 1),
			// Sorting
			'order'     => $request->input('order', Member::$orderBy),
			'order_dir' => $request->input('order_dir', Member::$orderDir)
		);

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Member::$orderDir;
		}

		/*$query = Member::query()
			->with('user');

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
		}*/
		$u = (new User)->getTable();
		$uu = (new UserUsername)->getTable();
		$m = (new Member)->getTable();

		$query = Member::query()
			->join($uu, $uu . '.userid', $m . '.userid')
			->join($u, $u . '.id', $uu . '.userid')
			->select($m . '.*', $u . '.name')
			->with('user')
			->with('type');

		if ($filters['search'])
		{
			$filters['search'] = strtolower((string)$filters['search']);

			$query->where($u . '.name', 'like', '%' . $filters['search'] . '%');
		}

		if ($filters['groupid'])
		{
			$query->where($m . '.groupid', '=', $filters['groupid']);
		}

		if ($filters['userid'])
		{
			$query->where($m . '.userid', '=', $filters['userid']);
		}

		if ($filters['membertype'])
		{
			$query->where($m . '.membertype', '=', $filters['membertype']);
		}

		if ($filters['userrequestid'])
		{
			$query->where($m . '.userrequestid', '=', $filters['userrequestid']);
		}

		if ($filters['state'] == 'active')
		{
			$query->withTrashed()
				->whereNull($uu . '.dateremoved')
				->whereNull($m . '.dateremoved');
		}
		elseif ($filters['state'] == 'trashed')
		{
			//$query->onlyTrashed();
			$query->withTrashed()
				->where(function($where) use ($uu, $m)
				{
					$where->whereNotNull($uu . '.dateremoved')
						->orWhereNotNull($m . '.dateremoved');
				});
		}
		else
		{
			$query->withTrashed();
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page'])
			->appends(array_filter($filters));

		$rows->each(function ($item, $key)
		{
			$item->api = route('api.groups.members.read', ['id' => $item->id]);
			$item->user->setHidden(['api_token']);
		});

		return new ResourceCollection($rows);
	}

	/**
	 * Create a new entry
	 *
	 * @apiMethod POST
	 * @apiUri    /groups/members
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "groupid",
	 * 		"description":   "Group ID",
	 * 		"required":      true,
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
	 * 		"name":          "membertype",
	 * 		"description":   "Member type",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "userrequestid",
	 * 		"description":   "User request ID",
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
	 * 						"id": 43,
	 * 						"groupid": 1,
	 * 						"userid": 1234,
	 * 						"userrequestid": 0,
	 * 						"membertype": 2,
	 * 						"owner": 1,
	 * 						"datecreated": "2011-03-08T13:46:42.000000Z",
	 * 						"dateremoved": null,
	 * 						"datelastseen": null,
	 * 						"notice": 0,
	 * 						"api": "https://example.org/api/groups/members/43818",
	 * 						"user": {
	 * 							"id": 1234,
	 * 							"name": "Jane Doe",
	 * 							"username": "janedoe",
	 * 							"unixid": 43674,
	 * 							"datecreated": "2021-06-17T17:07:05.000000Z",
	 * 							"dateremoved": null
	 * 						}
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"409": {
	 * 			"description": "Invalid data"
	 * 		}
	 * }
	 * @param   Request  $request
	 * @return  JsonResource|JsonResponse
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
			$user = User::createFromUsername($userid);

			if ($user->id)
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
		elseif ($row->trashed())
		{
			$row->restore();
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

		$row->membertype = $request->input('membertype', 1);

		if (!$row->isPending() && !auth()->user()->can('manage groups'))
		{
			$rowuser = Member::query()
				->where('userid', '=', auth()->user()->id)
				->where('groupid', '=', $row->groupid)
				->whereIsManager()
				->first();

			if (!$rowuser)
			{
				return response()->json(['message' => trans('Unauthorized to create memberships')], 403);
			}
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

		// Notify other managers of this person being made a manager
		if ($row->isManager())
		{
			$row->notice = Member::MEMBERSHIP_AUTHORIZED;
		}

		// Do we have any owners?
		// If not, there's no one else to notify
		if (count($row->group->managers) == 0)
		{
			$row->notice = Member::NO_NOTICE;
		}

		$row->userrequestid = $request->input('userrequestid', 0);

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.create failed')], 500);
		}

		$row->api = route('api.groups.members.read', ['id' => $row->id]);
		$row->user->setHidden(['api_token']);

		return new JsonResource($row);
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /groups/members/{id}
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
	 * 						"id": 43,
	 * 						"groupid": 1,
	 * 						"userid": 1234,
	 * 						"userrequestid": 0,
	 * 						"membertype": 2,
	 * 						"owner": 1,
	 * 						"datecreated": "2011-03-08T13:46:42.000000Z",
	 * 						"dateremoved": null,
	 * 						"datelastseen": null,
	 * 						"notice": 0,
	 * 						"api": "https://example.org/api/groups/members/43818",
	 * 						"user": {
	 * 							"id": 1234,
	 * 							"name": "Jane Doe",
	 * 							"username": "janedoe",
	 * 							"unixid": 43674,
	 * 							"datecreated": "2021-06-17T17:07:05.000000Z",
	 * 							"dateremoved": null
	 * 						}
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		}
	 * }
	 * @param  int  $id
	 * @return JsonResource
	 */
	public function read(int $id)
	{
		$row = Member::findOrFail($id);
		$row->api = route('api.groups.members.read', ['id' => $row->id]);
		$row->user->setHidden(['api_token']);

		return new JsonResource($row);
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /groups/members/{id}
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
	 * 		"name":          "membertype",
	 * 		"description":   "Member type",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "userrequestid",
	 * 		"description":   "User request ID",
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
	 * 						"id": 43,
	 * 						"groupid": 1,
	 * 						"userid": 1234,
	 * 						"userrequestid": 0,
	 * 						"membertype": 2,
	 * 						"owner": 1,
	 * 						"datecreated": "2011-03-08T13:46:42.000000Z",
	 * 						"dateremoved": null,
	 * 						"datelastseen": null,
	 * 						"notice": 0,
	 * 						"api": "https://example.org/api/groups/members/43818",
	 * 						"user": {
	 * 							"id": 1234,
	 * 							"name": "Jane Doe",
	 * 							"username": "janedoe",
	 * 							"unixid": 43674,
	 * 							"datecreated": "2021-06-17T17:07:05.000000Z",
	 * 							"dateremoved": null
	 * 						}
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
	 * @param   int  $id
	 * @return  JsonResource|JsonResponse
	 */
	public function update(Request $request, int $id)
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

		if (!auth()->user()->can('manage groups'))
		{
			$rowuser = Member::query()
				->where('userid', '=', auth()->user()->id)
				->where('groupid', '=', $row->groupid)
				->whereIsManager()
				->first();

			if (!$rowuser)
			{
				return response()->json(['message' => trans('Unauthorized to alter memberships')], 403);
			}
		}

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
					return response()->json(['message' => trans('groups::groups.error.cannot remove self as owner')], 409);
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
		$row->user->setHidden(['api_token']);

		return new JsonResource($row);
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /groups/members/{id}
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
	 * @param   int  $id
	 * @return  JsonResponse
	 */
	public function delete(int $id)
	{
		$row = Member::find($id);

		if ($row && !$row->trashed())
		{
			if (!$row->delete())
			{
				return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
			}
		}

		return response()->json(null, 204);
	}
}
