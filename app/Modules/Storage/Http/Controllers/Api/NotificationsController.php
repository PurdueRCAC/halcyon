<?php

namespace App\Modules\Storage\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Modules\Storage\Models\Notification;
use Carbon\Carbon;

/**
 * Notifications
 *
 * @apiUri    /storage/notifications
 */
class NotificationsController extends Controller
{
	/**
	 * Display a listing of notifications.
	 *
	 * @apiMethod GET
	 * @apiUri    /storage/notifications
	 * @apiAuthorization  true
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
	 * 		"name":          "enabled",
	 * 		"description":   "Enabled state of the notifications",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "-1",
	 * 			"enum": [
	 * 				"-1",
	 * 				"0",
	 * 				"1"
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "storagedirquotanotificationtypeid",
	 * 		"description":   "ID of the notification type to filter by.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "storagedirid",
	 * 		"description":   "ID of the storage directory to filter by.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "userid",
	 * 		"description":   "ID of the user to filter by.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "timeperiodid",
	 * 		"description":   "ID of the timeperiod to filter by",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   "-1"
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
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "order",
	 * 		"description":   "Field to sort results by.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "datetimestart",
	 * 			"enum": [
	 * 				"id",
	 * 				"storagedirid",
	 * 				"userid",
	 * 				"datetimecreated",
	 * 				"datetimeremoved",
	 * 				"enabled",
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
	 * 			"default":   "desc",
	 * 			"enum": [
	 * 				"asc",
	 * 				"desc"
	 * 			]
	 * 		}
	 * }
	 * @param  Request  $request
	 * @return ResourceCollection
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
			'page'      => $request->input('page', 1),
			// Sorting
			'order'     => $request->input('order', 'id'),
			'order_dir' => $request->input('order_dir', 'desc')
		);

		if (!in_array($filters['order'], ['id', 'storagedirid', 'userid', 'notice', 'datetimecreated', 'datetimeremoved', 'enabled']))
		{
			$filters['order'] = 'id';
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = 'desc';
		}

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
			->paginate($filters['limit'], ['*'], 'page', $filters['page'])
			->appends($filters);

		$rows->each(function($item, $key)
		{
			$item->api = route('api.storage.notifications.read', ['id' => $item->id]);
		});

		return new ResourceCollection($rows);
	}

	/**
	 * Create a notification.
	 *
	 * @apiMethod POST
	 * @apiUri    /storage/notifications
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "storagedirid",
	 * 		"description":   "Storage directory ID",
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
	 * 		"name":          "storagedirquotanotificationtypeid",
	 * 		"description":   "Storage quota notification type ID",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "value",
	 * 		"description":   "Notification threshold",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "timeperiodid",
	 * 		"description":   "Time period ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "periods",
	 * 		"description":   "Periods",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "notice",
	 * 		"description":   "Notice state",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "enabled",
	 * 		"description":   "Enabled state",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   1
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "datetimelastnotify",
	 * 		"description":   "Last notified timestamp",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date-time",
	 * 			"example":   "2021-01-30T08:30:00Z"
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"201": {
	 * 			"description": "Successful entry creation"
	 * 		},
	 * 		"401": {
	 * 			"description": "Unauthorized"
	 * 		},
	 * 		"409": {
	 * 			"description": "Invalid data"
	 * 		}
	 * }
	 * @param  Request $request
	 * @return JsonResource|JsonResponse
	 */
	public function create(Request $request)
	{
		$rules = [
			'storagedirid'       => 'required|integer|min:1',
			'userid'             => 'nullable|integer',
			'storagedirquotanotificationtypeid' => 'required|integer|min:1',
			'value'              => 'nullable|integer',
			'timeperiodid'       => 'nullable|integer',
			'periods'            => 'nullable|integer|min:1',
			'notice'             => 'nullable|integer',
			'enabled'            => 'nullable|integer',
			'datetimelastnotify' => 'nullable|date',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = new Notification;
		$row->enabled = 1;
		foreach ($rules as $key => $rule)
		{
			if ($request->has($key))
			{
				$row->{$key} = $request->input($key);
			}
		}

		if (!$row->userid)
		{
			$row->userid = auth()->user()->id;
		}

		if (!$row->directory)
		{
			return response()->json(['message' => trans('Invalid storagedirid')], 415);
		}

		if (!$row->directory->groupid)
		{
			//return response()->json(['message' => trans('Failed to retrieve `groupid` for `storagedirid`')], 415);
		}

		// Ensure the client is authorized to create storagedirs.
		$groupid = $row->directory->groupid;
		$ownedgroups = auth()->user()->groups()
			->whereIsManager()
			->get()
			->pluck('groupid')
			->toArray();

		if ($row->userid != auth()->user()->id
		 && !auth()->user()->can('manage storage')
		 && ($groupid && !in_array($groupid, $ownedgroups)))
		{
			return response()->json(null, 403);
		}

		if ($request->has('timeperiodid') && !$row->timeperiod)
		{
			return response()->json(['message' => trans('Invalid timeperiodid')], 415);
		}

		if (!$row->type)
		{
			return response()->json(['message' => trans('Invalid storagedirquotanotificationtypeid')], 415);
		}

		if ($row->type->id == 1)
		{
			$row->datetimelastnotify = Carbon::now();
		}

		$row->save();
		$row->api = route('api.storage.notifications.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Read a notification.
	 *
	 * @apiMethod GET
	 * @apiUri    /storage/notifications/{id}
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
	 * @param   int  $id
	 * @return  JsonResource
	 */
	public function read($id)
	{
		$row = Notification::findOrFail($id);
		$row->api = route('api.storage.notifications.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Update a notification.
	 *
	 * @apiMethod PUT
	 * @apiUri    /storage/notifications/{id}
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
	 * 		"name":          "userid",
	 * 		"description":   "User ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "storagedirquotanotificationtypeid",
	 * 		"description":   "Storage quota notification type ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "value",
	 * 		"description":   "Notification threshold",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "timeperiodid",
	 * 		"description":   "Time period ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "periods",
	 * 		"description":   "Periods",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "notice",
	 * 		"description":   "Notice state",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "enabled",
	 * 		"description":   "Enabled state",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   1
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "datetimelastnotify",
	 * 		"description":   "Last notified timestamp",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date-time",
	 * 			"example":   "2021-01-30T08:30:00Z"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "nextreport",
	 * 		"description":   "When the next report should be",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date-time",
	 * 			"example":   "2021-01-30T08:30:00Z"
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"202": {
	 * 			"description": "Successful entry modification"
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		},
	 * 		"409": {
	 * 			"description": "Invalid data"
	 * 		}
	 * }
	 * @param   int  $id
	 * @param   Request  $request
	 * @return  JsonResource|JsonResponse
	 */
	public function update($id, Request $request)
	{
		$rules = [
			'storagedirid'       => 'nullable|integer|min:1',
			'userid'             => 'nullable|integer',
			'storagedirquotanotificationtypeid' => 'nullable|integer',
			'value'              => 'nullable|integer',
			'timeperiodid'       => 'nullable|integer',
			'periods'            => 'nullable|integer|min:0',
			'notice'             => 'nullable|integer',
			'enabled'            => 'nullable|integer',
			'datetimelastnotify' => 'nullable|date',
			'nextreport'         => 'nullable|date',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = Notification::findOrFail($id);
		foreach ($rules as $key => $rule)
		{
			if ($key == 'nextreport')
			{
				continue;
			}
			if ($request->has($key))
			{
				$row->{$key} = $request->input($key);
			}
		}

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

		if ($request->has('nextreport'))
		{
			$timeperiod = $row->timeperiod;
			$row->datetimelastnotify = $timeperiod->calculateDateFrom($request->input('nextreport'));
		}

		$row->save();
		$row->api = route('api.storage.notifications.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Delete a notification.
	 *
	 * @apiMethod DELETE
	 * @apiUri    /storage/notifications/{id}
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
	 * @param  int  $id
	 * @return JsonResponse
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
