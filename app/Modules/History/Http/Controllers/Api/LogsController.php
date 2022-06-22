<?php

namespace App\Modules\History\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Modules\History\Models\Log;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Logs
 *
 * @apiUri    /logs
 */
class LogsController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /logs
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "action",
	 * 		"description":   "Action taken.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"enum": [
	 * 				"create",
	 * 				"update",
	 * 				"delete"
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "type",
	 * 		"description":   "The type of item (model name) that the action was taken on.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "search"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "search",
	 * 		"description":   "A word or phrase to search for.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "search"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "limit",
	 * 		"description":   "Number of result per page.",
	 * 		"type":          "integer",
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
	 * 			"default":   "created_at",
	 * 			"enum": [
	 * 				"id",
	 * 				"created_at",
	 * 				"action"
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
	 * @param  Request $request
	 * @return Response
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = array(
			'status'    => $request->input('status', null),
			'ip'        => $request->input('ip', null),
			'userid'    => $request->input('userid', null),
			'transportmethod' => $request->input('transportmethod', null),
			'classname' => $request->input('classname', null),
			'classmethod' => $request->input('classmethod', null),
			'objectid' => $request->input('objectid', null),
			'groupid' => $request->input('groupid', null),
			'targetuserid' => $request->input('targetuserid', null),
			'targetobjectid' => $request->input('targetobjectid', null),
			'app'       => $request->input('app', null),
			'search'    => $request->input('search', null),
			'limit'     => $request->input('limit', config('list_limit', 20)),
			'order'     => $request->input('order', Log::$orderBy),
			'order_dir' => $request->input('order_dir', Log::$orderDir),
			'action'    => $request->input('action', null),
			'type'      => $request->input('type', null)
		);

		if (!in_array($filters['order'], ['id', 'name']))
		{
			$filters['order'] = Log::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Log::$orderDir;
		}

		$query = Log::query();

		if ($filters['search'])
		{
			$query->where(function($query) use ($filters)
			{
				$query->where('uri', 'like', '%' . $filters['search'] . '%')
					->orWhere('payload', 'like', '%' . $filters['search'] . '%');
			});
		}

		if ($filters['ip'])
		{
			$query->where('ip', '=', $filters['ip']);
		}

		if ($filters['status'])
		{
			$query->where('status', '=', $filters['status']);
		}

		if ($filters['transportmethod'])
		{
			$query->where('transportmethod', '=', $filters['transportmethod']);
		}

		if ($filters['classname'])
		{
			$query->where('classname', '=', $filters['classname']);
		}

		if ($filters['classmethod'])
		{
			$query->where('classmethod', '=', $filters['classmethod']);
		}

		if ($filters['userid'])
		{
			$query->where('userid', '=', $filters['userid']);
		}

		if ($filters['objectid'])
		{
			$query->where('objectid', '=', $filters['objectid']);
		}

		if ($filters['groupid'])
		{
			$query->where('groupid', '=', $filters['groupid']);
		}

		if ($filters['targetuserid'])
		{
			$query->where('targetuserid', '=', $filters['targetuserid']);
		}

		if ($filters['targetobjectid'])
		{
			$query->where('targetobjectid', '=', $filters['targetobjectid']);
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit']);

		$rows->each(function ($row, $key)
		{
			$row->url = route('api.logs.read', ['id' => $row->id]);
		});

		return new ResourceCollection($rows);
	}

	/**
	 * Create a news article type
	 *
	 * @apiMethod POST
	 * @apiUri    /news/types
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "comment",
	 * 		"description":   "The comment being made",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "name",
	 * 		"description":   "The name of the type",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 32
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "tagresources",
	 * 		"description":   "Allow articles to tag resources",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0,
	 * 			"enum": [
	 * 				0,
	 * 				1
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "tagusers",
	 * 		"description":   "Allow articles to tag users",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0,
	 * 			"enum": [
	 * 				0,
	 * 				1
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "location",
	 * 		"description":   "Allow articles to set location",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0,
	 * 			"enum": [
	 * 				0,
	 * 				1
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "future",
	 * 		"description":   "Allow articles to set future",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0,
	 * 			"enum": [
	 * 				0,
	 * 				1
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "ongoing",
	 * 		"description":   "Allow articles to set ongoing",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0,
	 * 			"enum": [
	 * 				0,
	 * 				1
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "url",
	 * 		"description":   "A URL associated with the news article",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0,
	 * 			"enum": [
	 * 				0,
	 * 				1
	 * 			]
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"201": {
	 * 			"description": "Successful entry creation",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": {
	 * 						"id": 870,
	 * 						"datetime": "2021-11-30T13:21:03.000000Z",
	 * 						"ip": "127.0.0.1",
	 * 						"hostname": "",
	 * 						"userid": 1234,
	 * 						"status": 200,
	 * 						"transportmethod": "GET",
	 * 						"servername": "example.org",
	 * 						"uri": "/admin/history/activity",
	 * 						"app": "ui",
	 * 						"classname": "ActivityController",
	 * 						"classmethod": "index",
	 * 						"objectid": "",
	 * 						"payload": "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Safari/605.1.15",
	 * 						"groupid": -2,
	 * 						"targetuserid": -2,
	 * 						"targetobjectid": -2,
	 * 						"url": "https://example.org/api/logs/8703798"
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"409": {
	 * 			"description": "Invalid data"
	 * 		}
	 * }
	 * @param   Request  $request
	 * @return  Response
	 */
	public function create(Request $request)
	{
		$rules = [
			'name' => 'required|string|max:32',
			'tagresources' => 'nullable|boolean',
			'location' => 'nullable|boolean',
			'future' => 'nullable|boolean',
			'ongoing' => 'nullable|boolean',
			'tagusers' => 'nullable|boolean',
			'calendar' => 'nullable|boolean',
			'url' => 'nullable|url',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = new Log($request->all());

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.creation failed')], 500);
		}

		$row->api = route('api.logs.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Read a news article type
	 *
	 * @apiMethod GET
	 * @apiUri    /news/types/{id}
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
	 * 						"id": 870,
	 * 						"datetime": "2021-11-30T13:21:03.000000Z",
	 * 						"ip": "127.0.0.1",
	 * 						"hostname": "",
	 * 						"userid": 1234,
	 * 						"status": 200,
	 * 						"transportmethod": "GET",
	 * 						"servername": "example.org",
	 * 						"uri": "/admin/history/activity",
	 * 						"app": "ui",
	 * 						"classname": "ActivityController",
	 * 						"classmethod": "index",
	 * 						"objectid": "",
	 * 						"payload": "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Safari/605.1.15",
	 * 						"groupid": -2,
	 * 						"targetuserid": -2,
	 * 						"targetobjectid": -2,
	 * 						"url": "https://example.org/api/logs/8703798"
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		}
	 * }
	 * @param  integer  $id
	 * @return Response
	 */
	public function read($id)
	{
		$row = Log::findOrFail((int)$id);

		$row->api = route('api.logs.read', ['id' => $row->id]);

		return new JsonResource($row);
	}
}
