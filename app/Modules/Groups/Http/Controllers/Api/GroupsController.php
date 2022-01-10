<?php

namespace App\Modules\Groups\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Modules\Groups\Models\Group;
use App\Modules\Groups\Models\Member;
use App\Modules\Groups\Models\UnixGroup;
use App\Modules\Groups\Http\Resources\GroupResource;
use App\Modules\Groups\Http\Resources\GroupResourceCollection;
use App\Modules\Groups\Events\UnixGroupFetch;
use App\Modules\Users\Models\User;
use App\Modules\Users\Models\UserUsername;
use Carbon\Carbon;

/**
 * Groups
 *
 * @apiUri    /groups
 */
class GroupsController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /groups
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "owneruserid",
	 * 		"description":   "Owner user ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "state",
	 * 		"description":   "Entry state",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "active",
	 * 			"enum": [
	 * 				"active",
	 * 				"trashed",
	 * 				"*"
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "unixgroup",
	 * 		"description":   "Unix group name",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "unixid",
	 * 		"description":   "Unix ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "datetimecreated",
	 * 		"description":   "Created on or after the given timestamp",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "date",
	 * 			"default":   null
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
	 * 				"datetimecreated"
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
	 * @param   Request  $request
	 * @return Response
	 */
	public function index(Request $request)
	{
		$filters = array(
			'state'   => $request->input('state', 'active'),
			'search'   => $request->input('search', ''),
			'searchuser' => $request->input('searchuser', ''),
			'owneruserid'   => $request->input('owneruserid', 0),
			'unixgroup'   => $request->input('unixgroup', ''),
			'unixid'   => $request->input('unixid', 0),
			'datetimecreated'   => $request->input('datetimecreated'),
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

		$g = (new Group)->getTable();

		$query = Group::query()
			->with('departments')
			->select(DB::raw('DISTINCT ' . $g . '.id, ' . $g . '.name, ' . $g . '.owneruserid, ' . $g . '.unixgroup, ' . $g . '.unixid, ' . $g . '.githuborgname, ' . $g . '.datetimecreated, ' . $g . '.datetimeremoved'));

		if ($filters['state'] == 'trashed')
		{
			$query->onlyTrashed();
		}
		elseif ($filters['state'] == '*')
		{
			$query->withTrashed();
		}

		if ($filters['search'])
		{
			if (is_numeric($filters['search']))
			{
				$query->where($g . '.id', '=', (int)$filters['search']);
			}
			else
			{
				$search = (string)$filters['search'];
				$search = trim($search);
				$search = preg_replace('/ +/', ' ', $search);

				// Skip matches on trailing "group" or we'll return a billion results
				if (preg_match('/Group$/i', $search))
				{
					$search = preg_replace('/Group$/i', '', $search);
				}

				if (!empty($filters['searchuser']))
				{
					//$filters['searchuser'] = $search;
					$query->where(function($where) use ($g, $search)
					{
						$where->where($g . '.name', 'like', $search . '%')
							->orWhere($g . '.name', 'like', '%' . $search . '%');
					});
				}
				else
				{
					$gu = (new Member)->getTable();
					$u = (new User)->getTable();
					$uu = (new UserUsername)->getTable();

					$query->leftJoin($gu, $gu . '.groupid', $g . '.id');
					$query->leftJoin($u, $u . '.id', $gu . '.userid');
					$query->leftJoin($uu, $uu . '.userid', $u . '.id');

					//$query->where($gu . '.membertype', '=', 2);
					$query->where(function($where) use ($g, $gu, $u, $uu, $search)
					{
						$where->where($g . '.name', 'like', $search . '%')
							->orWhere($g . '.name', 'like', '%' . $search . '%')
							->orWhere(function($userswhere) use ($gu, $u, $uu, $search)
							{
								$userswhere
									->where($gu . '.membertype', '=', 2)
									->where(function($users) use ($u, $uu, $search)
									{
										$users->where($uu . '.username', '=', $search)
											->orWhere($uu . '.username', 'like', $search . '%')
											->orWhere($uu . '.username', 'like', '%' . $search . '%')
											->orWhere($u . '.name', 'like', '%' . $search . '%')
											->orWhere($u . '.name', 'like', $search . '%')
											->orWhere($u . '.name', 'like', '%' . $search);
									});
							});
					});
				}
			}
		}

		if ($filters['searchuser'])
		{
			$gu = (new Member)->getTable();
			$u = (new User)->getTable();
			$uu = (new UserUsername)->getTable();

			$filters['searchuser'] = strtolower((string)$filters['searchuser']);

			$query->join($gu, $gu . '.groupid', $g . '.id');
			$query->join($u, $u . '.id', $gu . '.userid');
			$query->join($uu, $uu . '.userid', $u . '.id');

			$query->where($gu . '.membertype', '=', 2);
			$query->where(function($where) use ($g, $u, $uu, $filters)
			{
				$where->where($uu . '.username', '=', $filters['searchuser'])
					->orWhere($uu . '.username', 'like', $filters['searchuser'] . '%')
					->orWhere($uu . '.username', 'like', '%' . $filters['searchuser'] . '%')
					->orWhere($u . '.name', 'like', '%' . $filters['searchuser'] . '%')
					->orWhere($u . '.name', 'like', $filters['searchuser'] . '%')
					->orWhere($u . '.name', 'like', '%' . $filters['searchuser']);
				/*$where->where($g . '.name', 'like', '%' . $filters['searchuser'] . '%')
					->orWhere(function($users) use ($u, $uu, $filters)
					{
						$users->where($uu . '.username', '=', $filters['searchuser'])
							->orWhere($uu . '.username', 'like', $filters['searchuser'] . '%')
							->orWhere($uu . '.username', 'like', '%' . $filters['searchuser'] . '%')
							->orWhere($u . '.name', 'like', '%' . $filters['searchuser'] . '%')
							->orWhere($u . '.name', 'like', $filters['searchuser'] . '%')
							->orWhere($u . '.name', 'like', '%' . $filters['searchuser']);
					});*/
			});
		}

		if ($filters['owneruserid'])
		{
			$query->where($g . '.owneruserid', '=', $filters['owneruserid']);
		}

		if ($filters['unixgroup'])
		{
			$query->where($g . '.unixgroup', '=', $filters['unixgroup']);
		}

		if ($filters['unixid'])
		{
			$query->where($g . '.unixid', '=', $filters['unixid']);
		}

		if ($filters['datetimecreated'])
		{
			$query->where($g . '.datetimecreated', '>=', $filters['datetimecreated']);
		}

		/*if (auth()->user() && auth()->user()->can('manage groups'))
		{
			$query->withCount('members');
		}*/

		$rows = $query
			//->with('motd')
			->orderBy($g . '.' . $filters['order'], $filters['order_dir'])
			->paginate($filters['limit'])
			->appends(array_filter($filters));

		return new GroupResourceCollection($rows);
	}

	/**
	 * Create a new entry
	 *
	 * @apiMethod POST
	 * @apiUri    /groups
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "name",
	 * 		"description":   "Group name",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "unixgroup",
	 * 		"description":   "Unix group name",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "unixid",
	 * 		"description":   "Unix ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "deptnumber",
	 * 		"description":   "Organization department ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "githuborgname",
	 * 		"description":   "Github organization name",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"201": {
	 * 			"description": "Successful entry creation",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": {
	 * 						"id": 1,
	 * 						"name": "My Research Group",
	 * 						"owneruserid": 0,
	 * 						"unixgroup": "mrg",
	 * 						"unixid": 0,
	 * 						"githuborgname": "",
	 * 						"datetimecreated": "2021-01-07T14:13:17.000000Z",
	 * 						"datetimeremoved": null,
	 * 						"api": "https://example.org/api/groups/1",
	 * 						"department": [
	 * 							{
	 * 								"id": 3541,
	 * 								"groupid": 1,
	 * 								"collegedeptid": 313,
	 * 								"percentage": 100,
	 * 								"api": "https://example.org/api/groups/departments/3541"
	 * 							}
	 * 						]
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"409": {
	 * 			"description": "Invalid data"
	 * 		}
	 * }
	 * @param   Request  $request
	 * @return  GroupResponse
	 */
	public function create(Request $request)
	{
		$rules = [
			'name' => 'required|string|max:48',
			'unixgroup' => 'nullable|integer|max:10',
			'userid' => 'nullable|integer'
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$name = $request->input('name');
		$userid = $request->input('userid', auth()->user()->id);

		$exists = Group::findByName($name);

		if ($exists)
		{
			return response()->json(['message' => trans('groups::groups.error.group name already exists', ['name' => $name])], 409);
		}

		$row = new Group;
		$row->name = $request->input('name');

		// Verify UNIX group is sane - this is just a first pass,
		// would still need to make sure this is not a duplicate anywhere, etc
		if ($request->has('unixgroup'))
		{
			$row->unixgroup = $request->input('unixgroup');

			if (!preg_match('/^[a-z][a-z0-9\-]{0,8}[a-z0-9]$/', $row->unixgroup))
			{
				return response()->json(['message' => trans('groups::groups.error.unixgroup invalid format')], 409);
			}

			$exists = Group::findByUnixgroup($row->unixgroup);

			if (!$exists)
			{
				$exists = UnixGroup::findByLongname($row->unixgroup);
			}

			// Check for a duplicate
			if ($exists)
			{
				return response()->json(['message' => trans('groups::groups.error.unixgroup name already exists', ['name' => $row->unixgroup])], 409);
			}

			// Check to make sure this base name doesn't exist elsewhere
			event($event = new UnixGroupFetch($row->unixgroup));

			$rows = $event->results;

			if (count($rows) > 0)
			{
				return response()->json(['message' => trans('groups::groups.error.unixgroup name already exists', ['name' => $row->unixgroup])], 409);
			}
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.create failed')], 500);
		}

		$member = new Member;
		$member->groupid = $row->id;
		$member->userid = $userid;
		$member->membertype = 2;

		if (!$member->save())
		{
			return response()->json(['message' => trans('global.messages.create failed')], 500);
		}

		if ($request->has('deptnumber'))
		{
			$row->addDepartment($request->input('deptnumber'));
		}

		return new GroupResource($row);
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /groups/{id}
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
	 * 						"name": "My Research Group",
	 * 						"owneruserid": 0,
	 * 						"unixgroup": "mrg",
	 * 						"unixid": 0,
	 * 						"githuborgname": "",
	 * 						"datetimecreated": "2021-01-07T14:13:17.000000Z",
	 * 						"datetimeremoved": null,
	 * 						"api": "https://example.org/api/groups/1",
	 * 						"department": [
	 * 							{
	 * 								"id": 3541,
	 * 								"groupid": 1,
	 * 								"collegedeptid": 313,
	 * 								"percentage": 100,
	 * 								"api": "https://example.org/api/groups/departments/3541"
	 * 							}
	 * 						]
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		}
	 * }
	 * @param  integer  $id
	 * @return GroupResponse
	 */
	public function read(int $id)
	{
		$row = Group::findOrFail($id);

		return new GroupResource($row);
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /groups/{id}
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
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "unixgroup",
	 * 		"description":   "Unix group name",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "unixid",
	 * 		"description":   "Unix ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "deptnumber",
	 * 		"description":   "Organization department ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "githuborgname",
	 * 		"description":   "Github organization name",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"202": {
	 * 			"description": "Successful entry modification",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": {
	 * 						"id": 1,
	 * 						"name": "My Research Group",
	 * 						"owneruserid": 0,
	 * 						"unixgroup": "mrg",
	 * 						"unixid": 0,
	 * 						"githuborgname": "",
	 * 						"datetimecreated": "2021-01-07T14:13:17.000000Z",
	 * 						"datetimeremoved": null,
	 * 						"api": "https://example.org/api/groups/1",
	 * 						"department": [
	 * 							{
	 * 								"id": 3541,
	 * 								"groupid": 1,
	 * 								"collegedeptid": 313,
	 * 								"percentage": 100,
	 * 								"api": "https://example.org/api/groups/departments/3541"
	 * 							}
	 * 						]
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
			'name' => 'nullable|max:48',
			'unixgroup' => 'nullable|max:10',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = Group::findOrFail($id);
		//$row->update($request->all());

		if ($request->has('unixgroup'))
		{
			// Verify UNIX group is sane - this is just a first pass,
			// would still need to make sure this is not a duplicate anywhere, etc
			$unixgroup = $request->input('unixgroup');

			if ($unixgroup && $unixgroup != $row->unixgroup)
			{
				if (!preg_match('/^[a-z][a-z0-9\-]{0,8}[a-z0-9]$/', $unixgroup))
				{
					return response()->json(['message' => trans('groups::groups.error.name is incorrectly formatted')], 415);
				}

				$exists = Group::findByUnixgroup($unixgroup);

				if (!$exists)
				{
					$exists = UnixGroup::findByLongname($unixgroup);
				}

				// Check for a duplicate
				if ($exists && $exists->id != $row->id)
				{
					return response()->json(['message' => trans('groups::groups.error.unixgroup name already exists', ['name' => $unixgroup])], 409);
				}

				// Check to make sure this base name doesn't exist elsewhere
				event($event = new UnixGroupFetch($unixgroup));

				$rows = $event->results;

				if (count($rows) > 0)
				{
					return response()->json(['message' => trans('groups::groups.error.unixgroup name already exists', ['name' => $unixgroup])], 409);
				}

				$row->unixgroup = $unixgroup;
			}
		}

		if ($request->has('name'))
		{
			$name = $request->input('name');

			$exists = Group::findByName($name);

			if ($exists)
			{
				return response()->json(['message' => trans('groups::groups.error.name already exists', ['name' => $name])], 415);
			}

			$row->name = $name;
		}

		if ($request->has('githuborgname'))
		{
			$row->githuborgname = $request->input('githuborgname');
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.create failed')], 500);
		}

		if ($request->has('deptnumber'))
		{
			$row->addDepartment($request->input('deptnumber'));
		}

		return new GroupResource($row);
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /groups/{id}
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
	 * @param   integer  $id
	 * @return  Response
	 */
	public function delete(int $id)
	{
		$row = Group::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
		}

		return response()->json(null, 204);
	}
}
