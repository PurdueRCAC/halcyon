<?php

namespace App\Modules\Queues\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Queues\Models\Queue;
use App\Modules\Queues\Models\User as Member;
use App\Modules\Queues\Models\GroupUser;
use App\Modules\Queues\Models\UserRequest;
use App\Modules\Resources\Entities\Child;
use App\Modules\Queues\Http\Resources\UserRequestResource;
use App\Modules\Queues\Http\Resources\UserRequestResourceCollection;
//use Illuminate\Http\Resources\Json\JsonResource;
//use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Queue User Requests
 *
 * @apiUri    /api/queues/requests
 */
class UserRequestsController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /api/queues/requests
	 * @apiAuthorization  true
	 * @apiParameter {
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
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "asc",
	 * 			"enum": [
	 * 				"asc",
	 * 				"desc"
	 * 			]
	 * 		}
	 * }
	 * @param  Request  $request
	 * @return Response
	 */
	public function index(Request $request)
	{
		$filters = array(
			'userid'    => $request->input('userid', 0),
			'queueid'   => $request->input('queueid', 0),
			// Paging
			'limit'     => $request->input('limit', config('list_limit', 20)),
			'page'     => $request->input('page', 1),
			// Sorting
			'order'     => $request->input('order', 'datetimecreated'),
			'order_dir' => $request->input('order_dir', 'desc')
		);

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = 'desc';
		}

		$u = (new UserRequest)->getTable();

		$query = UserRequest::query()
			->select($u . '.*');

		if ($filters['userid'])
		{
			$query->where($u . '.userid', '=', $filters['userid']);
		}

		if ($filters['queueid'])
		{
			$m = (new Member)->getTable();

			$query->join($m, $m . '.userrequestid', $u . '.id')
				->where($m . '.queueid', '=', $filters['groupid']);
		}

		$rows = $query
			->orderBy($u . '.' . $filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page'])
			->appends(array_filter($filters));

		return new UserRequestResourceCollection($rows);
	}

	/**
	 * Create a new entry
	 *
	 * @apiMethod POST
	 * @apiUri    /api/queues/requests
	 * @apiAuthorization  true
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
	 * @param   Request  $request
	 * @return Response
	 */
	public function create(Request $request)
	{
		$request->validate([
			'userid' => 'nullable|integer|min:1',
			'comment' => 'nullable|string|max:255',
			'queues' => 'required|array',
			'group' => 'nullable'
		]);

		$group = $request->input('group');

		$row = new UserRequest;
		$row->userid = $request->input('userid');
		if (!$row->userid)
		{
			$row->userid = auth()->user()->id;
		}
		$row->comment = $request->input('comment');

		if (!$row->save())
		{
			return response()->json(['message' => trans('messages.create failed')], 500);
		}

		// Generate necessary groupuser or queueuser records
		$queues = $request->input('queues', []);

		foreach ($queues as $queueid)
		{
			$queueuser = 0;

			// Gather existing queueuser if groupid == 0
			$queue = Queue::find($queueid);

			if ($queue)
			{
				if (!$queue->groupid)
				{
					$queueuser = Member::query()
						->where('queueid', '=', $queueid)
						->where('userid', '=', $row->userid)
						->get()
						->first();
				}

				if (!$queueuser)
				{
					$queueuser = new Member;
					$queueuser->queueid = $queueid;
					$queueuser->userid  = $row->userid;
					if (!$queue->groupid)
					{
						$queueuser->userrequestid = 0;
						$queueuser->notice        = 0;
						$queueuser->setAsMember();
					}
					else
					{
						$queueuser->userrequestid = $row->id;
						$queueuser->notice        = 6;
						$queueuser->setAsPending();
					}
					$queueuser->save();
				}

				// Set up groupqueueuser entry
				if (!$queue->groupid)
				{
					$groupqueueuser = new GroupUser;
					$groupqueueuser->groupid       = $queue->groupid;
					$groupqueueuser->queueuserid   = $queueuser->id;
					$groupqueueuser->userrequestid = $row->id;
					$groupqueueuser->notice        = 6;
					$groupqueueuser->setAsPending();
					$groupqueueuser->save();
				}
			}
		}

		$resources = $request->input('resources', []);

		if ($group && count($resources))
		{
			// Gather the queues this group owns and are in the requested resources
			$q = (new Queue)->getTable();
			$c = (new Child)->getTable();

			$queues = Queue::query()
				->select($q . '.*')
				->join($c, $c . '.subresourceid', $q . '.subresourceid')
				->whereIn($c . '.resourceid', $resources)
				->where(function($where) use ($q, $group)
					{
						$where->where($q . '.groupid', '=', $group)
							->orWhere($q . '.groupid', '=', 0)
							->orWhere($q . '.groupid', '=', 33338);
					})
				->get();

			foreach ($queues as $queue)
			{
				$queueuser = 0;

				// Gather existing queueuser if groupid == 0
				if ($queue->groupid == 0
				 || $queue->id == 33338)
				{
					$queue->groupid = 0;

					$queueuser = Member::query()
						->where('queueid', '=', $queue->id)
						->where('userid', '=', $row->userid)
						->get()
						->first();

					if (!$queueuser)
					{
						$queueuser = new Member;
						$queueuser->queueid = $queueid;
						$queueuser->userid  = $row->userid;
						if (!$queue->groupid)
						{
							$queueuser->userrequestid = 0;
							$queueuser->notice        = 0;
							$queueuser->setAsMember();
						}
						else
						{
							$queueuser->userrequestid = $row->id;
							$queueuser->notice        = 6;
							$queueuser->setAsPending();
						}
						$queueuser->save();
					}
				}

				// Set up groupqueueuser entry
				if (!$queue->groupid)
				{
					$groupqueueuser = new GroupUser;
					$groupqueueuser->groupid       = $queue->groupid;
					$groupqueueuser->queueuserid   = $queueuser->id;
					$groupqueueuser->userrequestid = $row->id;
					$groupqueueuser->notice        = 6;
					$groupqueueuser->setAsPending();
					$groupqueueuser->save();
				}
			}
		}

		return new UserRequestResource($row);
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /api/queues/requests/{id}
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
		$row = UserRequest::findOrFail($id);

		return new UserRequestResource($row);
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /api/queues/requests/{id}
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
	 * @param   integer $id
	 * @return  Response
	 */
	public function update(Request $request, $id)
	{
		$row = UserRequest::findOrFail($id);

		// Ensure the client is authorized to manage the controlling group.
		$u = (new Member)->getTable();
		$q = (new Queue)->getTable();

		$queueusers = Member::query()
			->select($u . '.*', $q . '.groupid')
			->join($q, $q . '.id', $u . '.queueid')
			->where($u . '.userrequestid', '=', $id)
			->wherePendingRequest()
			->get();
			//->first();

		if (!count($queueusers))
		{
			$gu = (new GroupMember)->getTable();

			$queueusers = GroupMember::query()
				->select($gu . '.*', $q . '.groupid')
				->join($q, $q . '.id', $gu . '.queueid')
				->where($gu . '.userrequestid', '=', $id)
				->wherePendingRequest()
				->get();
				//->first();

			if (!count($queueusers))
			{
				return response()->json(['message' => trans('No pending queueusers found for userrequest :id', ['id' => $id])], 404);
			}
		}

		// Loop through each request and call queuemember to create accounts
		foreach ($queueusers as $queueuser)
		{
			// Update membertypes
			$queueuser->setAsMember();
			$queueuser->notice = 2;

			if (!$queueuser->save())
			{
				return response()->json(['message' => trans('Failed to update `queueusers` fields `membertype` and `notice` for userrequest :id', ['id' => $id])], 500);
			}
		}

		return new UserRequestResource($row);
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /api/queues/requests/{id}
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
		$row = UserRequest::findOrFail($id);

		$user = $row->userid;
		$groups = auth()->user()->groups()->whereIsManager()->get()->pluck('id')->toArray();

		// Fetch count of queueuser entries to delete, and the groupid
		$u = (new Member)->getTable();
		$q = (new Queue)->getTable();

		$numqueues = Queue::query()
			->select($q . '.groupid')
			->join($u, $u . '.queueid', $q . '.id')
			->where($u . '.userrequestid', '=', $id)
			->wherePendingRequest()
			->where($u . '.userid', '=', $row->userid)
			->get();

		if (count($numqueues) > 0)
		{
			foreach ($numqueues as $numqueue)
			{
				// Ensure client is authorized to delete requests
				if ($row->userid != auth()->user()->id
				 && !in_array($numqueue->groupid, $groups)
				 && !auth()->user()->can('manage queues'))
				{
					return 403;
				}

				// If self deleting, we want to DELETE, otherwise managers should "end" queueusers (meaning rejected)
				if ($row->userid != auth()->user()->id)
				{
					// Delete any queueuser entries tied to this userrequest
					$result = Member::where('userrequestid', '=', $row->id)
						->where('userid', '=', $row->userid)
						->wherePendingRequest()
						->delete();

					if (!$result)
					{
						return response()->json(['message' => trans('Failed to delete `queueusers` entries for request :id', ['id' => $id])], 500);
					}
				}
				else
				{
					// End any queueuser entries tied to this userrequest
					$result = Member::where('userrequestid', '=', $row->id)
						->where('userid', '=', $row->userid)
						->wherePendingRequest()
						->delete();

					$result = Member::where('userrequestid', '=', $row->id)
						->where('userid', '=', $row->userid)
						->wherePendingRequest()
						->update(['notice' => 12]);

					if (!$result)
					{
						return response()->json(['message' => trans('Failed to mark `queueusers` entries as removed for request :id', ['id' => $id])], 500);
					}
				}
			}
		}

		// Fetch count of groupqueueuser entries to delete, and the groupid
		$numqueues = GroupUser::where('userrequestid', '=', $row->id)
						->wherePendingRequest()
						->get();

		if (count($numqueues) > 0)
		{
			foreach ($numqueues as $numqueue)
			{
				// Ensure client is authorized to delete requests
				if ($row->userid != auth()->user()->id
				 && !in_array($numqueue->groupid, $groups)
				 && !auth()->user()->can('edit.own queues'))
				{
					return 403;
				}

				// If self deleting, we want to DELETE, otherwise managers should "end" queueusers (meaning rejected)
				if ($row->userid != auth()->user()->id)
				{
					// Delete any queueuser entries tied to this userrequest
					$result = UserGroup::where('userrequestid', '=', $row->id)
						->where('userid', '=', $row->userid)
						->wherePendingRequest()
						->delete();

					if (!$result)
					{
						return response()->json(['message' => trans('Failed to delete `queueusers` entries for request :id', ['id' => $id])], 500);
					}
				}
				else
				{
					// End any queueuser entries tied to this userrequest
					$result = UserGroup::where('userrequestid', '=', $row->id)
						->where('userid', '=', $row->userid)
						->wherePendingRequest()
						->delete();

					$result = UserGroup::where('userrequestid', '=', $row->id)
						->where('userid', '=', $row->userid)
						->wherePendingRequest()
						->update(['notice' => 12]);

					if (!$result)
					{
						return response()->json(['message' => trans('Failed to mark `queueusers` entries as removed for request :id', ['id' => $id])], 500);
					}
				}
			}
		}

		if ($row->userid == auth()->user()->id
		 || auth()->user()->can('admin queues'))
		{
			if (!$row->delete())
			{
				return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
			}
		}

		return response()->json(null, 204);
	}
}
