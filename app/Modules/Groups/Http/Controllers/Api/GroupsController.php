<?php

namespace App\Modules\Groups\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Groups\Models\Group;
use App\Modules\Groups\Models\Member;
use App\Modules\Groups\Http\Resources\GroupResource;
use App\Modules\Groups\Http\Resources\GroupResourceCollection;
use App\Modules\Users\Models\User;
use Carbon\Carbon;

/**
 * Groups
 *
 * @apiUri    /api/groups
 */
class GroupsController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /api/groups
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
	 * 		"name":          "deptnumber",
	 * 		"description":   "Organization department ID",
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
	 * @return Response
	 */
	public function index(Request $request)
	{
		$filters = array(
			'search'   => $request->input('search', ''),
			'searchuser' => $request->input('searchuser', ''),
			'owneruserid'   => $request->input('owneruserid', 0),
			'unixgroup'   => $request->input('unixgroup', ''),
			'unixid'   => $request->input('unixid', 0),
			'deptnumber'   => $request->input('deptnumber', 0),
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
			->select($g . '.*');

		if ($filters['search'])
		{
			$filters['search'] = strtolower((string)$filters['search']);

			$query->where($g . '.name', 'like', '%' . $filters['search'] . '%');
		}

		if ($filters['searchuser'])
		{
			$gu = (new Member)->getTable();
			$u = (new User)->getTable();

			$filters['searchuser'] = strtolower((string)$filters['searchuser']);

			$query->join($gu, $gu . '.groupid', $g . '.id');
			$query->join($u, $u . '.id', $gu . '.userid');

			$query->where($gu . '.membertype', '=', 2);
			$query->where(function($where) use ($g, $u, $filters)
			{
				$where->where($g . '.name', 'like', '%' . $filters['searchuser'] . '%')
					->orWhere(function($users) use ($u, $filters)
					{
						$users->where($u . '.name', 'like', '%' . $filters['searchuser'] . '%')
							->orWhere($u . '.name', 'like', $filters['searchuser'] . '%')
							->orWhere($u . '.name', 'like', '%' . $filters['searchuser'])
							->orWhere($u . '.username', 'like', $filters['searchuser'] . '%');
					});
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

		if ($filters['deptnumber'])
		{
			$query->where($g . '.deptnumber', '=', $filters['deptnumber']);
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

		return new GroupResourcecollection($rows);
	}

	/**
	 * Create a new entry
	 *
	 * @apiMethod POST
	 * @apiUri    /api/groups
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
	 * @return Response
	 */
	public function create(Request $request)
	{
		$request->validate([
			'name' => 'required|max:255',
			'unixgroup' => 'nullable|max:10',
		]);

		$name = $request->input('name');

		$exists = Group::findByName($name);

		if ($exists)
		{
			return response()->json(['message' => trans('groups::groups.name already exists', ['name' => $name])], 415);
		}

		$row = new Group;
		$row->fill($request->all());
		//$row->datetimecreated = Carbon::now()->toDateTimeString();

		// Verify UNIX group is sane - this is just a first pass,
		// would still need to make sure this is not a duplicate anywhere, etc
		if ($row->unixgroup)
		{
			if (!preg_match('/^[a-z][a-z0-9\-]{0,8}[a-z0-9]$/', $row->unixgroup))
			{
				return response()->json(['message' => trans('Field `unixgroup` not in valid format')], 415);
			}

			$exists = Group::findByUnixgroup($row->unixgroup);

			// Check for a duplicate
			if ($exists)
			{
				return response()->json(['message' => trans('`unixgroup` ' . $dataobj->unixgroup . ' already exists')], 409);
			}

			try
			{
				// Check to make sure this base name doesn't exist elsewhere
				$config = config('ldap.rcac_group', []);

				$ldap = app('ldap')
					->addProvider($config, 'rcac_group')
					->connect('rcac_group');

				// Performing a query.
				$rows = $ldap->search()
					->where('cn', '=', $row->unixgroup)
					->get();

				if ($rows > 0)
				{
					//return 409;
				}
			}
			catch (\Exception $e)
			{
			}
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('messages.create failed')], 500);
		}

		$member = new Member;
		$member->groupid = $row->id;
		$member->userid = auth()->user()->id;
		$member->membertype = 2;

		if (!$member->save())
		{
			return response()->json(['message' => trans('messages.create failed')], 500);
		}

		return new GroupResource($row);
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /api/groups/{id}
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @return Response
	 */
	public function read($id)
	{
		$row = Group::findOrFail($id);

		return new GroupResource($row);
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /api/groups/{id}
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
	 * @param   Request $request
	 * @return  Response
	 */
	public function update(Request $request, $id)
	{
		$request->validate([
			'name' => 'nullable|max:255',
			'unixgroup' => 'nullable|max:10',
		]);

		$row = Group::findOrFail($id);
		//$row->update($request->all());

		// Verify UNIX group is sane - this is just a first pass,
		// would still need to make sure this is not a duplicate anywhere, etc
		$unixgroup = $request->input('unixgroup');

		if ($unixgroup)
		{
			if (!preg_match('/^[a-z][a-z0-9\-]{0,8}[a-z0-9]$/', $unixgroup))
			{
				return response()->json(['message' => trans('Field `unixgroup` not in valid format')], 415);
			}

			$exists = Group::findByUnixgroup($unixgroup);

			// Check for a duplicate
			if ($exists && $exists->id != $row->id)
			{
				return response()->json(['message' => trans('`unixgroup` ' . $dataobj->unixgroup . ' already exists')], 409);
			}

			try
			{
				// Check to make sure this base name doesn't exist elsewhere
				$config = config('ldap.rcac_group', []);

				$ldap = app('ldap')
					->addProvider($config, 'rcac_group')
					->connect('rcac_group');

				// Performing a query.
				$rows = $ldap->search()
					->where('cn', '=', $unixgroup)
					->get();

				if ($rows > 0)
				{
					//return 409;
				}
			}
			catch (\Exception $e)
			{
			}
		}

		if ($request->has('name'))
		{
			$name = $request->input('name');

			$exists = Group::findByName($name);

			if ($exists)
			{
				return response()->json(['message' => trans('groups::groups.name already exists', ['name' => $name])], 415);
			}

			$row->name = $name;
		}

		if ($request->has('deptnumber'))
		{
			$row->deptnumber = $request->input('deptnumber');
		}

		if ($request->has('githuborgname'))
		{
			$row->githuborgname = $request->input('githuborgname');
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('messages.create failed')], 500);
		}

		return new GroupResource($row);
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /api/groups/{id}
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @return  Response
	 */
	public function delete($id)
	{
		$row = Group::findOrFail($id);

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
