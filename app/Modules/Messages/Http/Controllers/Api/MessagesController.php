<?php

namespace App\Modules\Messages\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Modules\Messages\Models\Message;
use App\Modules\Messages\Http\Resources\MessageResource;
use App\Modules\Messages\Http\Resources\MessageResourceCollection;
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
	 * 		"in":            "query",
	 * 		"name":          "state",
	 * 		"description":   "Message state",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "pending",
	 * 			"enum": [
	 * 				"pending",
	 * 				"complete",
	 * 				"incomplete"
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "start",
	 * 		"description":   "Submitted datetime start",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date-time",
	 * 			"example":   "2021-01-30T08:30:00Z"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "stop",
	 * 		"description":   "Submitted datetime end",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date-time",
	 * 			"example":   "2021-01-30T08:30:00Z"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "returnstatus",
	 * 		"description":   "Filter by return status",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
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
	 * 		"name":          "limit",
	 * 		"description":   "Number of result per page.",
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
	 * 				"messagequeuetypeid",
	 * 				"messagequeueoptionsid",
	 * 				"pid",
	 * 				"datetimesubmitted",
	 * 				"datetimestarted",
	 * 				"datetimecompleted",
	 * 				"returnstatus"
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

		if (request()->segment(1) == 'ws')
		{
			$rows = $query
				->limit(1000)
				->orderBy($filters['order'], $filters['order_dir'])
				->get();

			$items = array();

			foreach ($rows as $row)
			{
				$data = $row->toArray();

				$data['id'] = '/ws/messagequeue/' . $data['id'];
				$data['messagequeuetype'] = '/ws/messagequeuetype/' . $data['messagequeuetypeid'];
				$data['datetimesubmitted'] = $row->datetimesubmitted->toDateTimeString();
				$data['datetimestarted'] = $row->datetimestarted->toDateTimeString();
				$data['datetimecompleted'] = $row->datetimecompleted->toDateTimeString();
				$data['submitted'] = $data['datetimesubmitted'];
				$data['started'] = $row->started() ? $data['datetimestarted'] : '0000-00-00 00:00:00';
				$data['completed'] = $row->completed() ? $data['datetimecompleted']->toDateTimeString() : '0000-00-00 00:00:00';
				$data['user'] = '/ws/user/' . $data['userid'];
				$data['targetobject'] = '/ws/' . ($row->type ? $row->type->classname : 'unknown') . '/' . $data['targetobjectid'];

				$items[] = $data;
			}

			return response()->json($items, 200);
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'])
			->appends(array_filter($filters));

		return new MessageResourceCollection($rows);
	}

	/**
	 * Create an entry
	 *
	 * @apiMethod POST
	 * @apiUri    /api/messages
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "messagequeuetypeid",
	 * 		"description":   "Message type ID.",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "targetobjectid",
	 * 		"description":   "Target object ID",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "messagequeueoptionsid",
	 * 		"description":   "Message options ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "userid",
	 * 		"description":   "User ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @param  Request $request
	 * @return Response
	 */
	public function create(Request $request)
	{
		$rules = [
			'messagequeuetypeid' => 'required|integer|min:1',
			'targetobjectid' => 'required|integer|min:1',
			'userid' => 'nullable|integer',
			'messagequeueoptionsid' => 'nullable|integer',
		];
		// [!] Legacy compatibility
		if ($request->segment(1) == 'ws')
		{
			$rules = [
				'messagequeuetype' => 'required|string',
				'targetobject' => 'required|string',
				'user' => 'nullable|integer',
				'messagequeueoptions' => 'nullable|integer',
			];
		}

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()->first()], 409);
		}

		$row = new Message;
		$row->messagequeuetypeid = $request->input('messagequeuetypeid');
		$row->targetobjectid = $request->input('targetobjectid');
		if ($request->has('userid'))
		{
			$row->userid = $request->input('userid');
		}
		if ($request->has('messagequeueoptionsid'))
		{
			$row->messagequeueoptionsid = $request->input('messagequeueoptionsid');
		}

		// Legacy compatibility
		if ($request->segment(1) == 'ws')
		{
			$row->messagequeuetypeid = $request->input('messagequeuetype');
			$row->targetobjectid = $request->input('targetobject');
			if ($request->has('userid'))
			{
				$row->userid = $request->input('user');
			}
			if ($request->has('messagequeueoptionsid'))
			{
				$row->messagequeueoptionsid = preg_replace('/[a-zA-Z\/]+\/(\d+)/', "$1", $request->input('messagequeueoptions'));
			}
		}

		$row->datetimesubmitted = Carbon::now()->toDateTimeString();

		if (!$row->save())
		{
			return response()->json(['message' => $row->getError()], 409);
		}

		return new MessageResource($row);
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
	public function read($id): MessageResource
	{
		$row = Message::findOrFail((int)$id);

		return new MessageResource($row);
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /api/messages/{id}
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
	 * 		"name":          "messagequeuetypeid",
	 * 		"description":   "Message type ID.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "targetobjectid",
	 * 		"description":   "Target object ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "messagequeueoptionsid",
	 * 		"description":   "Message options ID",
	 * 		"required":      false,
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
	 * 		"name":          "pid",
	 * 		"description":   "Process ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "datetimestarted",
	 * 		"description":   "Datetime started",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date-time",
	 * 			"example":   "2021-01-30T08:30:00Z"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "datetimecompleted",
	 * 		"description":   "Datetime completed",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date-time",
	 * 			"example":   "2021-01-30T08:30:00Z"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "returnstatus",
	 * 		"description":   "Return status code",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "output",
	 * 		"description":   "Process output",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 150
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "started",
	 * 		"description":   "Starting processing",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"enum": [
	 * 				0,
	 * 				1
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "completed",
	 * 		"description":   "Ending processing",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"enum": [
	 * 				0,
	 * 				1
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "retry",
	 * 		"description":   "Number of minutes to retry in",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
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
			'returnstatus' => 'nullable|integer',
			'output' => 'nullable|string|max:150',
			// Shortcut flags
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

		// [!] Legacy compatibility
		if ($request->segment(1) == 'ws')
		{
			if ($request->has('messagequeuetype'))
			{
				$fields['messagequeuetypeid'] = $request->input('messagequeuetype');
			}
			if ($request->has('targetobject'))
			{
				$fields['targetobjectid'] = $request->input('targetobject');
			}
			if ($request->has('userid'))
			{
				$fields['userid'] = $request->input('user');
			}
			if ($request->has('messagequeueoptionsid'))
			{
				$fields['messagequeueoptionsid'] = $request->input('messagequeueoptions');
			}
		}

		$row->fill($fields);

		if (!$row->save())
		{
			return response()->json(['message' => $row->getError()], 409);
		}

		return new MessageResource($row);
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /api/messages/{id}
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
		$row = Message::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
		}

		return response()->json(null, 204);
	}
}
