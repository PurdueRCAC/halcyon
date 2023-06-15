<?php

namespace App\Modules\ContactReports\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Validator;
use App\Modules\Groups\Models\Member as GroupUser;
use Carbon\Carbon;

/**
 * Follow Groups
 *
 * @apiUri    /contactreports/followgroups
 */
class FollowGroupsController extends Controller
{
	/**
	 * Display a listing of contact reports followgroups
	 *
	 * @apiMethod GET
	 * @apiUri    /contactreports/followgroups
	 * @apiAuthorization  true
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
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "datecreated",
	 * 			"enum": [
	 * 				"id",
	 * 				"userid",
	 * 				"datecreated",
	 * 				"targetuserid"
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "order_dir",
	 * 		"description":   "Direction to sort results by.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "desc",
	 * 			"enum": [
	 * 				"asc",
	 * 				"desc"
	 * 			]
	 * 		}
	 * }
	 * @param  Request $request
	 * @return ResourceCollection
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = array(
			'groupid'   => 0,
			'userid'    => 0,
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

		$rows->each(function($item, $key)
		{
			$item->api = route('api.contactreports.followgroups.read', ['id' => $item->id]);
		});

		return new ResourceCollection($rows);
	}

	/**
	 * Create a group following
	 *
	 * @apiMethod POST
	 * @apiUri    /contactreports/followgroups
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "groupid",
	 * 		"description":   "ID of group being followed",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "userid",
	 * 		"description":   "User ID of follower",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   "Current user's ID"
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"201": {
	 * 			"description": "Successful entry creation",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": {
	 * 						"id": 1,
	 * 						"groupid": 20,
	 * 						"userid": 1234,
	 * 						"userrequestid": 0,
	 * 						"membertype": 10,
	 * 						"owner": 0,
	 * 						"datecreated": "2021-09-03T05:44:38.000000Z",
	 * 						"dateremoved": null,
	 * 						"datelastseen": null,
	 * 						"notice": 0,
	 * 						"api": "https://example.org/api/contactreports/followgroups/1"
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"409": {
	 * 			"description": "Invalid data"
	 * 		}
	 * }
	 * @param   Request  $request
	 * @return  JsonResponse|JsonResource
	 */
	public function create(Request $request)
	{
		$rules = [
			'groupid' => 'required|integer|min:1',
			'userid'  => 'nullable|integer',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = new Follow();
		$row->groupid = $request->input('groupid');
		$row->userid = $request->input('userid', auth()->user() ? auth()->user()->id : 0);

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.create failed')], 500);
		}

		$row->api = route('api.contactreports.followgroups.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Retrieve a group following record
	 *
	 * @apiMethod GET
	 * @apiUri    /contactreports/followgroups/{id}
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
	 * 						"id": 1,
	 * 						"groupid": 20,
	 * 						"userid": 1234,
	 * 						"userrequestid": 0,
	 * 						"membertype": 10,
	 * 						"owner": 0,
	 * 						"datecreated": "2021-09-03T05:44:38.000000Z",
	 * 						"dateremoved": null,
	 * 						"datelastseen": null,
	 * 						"notice": 0,
	 * 						"api": "https://example.org/api/contactreports/followgroups/1"
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
	public function read($id)
	{
		$row = GroupUser::findOrFail((int)$id);

		$row->api = route('api.contactreports.followgroups.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Update a group following
	 *
	 * @apiMethod PUT
	 * @apiUri    /contactreports/followgroups/{id}
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
	 * 		"name":          "groupid",
	 * 		"description":   "ID of group being followed",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "userid",
	 * 		"description":   "ID of user following the group",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"204": {
	 * 			"description": "Successful entry modification",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": {
	 * 						"id": 1,
	 * 						"groupid": 20,
	 * 						"userid": 1234,
	 * 						"userrequestid": 0,
	 * 						"membertype": 10,
	 * 						"owner": 0,
	 * 						"datecreated": "2021-09-03T05:44:38.000000Z",
	 * 						"dateremoved": null,
	 * 						"datelastseen": null,
	 * 						"notice": 0,
	 * 						"api": "https://example.org/api/contactreports/followgroups/1"
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
	 * @param   Request  $request
	 * @param   int  $id
	 * @return  JsonResponse|JsonResource
	 */
	public function update(Request $request, $id)
	{
		$rules = [
			'groupid' => 'nullable|integer',
			'userid'  => 'nullable|integer',
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

			if (!$row->following)
			{
				return response()->json(['message' => 'No group found for specified ID.'], 415);
			}
		}
		if ($request->has('userid'))
		{
			$row->userid = $request->input('userid');

			if (!$row->follower)
			{
				return response()->json(['message' => 'No user found for specified ID.'], 415);
			}
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.update failed')], 500);
		}

		$row->api = route('api.contactreports.followgroups.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Delete a group following
	 *
	 * @apiMethod DELETE
	 * @apiUri    /contactreports/followgroups/{id}
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
