<?php

namespace App\Modules\Messages\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Modules\Messages\Models\Message;
use App\Modules\Messages\Http\Resources\MessagesResource;
use App\Modules\Messages\Http\Resources\MessagesResourceCollection;
use Carbon\Carbon;

/**
 * Message
 *
 * @apiUri    /api/messages
 */
class MessagesController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /api/messages
	 * @apiParameter {
	 * 		"name":          "state",
	 * 		"description":   "Message state",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "pending",
	 * 		"allowedValues": "pending, complete, incomplete"
	 * }
	 * @apiParameter {
	 * 		"name":          "start",
	 * 		"description":   "Submitted datetime start",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       ""
	 * }
	 * @apiParameter {
	 * 		"name":          "stop",
	 * 		"description":   "Submitted datetime end",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       ""
	 * }
	 * @apiParameter {
	 * 		"name":          "returnstatus",
	 * 		"description":   "Filter by return status",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
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
	 * 		"default":       20
	 * }
	 * @apiParameter {
	 * 		"name":          "page",
	 * 		"description":   "Number of where to start returning results.",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       0
	 * }
	 * @apiParameter {
	 * 		"name":          "order",
	 * 		"description":   "Field to sort results by.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "name",
	 * 		"allowedValues": "id, userid, messagequeuetypeid, messagequeueoptionsid, pid, datetimesubmitted, datetimestarted, datetimecompleted, returnstatus"
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
	 * @param  Request $request
	 * @return Response
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = array(
			'state'     => 'incomplete',
			'start'     => null,
			'stop'      => null,
			'limit'     => config('list_limit', 20),
			'order'     => Message::$orderBy,
			'order_dir' => Message::$orderDir,
			'type'      => null,
			'returnstatus' => -1,
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->input($key, $default);
		}

		if (!in_array($filters['order'], array_keys((new Message)->getAttributes())))
		{
			$filters['order'] = Message::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Message::$orderDir;
		}

		$query = Message::query()
			->with('type');

		if ($filters['state'] == 'complete')
		{
			$query->whereNotNull('datetimestarted')
				->where('datetimestarted', '!=', '0000-00-00 00:00:00')
				->whereNotNull('datetimecompleted')
				->where('datetimecompleted', '!=', '0000-00-00 00:00:00');
		}
		elseif ($filters['state'] == 'incomplete')
		{
			$query->where(function($where)
			{
				$where->whereNull('datetimecompleted')
					->orWhere('datetimecompleted', '=', '0000-00-00 00:00:00');
			});
		}
		elseif ($filters['state'] == 'pending')
		{
			$query
				->where(function($where)
				{
					$where->whereNull('datetimestarted')
						->orWhere('datetimestarted', '=', '0000-00-00 00:00:00');
				})
				->where(function($where)
				{
					$where->whereNull('datetimecompleted')
						->orWhere('datetimecompleted', '=', '0000-00-00 00:00:00');
				});
		}

		if ($filters['start'])
		{
			$query->where('datetimesubmitted', '>', $filters['start']);
		}

		if ($filters['stop'])
		{
			$query->where('datetimesubmitted', '<=', $filters['stop']);
		}

		if ($filters['type'])
		{
			$query->where('messagequeuetypeid', '=', $filters['type']);
		}

		if ($filters['returnstatus'] >= 0)
		{
			$query->where('returnstatus', '=', $filters['returnstatus']);
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'])
			->appends(array_filter($filters));

		return new MessagesResourceCollection($rows);
	}

	/**
	 * Create an entry
	 *
	 * @apiMethod POST
	 * @apiUri    /api/messages
	 * @apiParameter {
	 * 		"name":          "messagequeuetypeid",
	 * 		"description":   "Message type ID.",
	 * 		"type":          "integer",
	 * 		"required":      true,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "targetobjectid",
	 * 		"description":   "Target object ID",
	 * 		"type":          "integer",
	 * 		"required":      true,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "messagequeueoptionsid",
	 * 		"description":   "Message options ID",
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
	 * @param  Request $request
	 * @return Response
	 */
	public function create(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'messagequeuetypeid' => 'required|integer|min:1',
			'targetobjectid' => 'required|integer|min:1',
			'userid' => 'nullable|integer',
			'messagequeueoptionsid' => 'nullable|integer',
		]);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()->first()], 409);
		}

		$row = new Message;
		$row->fill($request->all());
		$row->datetimesubmitted = Carbon::now()->toDateTimeString();

		if (!$row->save())
		{
			return response()->json(['message' => $row->getError()], 409);
		}

		return new MessagesResource($row);
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /api/messages/{id}
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @param  integer $id
	 * @return Response
	 */
	public function read($id): MessagesResource
	{
		$row = Message::findOrFail((int)$id);

		return new MessagesResource($row);
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /api/messages/{id}
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
	 * 		"name":          "userid",
	 * 		"description":   "User ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "messagequeuetypeid",
	 * 		"description":   "Message type ID.",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "targetobjectid",
	 * 		"description":   "Target object ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "messagequeueoptionsid",
	 * 		"description":   "Message options ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "pid",
	 * 		"description":   "Process ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "datetimestarted",
	 * 		"description":   "Datetime started (YYYY-MM-DD HH:mm:ss)",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "datetimecompleted",
	 * 		"description":   "Datetime completed (YYYY-MM-DD HH:mm:ss)",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "returnstatus",
	 * 		"description":   "Return status code",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "output",
	 * 		"description":   "Process output",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "started",
	 * 		"description":   "Starting processing",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null,
	 * 		"allowedValues": "0, 1"
	 * }
	 * @apiParameter {
	 * 		"name":          "completed",
	 * 		"description":   "Ending processing",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null,
	 * 		"allowedValues": "0, 1"
	 * }
	 * @apiParameter {
	 * 		"name":          "retry",
	 * 		"description":   "Number of minutes to retry in",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @param  Request $request
	 * @param  integer $id
	 * @return Response
	 */
	public function update(Request $request, $id)
	{
		$validator = Validator::make($request->all(), [
			'userid' => 'nullable|integer|min:1',
			'messagequeuetypeid' => 'nullable|integer|min:1',
			'targetobjectid' => 'nullable|integer|min:1',
			'messagequeueoptionsid' => 'nullable|integer|min:1',
			'pid' => 'nullable|integer|min:1',
			'datetimestarted' => 'nullable|date',
			'datetimecompleted' => 'nullable|date',
			'returnstatus' => 'nullable|integer|min:1',
			'output' => 'nullable|string|max:150',
			'started' => 'nullable|integer',
			'completed' => 'nullable|integer',
		]);

		if ($validator->fails()) //!$request->validated())
		{
			return response()->json(['message' => $validator->messages()->first()], 409);
		}

		$row = Message::findOrFail($id);

		$fields = $request->all();

		if (isset($fields['started']))
		{
			$fields['datetimestarted'] = Carbon::now()->toDateTimeString();
			unset($fields['started']);
		}

		if (isset($fields['completed']))
		{
			$fields['datetimecompleted'] = Carbon::now()->toDateTimeString();
			unset($fields['completed']);
		}

		if (isset($fields['retry']))
		{
			$row->datetimesubmitted = Carbon::now()->modify('+' . $fields['retry'] * 60)->toDateTimeString();
			$fields['datetimestarted'] = null; //'0000-00-00 00:00:00';
			unset($fields['retry']);
		}

		$row->fill($fields);

		if (!$row->save())
		{
			return response()->json(['message' => $row->getError()], 409);
		}

		return new MessagesResource($row);
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /api/messages/{id}
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
		$row = Message::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
		}

		return response()->json(null, 204);
	}
}
