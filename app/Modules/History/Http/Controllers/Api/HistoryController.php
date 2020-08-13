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
 * @apiUri    /api/history
 */
class HistoryController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /api/history
	 * @apiParameter {
	 * 		"name":          "action",
	 * 		"description":   "Action taken.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * 		"allowedValues": "create, update, delete"
	 * }
	 * @apiParameter {
	 * 		"name":          "type",
	 * 		"description":   "The type of item (model name) that the action was taken on.",
	 * 		"type":          "string",
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
	 * 		"name":          "order",
	 * 		"description":   "Field to sort results by.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "name",
	 * 		"allowedValues": "id, created_at"
	 * }
	 * @apiParameter {
	 * 		"name":          "order_dir",
	 * 		"description":   "Direction to sort results by.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "asc",
	 * 		"allowedValues": "asc, desc"
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

		if (!in_array($filters['order'], ['id', 'name']))
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
	 * @apiUri    /api/history/{id}
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
		$row = History::findOrFail((int)$id);

		return new JsonResource($row);
	}
}
