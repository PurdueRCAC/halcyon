<?php

namespace App\Modules\History\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Modules\History\Models\Log;

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
	 * @apiAuthorization  true
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
	 * 			"type":      "string"
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
	 * @return ResourceCollection
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
			'page'      => $request->input('page', 1),
			'order'     => $request->input('order', Log::$orderBy),
			'order_dir' => $request->input('order_dir', Log::$orderDir),
			'action'    => $request->input('action', null),
			'type'      => $request->input('type', null),
			'start'     => $request->input('start', null),
			'end'       => $request->input('end', null),
		);

		$filters['order'] = Log::getSortField($filters['order']);
		$filters['order_dir'] = Log::getSortDirection($filters['order_dir']);

		$rows = Log::query()
			->withFilters($filters)
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page'])
			->each(function ($row, $key)
			{
				$row->api = route('api.logs.read', ['id' => $row->id]);
			});

		return new ResourceCollection($rows);
	}

	/**
	 * Read a log entry
	 *
	 * @apiMethod GET
	 * @apiUri    /logs/{id}
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
	 * 						"api": "https://example.org/api/logs/8703798"
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		}
	 * }
	 * @param  int  $id
	 * @return JsonResource
	 */
	public function read($id)
	{
		$row = Log::findOrFail((int)$id);

		$row->api = route('api.logs.read', ['id' => $row->id]);

		return new JsonResource($row);
	}
}
