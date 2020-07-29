<?php

namespace App\Modules\History\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Modules\History\Models\History;

class HistoryController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @param   Request  $request
	 * @return  Response
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

		return $rows;
	}

	/**
	 * Retrieve a specified entry
	 *
	 * @param   Request $request
	 * @return  Response
	 */
	public function read($id)
	{
		$row = History::findOrFail((int)$id);

		return $row;
	}

	/**
	 * Remove the specified entry
	 *
	 * @return  Response
	 */
	/*public function destroy(History $row)
	{
		//$row = History::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json([
				'errors'  => false,
				'message' => trans('histiory::messages.page deleted'),
			]);
		}

		return response()->json([
			'errors'  => true,
			'message' => trans('histiory::messages.page deleted'),
		]);
	}*/
}
