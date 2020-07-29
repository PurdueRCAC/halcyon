<?php

namespace App\Modules\Queues\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Queues\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UsersController extends Controller
{
	/**
	 * Display a listing of queue users.
	 *
	 * @apiMethod GET
	 * @apiUri    /queues/users
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
	 *      "name":          "sort",
	 *      "description":   "Field to sort results by.",
	 *      "type":          "string",
	 *      "required":      false,
	 *      "default":       "created",
	 *      "allowedValues": "id, name, datetimecreated, datetimeremoved, parentid"
	 * }
	 * @apiParameter {
	 *      "name":          "sort_dir",
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
			'sort'     => $request->input('sort', 'datetimecreated'),
			'sort_dir' => $request->input('sort_dir', 'asc')
		);

		if (!in_array($filters['sort_dir'], ['asc', 'desc']))
		{
			$filters['sort_dir'] = 'asc';
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
			->orderBy($filters['sort'], $filters['sort_dir'])
			->paginate($filters['limit'])
			->appends(array_filter($filters));

		return new ResourceCollection($rows);
	}

	/**
	 * Create a queue user
	 *
	 * @apiMethod POST
	 * @apiUri    /queues/users
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
			'name' => 'required|string|max:20'
		]);

		$row = Type::create($request->all());

		return new JsonResource($row);
	}

	/**
	 * Read a queue user
	 *
	 * @apiMethod POST
	 * @apiUri    /queues/users/{id}
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
	 * @apiUri    /queues/users/{id}
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
	 * @apiUri    /queues/users/{id}
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

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
		}

		return response()->json(null, 204);
	}
}
