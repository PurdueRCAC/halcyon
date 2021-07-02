<?php

namespace App\Modules\History\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Halcyon\Http\StatefulRequest;
use App\Modules\History\Models\Log;

class ActivityController extends Controller
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
			'order'     => Log::$orderBy,
			'order_dir' => Log::$orderDir,
			'action'    => '',
			'transport' => '',
			'status'    => '',
		);

		$reset = false;
		foreach ($filters as $key => $default)
		{
			if ($key != 'page' && session()->get($key) != $request->mergeWithBase()->input($key))
			{
				$reset = true;
			}
			$filters[$key] = $request->state('history.activity.filter_' . $key, $key, $default);
		}
		$filters['page'] = $reset ? 1 : $filters['page'];

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
				$query->where('classname', 'like', '%' . $filters['search'] . '%')
					->orWhere('classmethod', 'like', '%' . $filters['search'] . '%')
					->orWhere('uri', 'like', '%' . $filters['search'] . '%');
			});
		}

		if ($filters['transport'])
		{
			$query->where('transportmethod', '=', $filters['transport']);
		}

		if ($filters['status'])
		{
			$query->where('status', '=', $filters['status']);
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		$types = Log::query()
			->select('classname')
			->distinct()
			->orderBy('classname', 'asc')
			->get();

		return view('history::admin.activity.index', [
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
		$row = Log::findOrFail($id);

		return view('history::admin.activity.show', [
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
			$row = Log::findOrFail($id);

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
		return redirect(route('admin.history.activity'));
	}
}
