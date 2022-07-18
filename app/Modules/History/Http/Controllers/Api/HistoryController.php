<?php

namespace App\Modules\History\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Modules\History\Models\History;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Change History
 *
 * @apiUri    /history
 */
class HistoryController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /history
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
			'search'    => $request->input('search', null),
			'limit'     => $request->input('limit', config('list_limit', 20)),
			'order'     => $request->input('order', History::$orderBy),
			'order_dir' => $request->input('order_dir', History::$orderDir),
			'action'    => $request->input('action', null),
			'type'      => $request->input('type', null)
		);

		if (!in_array($filters['order'], ['id', 'created_at', 'action']))
		{
			$filters['order'] = History::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = History::$orderDir;
		}

		$query = History::query();

		if ($filters['search'])
		{
			$query->where(function($query) use ($filters)
			{
				$query->where('historable_type', 'like', '%' . $filters['search'] . '%')
					->orWhere('historable_table', 'like', '%' . $filters['search'] . '%');
			});
		}

		if ($filters['action'])
		{
			$query->where('action', '=', $filters['action']);
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit']);

		/*$rows->each(function ($row, $key)
		{
			$row->url = route('admin.history.show', ['id' => $row->id]);
			$row->formattedreport = $row->report;
		});*/

		return new ResourceCollection($rows);
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /history/{id}
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
	 * 						"id": 270955,
	 * 						"user_id": 1234,
	 * 						"historable_id": 51685,
	 * 						"historable_type": "App\\Modules\\Queues\\Models\\User",
	 * 						"historable_table": "queueusers",
	 * 						"action": "updated",
	 * 						"old": {
	 * 							"notice": 2
	 * 						},
	 * 						"new": {
	 * 							"notice": 0
	 * 						},
	 * 						"created_at": "2021-11-18T18:14:07.000000Z",
	 * 						"updated_at": "2021-11-18T18:14:07.000000Z"
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		}
	 * }
	 * @param  integer $id
	 * @return JsonResource
	 */
	public function read($id)
	{
		$row = History::findOrFail((int)$id);

		return new JsonResource($row);
	}
}
