<?php

namespace App\Modules\Knowledge\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Modules\Knowledge\Models\Feedback;

/**
 * Feedback
 *
 * @apiUri    /knowledge/feedback
 */
class FeedbackController extends Controller
{
	/**
	 * Display a listing of feedback
	 *
	 * @apiMethod GET
	 * @apiUri    /knowledge/feedback
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "user_id",
	 * 		"description":   "The user ID of the feedback submitter.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "target_id",
	 * 		"description":   "The page association the feedback is related to.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "type",
	 * 		"description":   "The type of feedback.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   null,
	 * 			"enum": [
	 * 				"positive",
	 * 				"neutral",
	 * 				"negative"
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "start",
	 * 		"description":   "Feedback created on or after this datetime.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "",
	 * 			"format":    "date-time",
	 * 			"example":   "2021-01-30T08:30:00Z"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "stop",
	 * 		"description":   "Feedback created before this datetime.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "",
	 * 			"format":    "date-time",
	 * 			"example":   "2021-01-30T08:30:00Z"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "ip",
	 * 		"description":   "The IP address of the feedback submitter.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   ""
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "search",
	 * 		"description":   "A word or phrase to search for in feedback comments.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   ""
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "limit",
	 * 		"description":   "Number of result per page.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   25
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
	 * 			"default":   "created_at",
	 * 			"enum": [
	 * 				"id",
	 * 				"created_at",
	 * 				"ip",
	 * 				"user_id",
	 * 				"target_id",
	 * 				"type"
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
	 * @param  Request  $request
	 * @return Response
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			'type'      => null,
			'target_id' => 0,
			'user_id'   => 0,
			'ip'        => null,
			'start'     => null,
			'stop'      => null,
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			'order'     => Feedback::$orderBy,
			'order_dir' => Feedback::$orderDir,
			'level'     => 0,
		);

		$refresh = false;
		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->input($key, $default);
		}

		if ($refresh)
		{
			$filters['page'] = 1;
		}

		if (!in_array($filters['order'], ['id', 'target_id', 'ip', 'type', 'user_id', 'created_at']))
		{
			$filters['order'] = Feedback::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Feedback::$orderDir;
		}

		// If the user isn't a manager, force all feedback to just theirs
		if (auth()->user() && !auth()->user()->can('manage knowledge'))
		{
			$filters['user_id'] = auth()->user()->id;
		}

		$query = Feedback::query();

		if ($filters['search'])
		{
			$query->where('comments', 'like', '%' . $filters['search'] . '%');
		}

		if ($filters['target_id'])
		{
			$query->where('target_id', '=', $filters['target_id']);
		}

		if ($filters['ip'])
		{
			$query->where('ip', '=', $filters['ip']);
		}

		if ($filters['type'])
		{
			$query->where('type', '=', $filters['type']);
		}

		if ($filters['user_id'])
		{
			$query->where('user_id', '=', $filters['user_id']);
		}

		if ($filters['start'])
		{
			$query->where('start', '>=', $filters['start']);
		}

		if ($filters['stop'])
		{
			$query->where('stop', '<', $filters['stop']);
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		$rows->each(function($item, $i)
		{
			$item->api = route('api.knowledge.feedback.read', ['id' => $item->id]);
		});

		return new ResourceCollection($rows);
	}

	/**
	 * Create an entry
	 *
	 * @apiMethod POST
	 * @apiUri    /knowledge/feedback
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "user_id",
	 * 		"description":   "ID of user making the feedback",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "target_id",
	 * 		"description":   "Targetted page association ID",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "comments",
	 * 		"description":   "User comments",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "type",
	 * 		"description":   "Type of feedback (e.g., positive, nuetral, negative)",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"201": {
	 * 			"description": "Successful entry creation",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": {
	 * 						"id": 1,
	 * 						"target_id": 5020,
	 * 						"ip": "10.195.27.2",
	 * 						"type": "positive",
	 * 						"user_id": 0,
	 * 						"created_at": "2021-03-04T23:29:24.000000Z",
	 * 						"updated_at": "2021-03-04T23:29:24.000000Z",
	 * 						"comments": "Testing the helpful commments",
	 * 						"api": "https://example.org/api/knowledge/feedback/1"
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"401": {
	 * 			"description": "Unauthorized"
	 * 		},
	 * 		"409": {
	 * 			"description": "Invalid data"
	 * 		}
	 * }
	 * @param  Request $request
	 * @return Response
	 */
	public function create(Request $request)
	{
		$rules = [
			'target_id' => 'required|integer',
			//'ip'        => 'nullable|string|max:15',
			'type'      => 'required|string|max:10',
			'user_id'   => 'nullable|integer',
			'comments'  => 'nullable|string|max:255',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = new Feedback;
		$row->target_id = $request->input('target_id');
		$row->ip = $request->ip();
		$row->type = $request->input('type');
		if (auth()->user())
		{
			$row->user_id = auth()->user()->id;
		}
		if ($request->has('comments'))
		{
			$row->comments = $request->input('comments');
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.save failed')], 409);
		}

		$row->api = route('api.knowledge.feedback.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /knowledge/feedback/{id}
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
	 * 			"description": "Successful entry read",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": {
	 * 						"id": 1,
	 * 						"target_id": 5020,
	 * 						"ip": "10.195.27.2",
	 * 						"type": "positive",
	 * 						"user_id": 0,
	 * 						"created_at": "2021-03-04T23:29:24.000000Z",
	 * 						"updated_at": "2021-03-04T23:29:24.000000Z",
	 * 						"comments": "Testing the helpful commments",
	 * 						"api": "https://example.org/api/knowledge/feedback/1"
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		}
	 * }
	 * @param  integer $id
	 * @return Response
	 */
	public function read($id)
	{
		$row = Feedback::findOrFail((int)$id);

		// If the user isn't a manager, force all feedback to just theirs
		if (!auth()->user()->can('manage knowledge') && $row->user_id != auth()->user()->id)
		{
			return response()->json(['message' => trans('global.messages.not found')], 404);
		}

		$row->api = route('api.knowledge.feedback.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /knowledge/feedback/{id}
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
	 * 		"name":          "comments",
	 * 		"description":   "User comments",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "type",
	 * 		"description":   "Type of feedback (e.g., positive, nuetral, negative)",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"202": {
	 * 			"description": "Successful entry modification",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": {
	 * 						"id": 1,
	 * 						"target_id": 5020,
	 * 						"ip": "10.195.27.2",
	 * 						"type": "positive",
	 * 						"user_id": 0,
	 * 						"created_at": "2021-03-04T23:29:24.000000Z",
	 * 						"updated_at": "2021-03-04T23:45:01.000000Z",
	 * 						"comments": "Updated comment",
	 * 						"api": "https://example.org/api/knowledge/feedback/1"
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		},
	 * 		"409": {
	 * 			"description": "Invalid data"
	 * 		}
	 * }
	 * @param   Request $request
	 * @param   integer $id
	 * @return  Response
	 */
	public function update(Request $request, $id)
	{
		$rules = [
			'type'      => 'nullable|string|max:10',
			'comments'  => 'nullable|string|max:255',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = Feedback::findOrFail($id);
		if ($request->has('comments'))
		{
			$row->comments = $request->input('comments');
		}
		if ($request->has('type'))
		{
			$row->type = $request->input('type');
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.save failed')], 409);
		}

		$row->api = route('api.knowledge.feedback.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /knowledge/feedback/{id}
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
	 * @param  integer $id
	 * @return Response
	 */
	public function delete($id)
	{
		$row = Feedback::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
		}

		return response()->json(null, 204);
	}
}
