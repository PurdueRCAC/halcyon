<?php

namespace App\Modules\Groups\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Halcyon\Http\Concerns\UsesFilters;
use App\Modules\Groups\Models\FieldOfScience;

class FieldsOfScienceController extends Controller
{
	use UsesFilters;

	/**
	 * Display a listing of entries
	 *
	 * @param  Request $request
	 * @return View
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = $this->getStatefulFilters($request, 'groups.fos', [
			'search'    => null,
			'parent'    => 1,
			// Paging
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			// Sorting
			'order'     => FieldOfScience::$orderBy,
			'order_dir' => FieldOfScience::$orderDir,
		]);
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

			$query->whereSearch($filters['search']);

			/*if ($filters['parent'])
			{
				$query->where('parentid', '=', $filters['parent']);
			}*/
			// Exclude the ROOT node
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
	 * @param  Request $request
	 * @return View
	 */
	public function create(Request $request)
	{
		$parents = FieldOfScience::tree();

		$row = new FieldOfScience();

		if ($name = $request->old('name'))
		{
			$row->name = $name;
		}
		if ($parentid = $request->old('parentid'))
		{
			$row->parentid = intval($parentid);
		}

		return view('groups::admin.fieldsofscience.edit', [
			'row' => $row,
			'parents' => $parents,
		]);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  Request $request
	 * @param  int $id
	 * @return View
	 */
	public function edit(Request $request, $id)
	{
		$parents = FieldOfScience::tree();

		$row = FieldOfScience::findOrFail($id);

		if ($name = $request->old('name'))
		{
			$row->name = $name;
		}
		if ($parentid = $request->old('parentid'))
		{
			$row->parentid = intval($parentid);
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
	 * @return RedirectResponse
	 */
	public function store(Request $request)
	{
		$rules = [
			'name' => 'required|string|max:255',
			'parentid' => 'nullable|integer'
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$id = $request->input('id');

		$row = FieldOfScience::findOrNew($id);
		$row->name = $request->input('name');
		if ($request->has('parentid'))
		{
			$row->parentid = $request->input('parentid');
		}
		$row->parentid = $row->parentid ? $row->parentid : 1;

		if (!$row->save())
		{
			return redirect()->back()
				->withError(trans('global.messages.save failed'));
		}

		return $this->cancel()->with('success', trans('global.messages.item saved'));
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  Request $request
	 * @return RedirectResponse
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
				$request->session()->flash('error', trans('global.messages.delete failed'));
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
	 * @return  RedirectResponse
	 */
	public function cancel()
	{
		return redirect(route('admin.groups.fieldsofscience'));
	}
}
