<?php

namespace App\Modules\ContactReports\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Modules\ContactReports\Models\Type;
use App\Halcyon\Http\StatefulRequest;

class TypesController extends Controller
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
			'order'     => Type::$orderBy,
			'order_dir' => Type::$orderDir,
		);

		$reset = false;
		$request = $request->mergeWithBase();
		foreach ($filters as $key => $default)
		{
			if ($key != 'page'
			 && $request->has($key) && session()->has('crm.types.filter_' . $key)
			 && $request->input($key) != session()->get('crm.types.filter_' . $key))
			{
				$reset = true;
			}
			$filters[$key] = $request->state('crm.types.filter_' . $key, $key, $default);
		}
		$filters['page'] = $reset ? 1 : $filters['page'];

		if (!in_array($filters['order'], ['id', 'name']))
		{
			$filters['order'] = Type::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Type::$orderDir;
		}

		$query = Type::query();

		if ($filters['search'])
		{
			$query->where('name', 'like', '%' . $filters['search'] . '%');
		}

		$rows = $query
			->withCount('reports')
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		return view('contactreports::admin.types.index', [
			'filters' => $filters,
			'rows'    => $rows,
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return  Response
	 */
	public function create(Request $request)
	{
		$row = new Type();

		if ($fields = $request->old('fields'))
		{
			$row->fill($fields);
		}

		return view('contactreports::admin.types.edit', [
			'row' => $row
		]);
	}


	/**
	 * Show the form for editing the specified entry
	 *
	 * @param   integer   $id
	 * @return  Response
	 */
	public function edit(Request $request, $id)
	{
		$row = Type::findOrFail($id);

		if ($fields = $request->old('fields'))
		{
			$row->fill($fields);
		}

		return view('contactreports::admin.types.edit', [
			'row' => $row
		]);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param   Request  $request
	 * @return  Response
	 */
	public function store(Request $request)
	{
		//$request->validate([
		$rules = [
			'fields.name' => 'required|string|max:32',
			'fields.timeperiodid' => 'nullable|integer',
			'fields.timeperiodcount' => 'nullable|integer',
			'fields.timeperiodlimit' => 'nullable|integer',
			'fields.waitperiodid' => 'nullable|integer',
			'fields.waitperiodcount' => 'nullable|integer',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$id = $request->input('id');

		$row = $id ? Type::findOrFail($id) : new Type();
		$row->fill($request->input('fields'));

		foreach (['timeperiodid', 'timeperiodcount', 'timeperiodlimit', 'waitperiodid', 'waitperiodcount'] as $key)
		{
			if (!$request->has('fields.' . $key))
			{
				$row->{$key} = 0;
			}
		}

		if (!$row->save())
		{
			$error = $row->getError() ? $row->getError() : trans('global.messages.save failed');

			return redirect()->back()->withError($error);
		}

		return $this->cancel()->with('success', trans('global.messages.item saved'));
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
			$row = Type::findOrFail($id);

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
		return redirect(route('admin.contactreports.types'));
	}
}
