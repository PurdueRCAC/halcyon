<?php

namespace App\Modules\Storage\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Modules\Storage\Models\Notification\Type;
use App\Halcyon\Http\StatefulRequest;
use App\Halcyon\Models\Timeperiod;

class NotificationTypesController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * 
	 * @param  StatefulRequest $request
	 * @return View
	 */
	public function index(StatefulRequest $request): View
	{
		$filters = array(
			'search'   => '',
			'state'    => 'active',
			// Paging
			'limit'    => config('list_limit', 20),
			'page'     => 1,
			// Sorting
			'order'     => 'name',
			'order_dir' => 'asc'
		);

		$reset = false;
		$request = $request->mergeWithBase();
		foreach ($filters as $key => $default)
		{
			if ($key != 'page'
			 && $request->has($key) //&& session()->has('storage.notifytypes.filter_' . $key)
			 && $request->input($key) != session()->get('storage.notifytypes.filter_' . $key))
			{
				$reset = true;
			}
			$filters[$key] = $request->state('storage.notifytypes.filter_' . $key, $key, $default);
		}
		$filters['page'] = $reset ? 1 : $filters['page'];

		// Get records
		$query = Type::query();

		if ($filters['search'])
		{
			if (is_numeric($filters['search']))
			{
				$query->where('id', '=', $filters['search']);
			}
			else
			{
				$query->where('name', 'like', '%' . $filters['search'] . '%');
			}
		}

		$rows = $query
			->withCount('notifications')
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page'])
			->appends(array_filter($filters));

		return view('storage::admin.types.index', [
			'rows'    => $rows,
			'filters' => $filters
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @param  Request $request
	 * @return View
	 */
	public function create(Request $request): View
	{
		$row = new Type;
		$timeperiods = Timeperiod::all();

		if ($fields = $request->old('fields'))
		{
			$row->fill($fields);
		}

		return view('storage::admin.types.edit', [
			'row'   => $row,
			'timeperiods' => $timeperiods,
		]);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  Request $request
	 * @param  int  $id
	 * @return View
	 */
	public function edit(Request $request, $id): View
	{
		$row = Type::find($id);
		$timeperiods = Timeperiod::all();

		if ($fields = $request->old('fields'))
		{
			$row->fill($fields);
		}

		return view('storage::admin.types.edit', [
			'row'   => $row,
			'timeperiods' => $timeperiods,
		]);
	}

	/**
	 * Store a newly created resource in storage.
	 * 
	 * @param  Request $request
	 * @return RedirectResponse
	 */
	public function store(Request $request): RedirectResponse
	{
		$rules = [
			'fields.name' => 'required|string|max:100',
			'fields.defaulttimeperiodid' => 'nullable|integer',
			'fields.valuetype' => 'required|integer|min:1'
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$id = $request->input('id');

		$row = Type::findOrNew($id);

		$row->fill($request->input('fields'));

		if (!$row->save())
		{
			return redirect()->back()->withError(trans('global.messages.save failed'));
		}

		return $this->cancel()->with('success', trans('global.messages.item saved'));
	}

	/**
	 * Remove the specified items
	 *
	 * @param   Request $request
	 * @return  RedirectResponse
	 */
	public function delete(Request $request): RedirectResponse
	{
		// Incoming
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		foreach ($ids as $id)
		{
			// Delete the entry
			// Note: This is recursive and will also remove all descendents
			$row = Type::find($id);

			if (!$row)
			{
				continue;
			}

			if ($row->notifications()->count())
			{
				$request->session()->flash('error', trans('storage::storage.error.not empty'));
				continue;
			}

			if (!$row->delete())
			{
				$request->session()->flash('error', trans('global.messages.delete failed'));
				continue;
			}

			$success++;
		}

		if ($success)
		{
			$request->session()->flash('success', trans('global.messages.item deleted', ['number' => $success]));
		}

		return redirect(route('admin.storage.types'));
	}

	/**
	 * Return to the main view
	 *
	 * @return  RedirectResponse
	 */
	public function cancel(): RedirectResponse
	{
		return redirect(route('admin.storage.types'));
	}
}
