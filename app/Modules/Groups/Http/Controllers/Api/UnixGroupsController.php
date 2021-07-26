<?php

namespace App\Modules\Groups\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Modules\Groups\Models\Group;
use App\Modules\Groups\Models\UnixGroup;
use App\Modules\Groups\Http\Resources\UnixGroupResource;
use App\Modules\Groups\Http\Resources\UnixGroupResourceCollection;
use App\Modules\Groups\Events\UnixGroupFetch;

/**
 * Unix groups
 *
 * @apiUri    /api/unixgroups
 */
class UnixGroupsController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /api/unixgroups
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
			'groupid'   => $request->input('groupid'),
			// Paging
			'limit'     => $request->input('limit', config('list_limit', 20)),
			// Sorting
			'order'     => $request->input('order', UnixGroup::$orderBy),
			'order_dir' => $request->input('order_dir', UnixGroup::$orderDir)
		);

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = UnixGroup::$orderDir;
		}

		$query = UnixGroup::query();

		if ($filters['search'])
		{
			$filters['search'] = strtolower((string)$filters['search']);

			$query->where(function($where)
			{
				$where->where('longname', 'like', '%' . $filters['search'] . '%')
					->orWhere('shortname', 'like', '%' . $filters['search'] . '%');
			});
		}

		if ($filters['groupid'])
		{
			$query->where('groupid', '=', $filters['groupid']);
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'])
			->appends(array_filter($filters));

		return new UnixGroupResourceCollection($rows);
	}

	/**
	 * Create a new entry
	 *
	 * @apiMethod POST
	 * @apiUri    /api/unixgroups
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "name",
	 * 		"description":   "Department name",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "groupid",
	 * 		"description":   "Group ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "longname",
	 * 		"description":   "Long name",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 32
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "shortname",
	 * 		"description":   "Short name",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 8
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "unixgid",
	 * 		"description":   "Unix group system ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @param   Request  $request
	 * @return Response
	 */
	public function create(Request $request)
	{
		$rules = [
			'groupid' => 'required|integer',
			'longname' => 'nullable|string|max:32',
			'shortname' => 'nullable|string|max:8',
			'unixgid' => 'nullable|integer'
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		// Check to see if groups.unixgroup (base) is set
		$group = Group::findOrFail($request->input('groupid'));

		$base = '';
		$name = $request->input('longname');

		if ($group->id != 1)
		{
			if (!$group->unixgroup)
			{
				return response()->json(['message' => trans('groups::groups.base unixgroup is not set')], 400);
			}

			$base = $group->unixgroup;

			// Check if the name has base as prefix already
			// If it does, filter it out
			if (preg_match('/^'. $base . '-/', $name))
			{
				$name = preg_replace('/^' . $base . '-/', '', $name);
			}

			// Now check format for group name entered
			// Only allow lowercase and numeric
			if (!preg_match('/^$/', $name)
			 && !preg_match('/^[a-z0-9][a-z0-9\-]*[a-z0-9]+$/', $name))
			{
				return response()->json(['message' => trans('groups::groups.name is incorrectly formatted')], 415);
			}

			/*if ($group->unixgroup == $name)
			{
				$name = '';
			}*/

			event($event = new UnixGroupFetch($base));

			$rows = $event->results;

			/*if (count($rows) > 0)
			{
				// group name exists in LDAP
				return response()->json(['message' => trans('groups::groups.LDAP entry already exists for unix group')], 409);
			}*/

			// Set the base for groups and add a '-' if the name is empty 
			if (!preg_match('/^$/', $name))
			{
				$base = $base . '-';
			}
		}
		else
		{
			// This is a special group without a base name, so check to make sure the requested group name doesn't exist (without base)
			event($event = new UnixGroupFetch($name));

			$rows = $event->results;

			if (count($rows) > 0)
			{
				// group name exists in LDAP
				return response()->json(['message' => trans('groups::groups.LDAP entry already exists for unix group')], 409);
			}
		}

		// If base is longer than 10 or fewer than 2 chars, do not proceed
		// If base+name > 17, do not proceed
		// WS allows    groupName: ^rcs\d{1,5}$
		//              lgroupName: ^rcac-.{1,17}$
		if ((strlen($base) + strlen($name) > 17))
		{
			return response()->json(['message' => trans('groups::groups.name is too long')], 415);
		}

		// Look for this entry, duplicate name, etc.
		$exist = UnixGroup::query()
			->where('groupid', '=', $group->id)
			->where('longname', '=', $base . $name)
			->where(function($where)
			{
				$where->whereNull('datetimeremoved')
					->orWhere('datetimeremoved', '=', '0000-00-00 00:00:00');
			})
			->get()
			->first();

		if ($exist && $exist->id)
		{
			return response()->json(['message' => trans('groups::groups.entry already exists for :longname', ['longname' => $name])], 409);
		}

		$row = new UnixGroup;
		$row->groupid = $group->id;
		$row->longname = $base . $name;
		$row->shortname = $row->generateShortname($name);

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.create failed')], 500);
		}

		return new UnixGroupResource($row);
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /api/unixgroups/{id}
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @param   integer $id
	 * @return Response
	 */
	public function read($id)
	{
		$row = UnixGroup::findOrFail($id);

		return new UnixGroupResource($row);
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /api/unixgroups/{id}
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
	 * 		"name":          "longname",
	 * 		"description":   "Long name",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 32
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "shortname",
	 * 		"description":   "Short name",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 8
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "unixgid",
	 * 		"description":   "Unix group system ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @param   Request $request
	 * @param   integer $id
	 * @return  Response
	 */
	public function update(Request $request, $id)
	{
		$rules = [
			//'groupid' => 'nullable|integer',
			'longname' => 'nullable|string|max:32',
			'shortname' => 'nullable|string|max:8',
			'unixgid' => 'nullable|integer'
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = UnixGroup::findOrFail($id);

		if ($request->has('longname'))
		{
			$row->longname = $request->input('longname');
		}

		if ($request->has('shortname'))
		{
			$row->shortname = $request->input('shortname');
		}

		if ($request->has('unixgid'))
		{
			$row->unixgid = $request->input('unixgid');
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.create failed')], 500);
		}

		return new UnixGroupResource($row);
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /api/unixgroups/{id}
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
	 * @param   integer $id
	 * @return  Response
	 */
	public function delete($id)
	{
		$row = UnixGroup::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
		}

		return response()->json(null, 204);
	}
}
