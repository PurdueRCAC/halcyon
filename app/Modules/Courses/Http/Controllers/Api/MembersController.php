<?php

namespace App\Modules\Courses\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Courses\Models\Member;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

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
	 * 		"name":          "classaccountid",
	 * 		"description":   "Class account ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       0
	 * }
	 * @apiParameter {
	 * 		"name":          "membertype",
	 * 		"description":   "Member type ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       0
	 * }
	 * @apiParameter {
	 * 		"name":          "userid",
	 * 		"description":   "User ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       0
	 * }
	 * @apiParameter {
	 * 		"name":          "notice",
	 * 		"description":   "Notice status",
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

		$rows->each(function ($item, $key)
		{
			$item->api = route('api.courses.members.read', ['id' => $item->id]);
			$item->user;
		});

		return new ResourceCollection($rows);
	}

	/**
	 * Create a new entry
	 *
	 * @apiMethod POST
	 * @apiUri    /courses/members
	 * @apiParameter {
	 * 		"name":          "classaccountid",
	 * 		"description":   "Class account ID",
	 * 		"type":          "integer",
	 * 		"required":      true,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "membertype",
	 * 		"description":   "Member type ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "userid",
	 * 		"description":   "User ID",
	 * 		"type":          "integer",
	 * 		"required":      true,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "datetimestart",
	 * 		"description":   "Datetime (YYYY-MM-DD hh:mm:ss) user enrollment starts",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "datetimestop",
	 * 		"description":   "Datetime (YYYY-MM-DD hh:mm:ss) user enrollment stops",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "notice",
	 * 		"description":   "Notice state",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @return Response
	 */
	public function create(Request $request)
	{
		$request->validate([
			'classaccountid' => 'required|integer',
			'userid' => 'required|integer',
			'membertype' => 'nullable|integer',
		]);

		$classaccountid = $request->input('classaccountid');
		$userid  = $request->input('userid');

		$exists = Member::findByAccountAndUser($classaccountid, $userid);

		if ($exists)
		{
			return new JsonResource($exists);
		}

		$row = new Member;
		$row->classaccountid = $classaccountid;
		$row->userid = $userid;
		$row->membertype = $request->input('membertype', 1);
		$row->notice = 1;

		if (!$row->save())
		{
			return response()->json(['message' => trans('messages.create failed')], 500);
		}

		return new JsonResource($row);
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /courses/members/{id}
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
		$row->api = route('api.courses.members.read', ['id' => $row->id]);
		$row->user;

		return new JsonResource($row);
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /courses/members/{id}
	 * @apiParameter {
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"type":          "integer",
	 * 		"required":      true,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "classaccountid",
	 * 		"description":   "Class account ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "membertype",
	 * 		"description":   "Member type ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "userid",
	 * 		"description":   "User ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "datetimestart",
	 * 		"description":   "Datetime (YYYY-MM-DD hh:mm:ss) user enrollment starts",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "datetimestop",
	 * 		"description":   "Datetime (YYYY-MM-DD hh:mm:ss) user enrollment stops",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "notice",
	 * 		"description":   "Notice state",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @param   Request $request
	 * @return  Response
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

		if (!$row->save())
		{
			return response()->json(['message' => trans('messages.create failed')], 500);
		}

		return new JsonResource($row);
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /courses/members/{id}
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
