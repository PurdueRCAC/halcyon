<?php

namespace App\Modules\Groups\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Halcyon\Http\StatefulRequest;
use App\Modules\Groups\Models\Department;
use App\Modules\Groups\Models\GroupDepartment;

class DepartmentsController extends Controller
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
			'order'     => Department::$orderBy,
			'order_dir' => Department::$orderDir,
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->state('groups.fos.filter_' . $key, $key, $default);
		}
		$filters['start'] = ($filters['limit'] * $filters['page']) - $filters['limit'];

		if (!in_array($filters['order'], array('id', 'name')))
		{
			$filters['order'] = Department::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Department::$orderDir;
		}

		if ($filters['search'])
		{
			$query = Department::query();

			if (is_numeric($filters['search']))
			{
				$query->where('id', '=', $filters['search']);
			}
			else
			{
				$filters['search'] = strtolower((string)$filters['search']);

				$query->where('name', 'like', '%' . $filters['search'] . '%');
			}

			if ($filters['parent'])
			{
				$query->where('parentid', '=', $filters['parent']);
			}

			$rows = $query
				->orderBy($filters['order'], $filters['order_dir'])
				->get();
		}
		else
		{
			$rows = Department::tree($filters['order'], $filters['order_dir']);
			$root = array_shift($rows);
		}

		$total = count($rows);

		$rows = array_slice($rows, $filters['start'], $filters['limit']);

		$paginator = new \Illuminate\Pagination\LengthAwarePaginator($rows, $total, $filters['limit'], $filters['page']);
		$paginator->withPath(route('admin.groups.departments'));

		return view('groups::admin.departments.index', [
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
		$parents = Department::tree();

		$row = new Department();

		return view('groups::admin.departments.edit', [
			'row' => $row,
			'parents' => $parents
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
		$parents = Department::tree();

		$row = Department::findOrFail($id);

		return view('groups::admin.departments.edit', [
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
		$request->validate([
			'fields.name' => 'required'
		]);

		$id = $request->input('id');

		$row = $id ? Department::findOrFail($id) : new Department();
		$row->fill($request->input('fields'));

		if (!$row->save())
		{
			$error = $row->getError() ? $row->getError() : trans('messages.save failed');

			return redirect()->back()->withError($error);
		}

		return $this->cancel()->with('success', trans('messages.item saved'));
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
			$row = Department::findOrFail($id);

			if (!$row->delete())
			{
				$request->session()->flash('error', $row->getError());
				continue;
			}

			$success++;
		}

		if ($success)
		{
			$request->session()->flash('success', trans('messages.item deleted', ['count' => $success]));
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
		return redirect(route('admin.groups.departments'));
	}
}
