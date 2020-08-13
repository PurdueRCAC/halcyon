<?php

namespace App\Modules\Groups\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Groups\Models\Group;
use App\Modules\Groups\Models\Motd;

/**
 * Message of the day
 *
 * @apiUri    /api/groups/motd
 */
class MotdController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /api/groups/motd
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
	 * 		"name":          "group_id",
	 * 		"description":   "Group ID",
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
	 * 		"default":       "datetimecreated",
	 * 		"allowedValues": "id, motd, datetimecreated, datetimeremoved"
	 * }
	 * @apiParameter {
	 * 		"name":          "order_dir",
	 * 		"description":   "Direction to sort results by.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "desc",
	 * 		"allowedValues": "asc, desc"
	 * }
	 * @return Response
	 */
	public function index(Request $request)
	{
		$filters = array(
			'search'   => $request->input('search', ''),
			'group_id'   => $request->input('group_id', 0),
			// Paging
			'limit'    => $request->input('limit', config('list_limit', 20)),
			// Sorting
			'order'     => $request->input('order', Motd::$orderBy),
			'order_dir' => $request->input('order_dir', Motd::$orderDir)
		);

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Motd::$orderDir;
		}

		$query = Motd::query();

		if ($filters['search'])
		{
			$filters['search'] = strtolower((string)$filters['search']);

			$query->where('motd', 'like', '%' . $filters['search'] . '%');
		}

		if ($filters['group_id'])
		{
			$query->where('groupid', '=', $filters['group_id']);
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'])
			->appends(array_filter($filters));

		$rows->each(function ($item, $key)
		{
			$item->api = route('api.groups.motd.read', ['id' => $item->id]);
		});

		return new ResourceCollection($rows);
	}

	/**
	 * Create a new entry
	 *
	 * @apiMethod POST
	 * @apiUri    /api/groups/motd
	 * @apiParameter {
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"type":          "integer",
	 * 		"required":      true,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "message",
	 * 		"description":   "Message of the day",
	 * 		"type":          "string",
	 * 		"required":      true,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "group_id",
	 * 		"description":   "Group ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       0
	 * }
	 * @return Response
	 */
	public function create(Request $request)
	{
		$request->validate([
			'groupid' => 'required|integer|min:1',
			'message' => 'required|string',
		]);

		$group_id = $request->input('groupid');

		$exists = Group::findOrFail($group_id);

		if (!$exists)
		{
			return response()->json(['message' => trans('Specified group does not exist')], 500);
		}

		$row = new Motd;
		$row->groupid = $group_id;
		$row->motd = $request->input('message');

		if (!$row->save())
		{
			return response()->json(['message' => trans('messages.create failed')], 500);
		}

		// Disable any old MOTD
		$motds = $group->motds()->where('id', '!=', $row->id)->get();
		foreach ($motds as $motd)
		{
			$motd->delete();
		}

		$row->api = route('api.groups.motd.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /api/groups/motd/{id}
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
		$row = Motd::findOrFail($id);
		$row->api = route('api.groups.motd.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /api/groups/motd/{id}
	 * @apiParameter {
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"type":          "integer",
	 * 		"required":      true,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "message",
	 * 		"description":   "Message of the day",
	 * 		"type":          "string",
	 * 		"required":      true,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "group_id",
	 * 		"description":   "Group ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       0
	 * }
	 * @param   Request $request
	 * @return  Response
	 */
	public function update(Request $request, $id)
	{
		$request->validate([
			'group_id' => 'required|integer',
			'message' => 'required|string',
		]);

		$row = Motd::findOrFail($id);

		if ($message = $request->input('message'))
		{
			$row->message = $message;
		}

		if ($group_id = $request->input('group_id'))
		{
			$exists = Group::findOrFail($group_id);

			$row->group_id = $group_id;
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('messages.create failed')], 500);
		}

		$row->api = route('api.groups.motd.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /api/groups/motd/{id}
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
		$row = Motd::findOrFail($id);

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
