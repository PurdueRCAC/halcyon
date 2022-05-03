<?php

namespace App\Modules\Groups\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Halcyon\Http\StatefulRequest;
use App\Halcyon\Models\FieldOfScience;
//use App\Modules\Groups\Models\FieldOfScience;

class FieldsOfScienceController extends Controller
{
	/**
	 * Display a listing of tags
	 *
	 * @param  StatefulRequest $request
	 * @return Response
	 */
	public function index(StatefulRequest $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			'parent'    => 1,
			// Paging
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			// Sorting
			'order'     => FieldOfScience::$orderBy,
			'order_dir' => FieldOfScience::$orderDir,
		);

		$reset = false;
		$request = $request->mergeWithBase();
		foreach ($filters as $key => $default)
		{
			if ($key != 'page'
			 && $request->has($key) //&& session()->has('groups.fos.filter_' . $key)
			 && $request->input($key) != session()->get('groups.fos.filter_' . $key))
			{
				$reset = true;
			}
			$filters[$key] = $request->state('groups.fos.filter_' . $key, $key, $default);
		}
		$filters['page'] = $reset ? 1 : $filters['page'];
		$filters['start'] = ($filters['limit'] * $filters['page']) - $filters['limit'];

		if (!in_array($filters['order'], array('id', 'name')))
		{
			$filters['order'] = FieldOfScience::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = FieldOfScience::$orderDir;
		}

		if ($filters['search'])
		{
			$query = FieldOfScience::query();

			if (is_numeric($filters['search']))
			{
				$query->where('id', '=', $filters['search']);
			}
			else
			{
				/*$filters['search'] = strtolower((string)$filters['search']);

				$query->where('name', 'like', '%' . $filters['search'] . '%');*/

				$query->where(function($where) use ($filters)
				{
					$search = strtolower((string)$filters['search']);
					$skipmiddlename = preg_replace('/ /', '% ', $search);

					$where->where('name', 'like', '% ' . $search . '%')
						->orWhere('name', 'like', $search . '%')
						->orWhere('name', 'like', '% ' . $skipmiddlename . '%')
						->orWhere('name', 'like', $skipmiddlename . '%');
				});
			}

			/*if ($filters['parent'])
			{
				$query->where('parentid', '=', $filters['parent']);
			}*/
			$query->where('parentid', '>', 0);

			$rows = $query
				->withCount('groups')
				->orderBy($filters['order'], $filters['order_dir'])
				->get();

			$total = count($rows);

			$rows = $rows->slice($filters['start'], $filters['limit']);
		}
		else
		{
			$rows = FieldOfScience::tree($filters['order'], $filters['order_dir']);
			$root = array_shift($rows);

			$total = count($rows);
			$rows = array_slice($rows, $filters['start'], $filters['limit']);
		}

		$paginator = new \Illuminate\Pagination\LengthAwarePaginator($rows, $total, $filters['limit'], $filters['page']);
		$paginator->withPath(route('admin.groups.fieldsofscience'));

		return view('groups::admin.fieldsofscience.index', [
			'rows'    => $rows,
			'filters' => $filters,
			'paginator' => $paginator,
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		$parents = FieldOfScience::tree();

		$row = new FieldOfScience();

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		return view('groups::admin.fieldsofscience.edit', [
			'row' => $row,
			'parents' => $parents,
		]);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  integer $id
	 * @return Response
	 */
	public function edit($id)
	{
		$parents = FieldOfScience::tree();

		$row = FieldOfScience::findOrFail($id);

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		return view('groups::admin.fieldsofscience.edit', [
			'row' => $row,
			'parents' => $parents,
		]);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  Request $request
	 * @return Response
	 */
	public function store(Request $request)
	{
		//$request->validate([
		$rules = [
			'fields.name' => 'required|string|max:255'
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$id = $request->input('id');

		$row = $id ? FieldOfScience::findOrFail($id) : new FieldOfScience();
		$row->fill($request->input('fields'));

		if (!$row->save())
		{
			$error = $row->getError() ? $row->getError() : trans('global.messages.save failed');

			return redirect()->back()->withError($error);
		}

		return $this->cancel()->with('success', trans('global.messages.item saved'));
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  Request $request
	 * @return Response
	 */
	public function delete(Request $request)
	{
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		foreach ($ids as $id)
		{
			$row = FieldOfScience::findOrFail($id);

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
		return redirect(route('admin.groups.fieldsofscience'));
	}
}
