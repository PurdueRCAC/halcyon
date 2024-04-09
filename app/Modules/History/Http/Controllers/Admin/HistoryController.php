<?php

namespace App\Modules\History\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use App\Halcyon\Http\Concerns\UsesFilters;
use App\Modules\History\Models\History;

class HistoryController extends Controller
{
	use UsesFilters;

	/**
	 * Display a listing of the resource.
	 *
	 * @param   Request  $request
	 * @return  View
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = $this->getStatefulFilters($request, 'history', [
			'search'    => null,
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			'order'     => History::$orderBy,
			'order_dir' => History::$orderDir,
			'action'    => '',
			'type'      => '',
			'start'     => null,
			'end'       => null,
		]);

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
