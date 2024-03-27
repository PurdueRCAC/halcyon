<?php

namespace App\Modules\History\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Halcyon\Http\StatefulRequest;
use App\Modules\History\Models\Log;

class ActivityController extends Controller
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
			'app'       => null,
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			'order'     => Log::$orderBy,
			'order_dir' => Log::$orderDir,
			'action'    => '',
			'transportmethod' => '',
			'status'    => '',
			'start'     => null,
			'end'       => null,
		);

		$reset = false;
		$request = $request->mergeWithBase();
		foreach ($filters as $key => $default)
		{
			if ($key != 'page'
			 && $request->has($key)
			 && $request->input($key) != session()->get('history.activity.filter_' . $key))
			{
				$reset = true;
			}
			$filters[$key] = $request->state('history.activity.filter_' . $key, $key, $default);
		}
		$filters['page'] = $reset ? 1 : $filters['page'];

		$filters['order'] = Log::getSortField($filters['order']);
		$filters['order_dir'] = Log::getSortDirection($filters['order_dir']);

		$rows = Log::query()
			->withFilters($filters)
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		$types = Log::query()
			->select('classname')
			->distinct()
			->orderBy('classname', 'asc')
			->get();

		$apps = Log::query()
			->select(DB::raw('DISTINCT(app)'))
			->where('app', '!=', 'api')
			->get();

		return view('history::admin.activity.index', [
			'filters' => $filters,
			'rows'    => $rows,
			'types'   => $types,
			'apps'    => $apps,
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
		$row = Log::findOrFail($id);

		return view('history::admin.activity.show', [
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
			$row = Log::findOrFail($id);

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

		return redirect(route('admin.history.activity'));
	}
}
