<?php

namespace App\Modules\History\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Halcyon\Http\StatefulRequest;
use App\Modules\History\Models\History;

class HistoryController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @param   StatefulRequest  $request
	 * @return  Response
	 */
	public function index(StatefulRequest $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			'order'     => History::$orderBy,
			'order_dir' => History::$orderDir,
			'action'    => '',
			'type'      => ''
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->state('history.filter_' . $key, $key, $default);
		}

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
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		$types = History::query()
			->select('historable_type')
			->distinct()
			->orderBy('historable_type', 'asc')
			->get();

		return view('history::admin.history.index', [
			'filters' => $filters,
			'rows'    => $rows,
			'types'   => $types
		]);
	}

	/**
	 * Show the form for editing the specified entry
	 *
	 * @param   integer   $id
	 * @return  Response
	 */
	public function show($id)
	{
		$row = History::findOrFail($id);

		return view('history::admin.history.show', [
			'row' => $row
		]);
	}

	/**
	 * Remove the specified entry
	 *
	 * @param   Request  $request
	 * @return  Response
	 */
	public function delete(Request $request)
	{
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		foreach ($ids as $id)
		{
			$row = History::findOrFail($id);

			if (!$row->delete())
			{
				$request->session()->flash('error', $row->getError());
				continue;
			}

			$success++;
		}

		if ($success)
		{
			$request->session()->flash('success', trans('global.messages.item deleted', ['count' => $success]));
		}

		return $this->cancel();
	}

	/**
	 * Return to the main view
	 *
	 * @return  Response
	 */
	public function cancel()
	{
		return redirect(route('admin.history.index'));
	}
}
