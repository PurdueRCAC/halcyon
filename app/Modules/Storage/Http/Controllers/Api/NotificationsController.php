<?php

namespace App\Modules\Storage\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Storage\Models\Notification;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Notifications
 *
 * @apiUri    /api/storage/notifications
 */
class NotificationsController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @apiMethod GET
	 * @apiUri    /storage/notifications
	 * @apiParameter {
	 * 		"name":          "limit",
	 * 		"description":   "Number of result to return.",
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
	 *      "default":       "created",
	 * 		"allowedValues": "id, name, datetimecreated, datetimeremoved, parentid"
	 * }
	 * @apiParameter {
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
	 * @return Response
	 */
	public function index(Request $request)
	{
		$filters = array(
			'enabled'      => $request->input('enabled', '-1'),
			'storagedirquotanotificationtypeid' => $request->input('storagedirquotanotificationtypeid'),
			'storagedirid' => $request->input('storagedirid'),
			'userid'       => $request->input('userid'),
			'timeperiodid' => $request->input('timeperiodid', '-1'),
			// Paging
			'limit'     => $request->input('limit', config('list_limit', 20)),
			// Sorting
			'order'     => $request->input('order', 'id'),
			'order_dir' => $request->input('order_dir', 'desc')
		);

		// Get records
		$query = Notification::query();

		if ($filters['enabled'] >= 0)
		{
			$query->where('enabled', '=', $filters['enabled']);
		}

		if ($filters['storagedirquotanotificationtypeid'])
		{
			$query->where('storagedirquotanotificationtypeid', '=', $filters['storagedirquotanotificationtypeid']);
		}

		if ($filters['storagedirid'])
		{
			$query->where('storagedirid', '=', $filters['storagedirid']);
		}

		if ($filters['userid'])
		{
			$query->where('userid', '=', $filters['userid']);
		}

		if ($filters['timeperiodid'] >= 0)
		{
			$query->where('timeperiodid', '=', $filters['timeperiodid']);
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'])
			->appends($filters);

		return new ResourceCollection($rows);
	}

	/**
	 * Create a resource
	 *
	 * @apiMethod POST
	 * @apiUri    /storage/notifications
	 * @apiParameter {
	 *      "name":          "name",
	 *      "description":   "The name of the resource type",
	 *      "type":          "string",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @return Response
	 */
	public function create(Request $request)
	{
		$request->validate([
			'storagedirid' => 'required|integer|min:1',
			'userid' => 'nullable|integer',
			'storagedirquotanotificationtypeid' => 'required|integer|min:1',
			'value' => 'nullable|integer',
			'timeperiodid' => 'nullable|integer',
			'periods' => 'nullable|integer|min:0',
			'notice'  => 'nullable|integer',
			'enabled' => 'nullable|integer',
			'datetimelastnotify' => 'nullable|date',
		]);

		//$row = Notification::create($request->all());
		$row = new Notification;
		$row->fill($request->all());

		if (!$row->userid)
		{
			$row->userid = auth()->user()->id;
		}

		if (!$row->directory)
		{
			return response()->json(['message' => trans('Invalid storagedirid')], 415);
		}

		if ($request->has('timeperiodid') && !$row->timeperiod)
		{
			return response()->json(['message' => trans('Invalid timeperiodid')], 415);
		}

		if (!$row->type)
		{
			return response()->json(['message' => trans('Invalid storagedirquotanotificationtypeid')], 415);
		}

		$row->save();

		return new JsonResource($row);
	}

	/**
	 * Read a resource
	 *
	 * @apiMethod GET
	 * @apiUri    /storage/notifications/{id}
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
	public function read($id)
	{
		$row = Notification::findOrFail($id);

		return new JsonResource($row);
	}

	/**
	 * Update a resource
	 *
	 * @apiMethod PUT
	 * @apiUri    /storage/notifications/{id}
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
	 *      "name":          "name",
	 *      "description":   "The name of the resource type",
	 *      "type":          "string",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @return  Response
	 */
	public function update($id, Request $request)
	{
		$request->validate([
			'storagedirid' => 'nullable|integer|min:1',
			'userid' => 'nullable|integer',
			'storagedirquotanotificationtypeid' => 'required|integer|min:1',
			'value' => 'nullable|integer',
			'timeperiodid' => 'nullable|integer',
			'periods' => 'nullable|integer|min:0',
			'notice'  => 'nullable|integer',
			'enabled' => 'nullable|integer',
			'datetimelastnotify' => 'nullable|date',
		]);

		$row = Notification::findOrFail($id);
		$row->fill($request->all());

		if ($request->has('storagedirid') && !$row->directory)
		{
			return response()->json(['message' => trans('Invalid storagedirid')], 415);
		}

		if ($request->has('timeperiodid') && !$row->timeperiod)
		{
			return response()->json(['message' => trans('Invalid timeperiodid')], 415);
		}

		if ($request->has('storagedirquotanotificationtypeid') && !$row->type)
		{
			return response()->json(['message' => trans('Invalid storagedirquotanotificationtypeid')], 415);
		}

		$row->save();

		return new JsonResource($row);
	}

	/**
	 * Delete a resource
	 *
	 * @apiMethod DELETE
	 * @apiUri    /storage/notifications/{id}
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
		$row = Notification::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
		}

		return response()->json(null, 204);
	}
}
