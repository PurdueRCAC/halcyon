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

class GroupsController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /groups
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
	 * @apiUri    /groups
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
	 * @apiUri    /groups/{id}
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
		$row = Group::findOrFail($id);

		return new GroupResource($row);
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /groups/{id}
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
	 * @apiUri    /groups/{id}
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
