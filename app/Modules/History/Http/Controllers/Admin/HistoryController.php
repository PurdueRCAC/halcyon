<?php

namespace App\Modules\History\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use App\Halcyon\Http\StatefulRequest;
use App\Modules\History\Models\History;

class HistoryController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @param   StatefulRequest  $request
	 * @return  View
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
			'type'      => '',
			'start'     => null,
			'end'       => null,
		);

		$reset = false;
		$request = $request->mergeWithBase();
		foreach ($filters as $key => $default)
		{
			if ($key != 'page'
			 && $request->has($key)
			 && $request->input($key) != session()->get('history.filter_' . $key))
			{
				$reset = true;
			}
			$filters[$key] = $request->state('history.filter_' . $key, $key, $default);
		}
		$filters['page'] = $reset ? 1 : $filters['page'];

		$filters['order'] = History::getSortField($filters['order']);
		$filters['order_dir'] = History::getSortDirection($filters['order_dir']);

		$rows = History::query()
			->withFilters($filters)
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
	 * @param   int   $id
	 * @return  View
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
	 * @return  RedirectResponse
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
				$request->session()->flash('error', trans('global.messages.delete failed'));
				continue;
			}

			$success++;
		}

		if ($success)
		{
			$request->session()->flash('success', trans('global.messages.item deleted', ['count' => $success]));
		}

		return redirect(route('admin.history.index'));
	}
}
