<?php

namespace App\Modules\Queues\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Queues\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Queue Users
 *
 * @apiUri    /api/queues/users
 */
class UsersController extends Controller
{
	/**
	 * Display a listing of queue users.
	 *
	 * @apiMethod GET
	 * @apiUri    /api/queues/users
	 * @apiAuthorization  true
	 * @apiParameter {
	 *      "name":          "limit",
	 *      "description":   "Number of result to return.",
	 *      "type":          "integer",
	 *      "required":      false,
	 *      "default":       25
	 * }
	 * @apiParameter {
	 *      "name":          "page",
	 *      "description":   "Number of where to start returning results.",
	 *      "type":          "integer",
	 *      "required":      false,
	 *      "default":       0
	 * }
	 * @apiParameter {
	 *      "name":          "search",
	 *      "description":   "A word or phrase to search for.",
	 *      "type":          "string",
	 *      "required":      false,
	 *      "default":       ""
	 * }
	 * @apiParameter {
	 *      "name":          "order",
	 *      "description":   "Field to sort results by.",
	 *      "type":          "string",
	 *      "required":      false,
	 *      "default":       "created",
	 *      "allowedValues": "id, name, datetimecreated, datetimeremoved, parentid"
	 * }
	 * @apiParameter {
	 *      "name":          "order_dir",
	 *      "description":   "Direction to sort results by.",
	 *      "type":          "string",
	 *      "required":      false,
	 *      "default":       "desc",
	 *      "allowedValues": "asc, desc"
	 * }
	 * @param   Request  $request
	 * @return Response
	 */
	public function index(Request $request)
	{
		$filters = array(
			'queueid'   => $request->input('queueid'),
			'userid'   => $request->input('userid'),
			'membertype' => $request->input('membertype'),
			'notice' => $request->input('notice'),
			// Paging
			'limit'    => $request->input('limit', config('list_limit', 20)),
			//'start' => $request->input('limitstart', 0),
			// Sorting
			'order'     => $request->input('order', 'datetimecreated'),
			'order_dir' => $request->input('order_dir', 'asc')
		);

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = 'asc';
		}

		$query = User::query();

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
			->paginate($filters['limit'])
			->appends(array_filter($filters));

		return new ResourceCollection($rows);
	}

	/**
	 * Create a queue user
	 *
	 * @apiMethod POST
	 * @apiUri    /api/queues/users
	 * @apiAuthorization  true
	 * @apiParameter {
	 *      "name":          "name",
	 *      "description":   "The name of the queue user",
	 *      "type":          "string",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @param   Request  $request
	 * @return Response
	 */
	public function create(Request $request)
	{
		$request->validate([
			'queueid' => 'required|integer',
			'userid' => 'required|integer',
			'userrequestid' => 'nullable|integer',
			'membertype' => 'nullable|integer',
		]);

		$row = new User();
		$row->fill($request->all());
		$row->membertype = $row->membertype ?: 1;
		$row->notice = 2;

		$row->save();

		return new JsonResource($row);
	}

	/**
	 * Read a queue user
	 *
	 * @apiMethod GET
	 * @apiUri    /api/queues/users/{id}
	 * @apiAuthorization  true
	 * @apiParameter {
	 *      "name":          "id",
	 *      "description":   "The ID of the queue user",
	 *      "type":          "integer",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @param   integer  $id
	 * @return  Response
	 */
	public function read($id)
	{
		$row = User::findOrFail($id);

		return new JsonResource($row);
	}

	/**
	 * Update a queue user
	 *
	 * @apiMethod PUT
	 * @apiUri    /api/queues/users/{id}
	 * @apiAuthorization  true
	 * @apiParameter {
	 *      "name":          "id",
	 *      "description":   "The ID of the queue user",
	 *      "type":          "integer",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @apiParameter {
	 *      "name":          "queueid",
	 *      "description":   "The ID of the queue",
	 *      "type":          "integer",
	 *      "required":      false,
	 *      "default":       null
	 * }
	 * @apiParameter {
	 *      "name":          "userid",
	 *      "description":   "The ID of the user",
	 *      "type":          "integer",
	 *      "required":      false,
	 *      "default":       null
	 * }
	 * @apiParameter {
	 *      "name":          "userrequestid",
	 *      "description":   "The ID of a user request",
	 *      "type":          "integer",
	 *      "required":      false,
	 *      "default":       null
	 * }
	 * @apiParameter {
	 *      "name":          "membertype",
	 *      "description":   "The ID of user member type",
	 *      "type":          "integer",
	 *      "required":      false,
	 *      "default":       null
	 * }
	 * @apiParameter {
	 *      "name":          "datetimelastseen",
	 *      "description":   "The datetime the user was last seen",
	 *      "type":          "string",
	 *      "required":      false,
	 *      "default":       null
	 * }
	 * @apiParameter {
	 *      "name":          "notice",
	 *      "description":   "The notice state",
	 *      "type":          "integer",
	 *      "required":      false,
	 *      "default":       null
	 * }
	 * @param   integer  $id
	 * @param   Request  $request
	 * @return  Response
	 */
	public function update($id, Request $request)
	{
		$request->validate([
			'queueid' => 'nullable|integer',
			'userid' => 'nullable|integer',
			'userrequestid' => 'nullable|integer',
			'membertype' => 'nullable|integer',
			'datetimelastseen' => 'nullable|date',
			'notice' => 'nullable|integer',
		]);

		$row = User::findOrFail($id);
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

		return new JsonResource($row);
	}

	/**
	 * Delete a queue user
	 *
	 * @apiMethod DELETE
	 * @apiUri    /api/queues/users/{id}
	 * @apiAuthorization  true
	 * @apiParameter {
	 *      "name":          "id",
	 *      "description":   "The ID of the queue user",
	 *      "type":          "integer",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @param   integer  $id
	 * @return  Response
	 */
	public function delete($id)
	{
		$row = User::findOrFail($id);

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

		if ($groupid == 0)
		{
			// Only allow delete if no groupqueueuser entries are present
			$gqusers = GroupUser::query()
				->where('queueuserid', '=', $row->id)
				->whereIsMember()
				->where(function($where)
				{
					$where->whereNull('datetimeremoved')
						->orWhere('datetimeremoved', '=', '0000-00-00 00:00:00');
				})
				->get();

			if (count($gqusers) && auth()->user()->id != $row->userid)
			{
				return 403;
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

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
		}

		return response()->json(null, 204);
	}
}
