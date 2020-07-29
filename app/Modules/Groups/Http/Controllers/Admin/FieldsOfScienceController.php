<?php

namespace App\Modules\Groups\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Halcyon\Http\StatefulRequest;
use App\Halcyon\Models\FieldOfScience;
//use App\Modules\Groups\Models\FieldOfScience;

class FieldsOfScienceController extends Controller
{
	/**
	 * Display a listing of tags
	 *
	 * @return Response
	 */
	public function index(StatefulRequest $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			// Paging
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			// Sorting
			'order'     => FieldOfScience::$orderBy,
			'order_dir' => FieldOfScience::$orderDir,
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->state('groups.fos.filter_' . $key, $key, $default);
		}
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
				$filters['search'] = strtolower((string)$filters['search']);

				$query->where('name', 'like', '%' . $filters['search'] . '%');
			}

			$rows = $query
				->orderBy($filters['order'], $filters['order_dir']);
		}
		else
		{
			$rows = FieldOfScience::tree($filters['order'], $filters['order_dir']);
			$root = array_shift($rows);
		}

		/*$rows = $query
			->withCount('groups')
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);*/

		$total = count($rows);
		/*$levellimit = ($filters['limit'] == 0) ? 500 : $filters['limit'];
		$list       = array();
		$children   = array();

		if ($rows)
		{
			// First pass - collect children
			foreach ($rows as $k)
			{
				$pt = $k->parentid;
				$list = @$children[$pt] ? $children[$pt] : array();
				array_push($list, $k);
				$children[$pt] = $list;
			}

			// Second pass - get an indent list of the items
			$list = $this->treeRecurse(0, '', array(), $children, max(0, $levellimit-1));
		}

		if ($filters['batchsystem'])
		{
			$list = array_filter($list, function($k) use ($filters)
			{
				return ($k->batchsystem == $filters['batchsystem']);
			});
			$total = count($list);
		}*/

		$rows = array_slice($rows, $filters['start'], $filters['limit']);

		$paginator = new \Illuminate\Pagination\LengthAwarePaginator($rows, $total, $filters['limit'], $filters['page']);
		$paginator->withPath(route('admin.resources.index'));

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
		app('request')->merge(['hidemainmenu' => 1]);

		$row = new Group();

		$departments = Department::tree();
		$fields = FieldOfScience::tree();

		return view('groups::admin.groups.edit', [
			'row' => $row,
			'departments' => $departments,
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

		$row = $id ? Group::findOrFail($id) : new Group();
		$row->fill($request->input('fields'));
		$row->slug = $row->normalize($row->name);

		if (!$row->created_by)
		{
			$row->created_by = auth()->user()->id;
		}

		if (!$row->updated_by)
		{
			$row->updated_by = auth()->user()->id;
		}

		if (!$row->save())
		{
			$error = $row->getError() ? $row->getError() : trans('messages.save failed');

			return redirect()->back()->withError($error);
		}

		return $this->cancel()->with('success', trans('messages.item saved'));
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @return Response
	 */
	public function edit($id)
	{
		app('request')->merge(['hidemainmenu' => 1]);

		$row = Group::findOrFail($id);

		$departments = Department::tree();
		$fields = FieldOfScience::tree();

		return view('groups::admin.groups.edit', [
			'row' => $row,
			'departments' => $departments,
			'fields' => $fields,
		]);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @return Response
	 */
	public function delete(Request $request)
	{
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		foreach ($ids as $id)
		{
			$row = Group::findOrFail($id);

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
		return redirect(route('admin.groups.index'));
	}
}
