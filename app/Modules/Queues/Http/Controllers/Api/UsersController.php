<?php

namespace App\Modules\Queues\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Modules\Users\Models\User;
use App\Modules\Queues\Models\User as QueueUser;
use App\Modules\Queues\Models\Queue;
use App\Modules\Queues\Models\GroupUser;
use App\Modules\Queues\Events\UserCreated;
use App\Modules\Group\Models\Group;
use App\Modules\Resources\Events\ResourceMemberCreated;
use App\Modules\Resources\Events\ResourceMemberStatus;
use App\Modules\Resources\Events\ResourceMemberDeleted;
use App\Modules\Resources\Models\Asset;

/**
 * Queue Users
 *
 * @apiUri    /queues/users
 */
class UsersController extends Controller
{
	/**
	 * Display a listing of queue users.
	 *
	 * @apiMethod GET
	 * @apiUri    /queues/users
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "queueid",
	 * 		"description":   "A queue ID to filter by.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "userid",
	 * 		"description":   "A user ID to filter by.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "membertype",
	 * 		"description":   "A member type ID to filter by.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "notice",
	 * 		"description":   "A notice state to filter by.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "limit",
	 * 		"description":   "Number of result to return.",
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
	 * 			"default":   "datetimecreated",
	 * 			"enum": [
	 * 				"id",
	 * 				"userid",
	 * 				"queueid",
	 * 				"membertype",
	 * 				"datetimecreated",
	 * 				"datetimeremoved",
	 * 				"notice"
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
			'queueid'    => $request->input('queueid'),
			'userid'     => $request->input('userid'),
			'membertype' => $request->input('membertype'),
			'notice'     => $request->input('notice'),
			// Paging
			'limit'      => $request->input('limit', config('list_limit', 20)),
			'page'       => $request->input('page', 1),
			// Sorting
			'order'      => $request->input('order', 'datetimecreated'),
			'order_dir'  => $request->input('order_dir', 'asc')
		);

		if (!in_array($filters['order'], ['id', 'userid', 'queueid', 'membertype', 'datetimecreated', 'datetimeremoved', 'notice']))
		{
			$filters['order'] = 'datetimecreated';
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = 'asc';
		}

		$query = QueueUser::query();

		if ($filters['queueid'])
		{
			$query->where('queueid', '=', $filters['queueid']);
		}

		if ($filters['userid'])
		{
			$query->where('userid', '=', $filters['userid']);
		}

		if ($filters['membertype'])
		{
			$query->where('membertype', '=', $filters['membertype']);
		}

		if ($filters['notice'])
		{
			$query->where('notice', '=', $filters['notice']);
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page'])
			->appends(array_filter($filters));

		$rows->map(function($row, $key)
		{
			$row->api = route('api.queues.users.read', ['id' => $row->id]);
		});

		return new ResourceCollection($rows);
	}

	/**
	 * Create a queue user
	 *
	 * @apiMethod POST
	 * @apiUri    /queues/users
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "queueid",
	 * 		"description":   "Queue ID",
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
	 * 		"name":          "userrequestid",
	 * 		"description":   "User Request ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "membertype",
	 * 		"description":   "Member type ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   1
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"201": {
	 * 			"description": "Successful entry creation"
	 * 		},
	 * 		"409": {
	 * 			"description": "Invalid data"
	 * 		}
	 * }
	 * @param   Request  $request
	 * @return  JsonResource
	 */
	public function create(Request $request)
	{
		$rules = [
			'queueid'       => 'required|integer',
			'userid'        => 'required',
			'userrequestid' => 'nullable|integer',
			'membertype'    => 'nullable|integer',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		// This can be either an ID, username, or email
		$userid = $request->input('userid');

		if (!is_numeric($userid))
		{
			// If a username or email is provided, try to find or auto-create an account
			$user = User::createFromUsername($userid);

			if ($user && $user->id)
			{
				$userid = $user->id;
			}
		}

		$queue = Queue::findOrFail($request->input('queueid'));

		$row = QueueUser::query()
			->withTrashed()
			->where('queueid', '=', $request->input('queueid'))
			->where('userid', '=', $userid)
			->get()
			->first();

		// Set notice state
		if ($row)
		{
			if ($row->trashed())
			{
				$row->restore();

				event(new UserCreated($row));
			}
			// Nothing to do, we are cancelling a removal
			$row->notice = 0;
		}
		else
		{
			$row = new QueueUser();
			$row->queueid = $request->input('queueid');
			$row->userid = $userid;
			if ($request->has('userrequestid'))
			{
				$row->userrequestid = $request->input('userrequestid');
			}
			$row->membertype = $request->input('membertype');
			$row->membertype = $row->membertype ?: 1;
			$row->notice = 2;
		}

		// Look up the current username of the user being granted access.
		$user = User::find($row->userid);

		if (!$user || !$user->id || $user->trashed())
		{
			return response()->json(['message' => trans('global.error.user not found')], 409);
		}

		if ($queue->groupid
		 && !$queue->group->isManager(auth()->user())
		 && !auth()->user()->can('manage groups'))
		{
			return response()->json(['message' => trans('global.error.user not authorized')], 403);
		}

		if (!$queue->groupid)
		{
			$groupid = $request->input('groupid');

			if (!$groupid)
			{
				return response()->json(['message' => trans('Missing required field `group`')], 415);
			}

			$group = Group::findOrFail($groupid);

			if (!$group->isManager(auth()->user())
			 && !auth()->user()->can('manage groups'))
			{
				return response()->json(['message' => trans('global.error.user not authorized')], 403);
			}

			$groupuser = GroupUser::query()
				->where('groupid', '=', $group->id)
				->where('queueuserid', '=', $row->id)
				->first();

			if ($groupuser)
			{
				$groupuser->update(['notice' => 0]);
			}
			else
			{
				$row->notice = 2;
			}
		}

		$row->save();

		event($resourcemember = new ResourceMemberStatus($row->queue->scheduler->resource, $row->user));

		if ($resourcemember->noStatus() || $resourcemember->isPendingRemoval())
		{
			event($resourcemember = new ResourceMemberCreated($row->queue->scheduler->resource, $row->user));

			if ($resourcemember->status >= 400)
			{
				$row->error = implode("\n", $resourcemember->errors);
			}
		}

		$row->api = route('api.queues.users.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Read a queue user
	 *
	 * @apiMethod GET
	 * @apiUri    /queues/users/{id}
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
	 * 			"description": "Successful entry read"
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		}
	 * }
	 * @param   integer  $id
	 * @return  JsonResource
	 */
	public function read($id)
	{
		$row = QueueUser::findOrFail($id);
		$row->api = route('api.queues.users.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Update a queue user
	 *
	 * @apiMethod PUT
	 * @apiUri    /queues/users/{id}
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
	 * 		"name":          "queueid",
	 * 		"description":   "Queue ID",
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
	 * 		"name":          "userrequestid",
	 * 		"description":   "User Request ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "membertype",
	 * 		"description":   "Member type ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   1
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "datetimelastseen",
	 * 		"description":   "The datetime the user was last seen",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date-time",
	 * 			"example":   "2021-01-30T08:30:00Z"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "notice",
	 * 		"description":   "The notice state",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"204": {
	 * 			"description": "Successful entry modification"
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		},
	 * 		"409": {
	 * 			"description": "Invalid data"
	 * 		}
	 * }
	 * @param   integer  $id
	 * @param   Request  $request
	 * @return  JsonResource
	 */
	public function update($id, Request $request)
	{
		$rules = [
			'queueid'          => 'nullable|integer',
			'userid'           => 'nullable|integer',
			'userrequestid'    => 'nullable|integer',
			'membertype'       => 'nullable|integer',
			'datetimelastseen' => 'nullable|date',
			'notice'           => 'nullable|integer',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = QueueUser::findOrFail($id);
		$row->fill($request->all());

		if ($request->has('queueid'))
		{
			if (!$request->queue)
			{
				return response()->json(['message' => trans('Invalid queueid.')], 415);
			}
		}

		if ($request->has('userid'))
		{
			if (!$request->user)
			{
				return response()->json(['message' => trans('Invalid userid.')], 415);
			}
		}

		if ($request->has('membertype'))
		{
			if (!$request->type)
			{
				return response()->json(['message' => trans('Invalid membertype.')], 415);
			}
		}

		$row->save();
		$row->api = route('api.queues.users.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Delete a queue user
	 *
	 * @apiMethod DELETE
	 * @apiUri    /queues/users/{id}
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
	public function delete($id)
	{
		$row = QueueUser::findOrFail($id);

		// Determine notice level
		if ($row->notice == 2)
		{
			$row->notice = 0;
		}
		elseif ($row->notice == 10)
		{
			$row->notice = 17;
		}
		else
		{
			$row->notice = 3;
		}

		if ($row->queue->groupid == 0)
		{
			// Only allow delete if no groupqueueuser entries are present
			$gqusers = GroupUser::query()
				->where('queueuserid', '=', $row->id)
				->whereIsMember()
				->get();

			if (count($gqusers) && auth()->user()->id != $row->userid)
			{
				return response()->json(['message' => trans('global.messages.not authorized')], 403);
			}
			elseif (count($gqusers) && auth()->user()->id == $row->userid)
			{
				// Clean up all groupqueueuser entries
				foreach ($gqusers as $gquser)
				{
					$gquser->delete();
				}
			}

			// Set notice to 0 for now
			$row->notice = 0;
		}

		$row->save(); //update(['notice' => $row->notice]);

		// Skip actually deleting the database entry if there are pending requests for this queue (but continue with deleting accounts)
		/*$gqusers = GroupUser::query()
			->where('queueuserid', '=', $row->id)
			->whereIsMember()
			->get();*/

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
		}

		$response = null;
		$status = 204;

		event($resourcemember = new ResourceMemberStatus($row->queue->scheduler->resource, $row->user));

		if ($resourcemember->isPending() || $resourcemember->isReady())
		{
			$rows = 0;

			/*$owned = $row->user->groups->pluck('id')->toArray();

			// Check for other queue memberships on this resource that might conflict with removing the role
			$resources = Asset::query()
				->where('rolename', '!=', '')
				->where('listname', '!=', '')
				->get();

			foreach ($resources as $res)
			{
				$subresources = $res->subresources;*/
				$subresources = $row->queue->scheduler->resource->subresources;

				foreach ($subresources as $sub)
				{
					$queues = $sub->queues()
						//->whereIn('groupid', $owned)
						->get();
						//->pluck('queuid')
						//->toArray();

					foreach ($queues as $queue)
					{
						$rows += $queue->users()
							->whereIsMember()
							->where('userid', '=', $row->userid)
							->count();

						if ($queue->group)
						{
							$rows += $queue->group->members()
								->whereIsManager()
								->where('userid', '=', $row->userid)
								->count();
						}
					}
				}
			//}

			if ($rows == 0)
			{
				// No other active memberships found, remove resource access
				event($resourcemember = new ResourceMemberDeleted($row->queue->scheduler->resource, $row->user));

				if (count($resourcemember->errors))
				{
					$response = ['message' => implode("\n", $resourcemember->errors)];
					$status = 500;
				}
			}
		}

		return response()->json($response, $status);
	}
}
