<?php

namespace App\Modules\Orders\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Halcyon\Http\StatefulRequest;
use App\Modules\Groups\Models\Department;
use App\Modules\Orders\Models\Approver;

class ApproversController extends Controller
{
	/**
	 * Display a listing of tags
	 *
	 * @param  StatefulRequest $request
	 * @return View
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

		$reset = false;
		$request = $request->mergeWithBase();
		foreach ($filters as $key => $default)
		{
			if ($key != 'page'
			 && $request->has($key) //&& session()->has('groups.deps.filter_' . $key)
			 && $request->input($key) != session()->get('groups.deps.filter_' . $key))
			{
				$reset = true;
			}
			$filters[$key] = $request->state('groups.deps.filter_' . $key, $key, $default);
		}
		$filters['page'] = $reset ? 1 : $filters['page'];
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
			$d = (new Department)->getTable();
			$a = (new Approver)->getTable();

			$query = Department::query()
				->select($d . '.*', $a . '.userid', $a . '.id AS approver')
				->leftJoin($a, $a . '.departmentid', $d . '.id');

			$query->whereSearch($filters['search']);

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
			$rows = self::tree($filters['order'], $filters['order_dir']);
			$root = array_shift($rows);

			$total = count($rows);

			$rows = array_slice($rows, $filters['start'], $filters['limit']);
		}

		$paginator = new \Illuminate\Pagination\LengthAwarePaginator($rows, $total, $filters['limit'], $filters['page']);
		$paginator->withPath(route('admin.orders.approvers'));

		return view('orders::admin.approvers.index', [
			'rows'    => $rows,
			'filters' => $filters,
			'paginator' => $paginator,
			'departments' => Department::tree()
		]);
	}

	/**
	 * Get records as nested tree
	 *
	 * @param   string  $order
	 * @param   string  $dir
	 * @return  array<int,Department>
	 */
	public static function tree(string $order = 'name', string $dir = 'asc'): array
	{
		$d = (new Department)->getTable();
		$a = (new Approver)->getTable();

		$rows = Department::query()
			->select($d . '.*', $a . '.userid', $a . '.id AS approver')
			->leftJoin($a, $a . '.departmentid', $d . '.id')
			->orderBy($order, $dir)
			->get();

		$list = array();

		if (count($rows) > 0)
		{
			$levellimit = 9999;
			$list       = array();
			$children   = array();

			// First pass - collect children
			foreach ($rows as $k)
			{
				$pt = $k->parentid;

				if (!isset($children[$pt]))
				{
					$children[$pt] = array();
				}
				$children[$pt][] = $k;
			}

			// Second pass - get an indent list of the items
			$list = self::treeRecurse(0, $list, $children, max(0, $levellimit-1));
		}

		return $list;
	}

	/**
	 * Recursive function to build tree
	 *
	 * @param   int    $id        Parent ID
	 * @param   array<int,Department>  $list      List of records
	 * @param   array<int,array{int,Department}>  $children  Container for parent/children mapping
	 * @param   int    $maxlevel  Maximum levels to descend
	 * @param   int    $level     Indention level
	 * @param   string $prfx
	 * @return  array<int,Department>
	 */
	protected static function treeRecurse(int $id, array $list, array $children, int $maxlevel=9999, int $level=0, int $type=1, string $prfx = ''): array
	{
		if (@$children[$id] && $level <= $maxlevel)
		{
			foreach ($children[$id] as $z => $v)
			{
				$vid = $v->id;
				$pt = $v->parentid;

				$list[$vid] = $v;
				$list[$vid]->prefix = ($prfx ? $prfx . ' › ' : '');
				$list[$vid]->name = $list[$vid]->name;
				$list[$vid]->level = $level;
				$list[$vid]->children_count = isset($children[$vid]) ? count(@$children[$vid]) : 0;

				$p = '';
				if ($v->parentid)
				{
					$p = $list[$vid]->prefix . $list[$vid]->name;
				}

				unset($children[$id][$z]);

				$list = self::treeRecurse($vid, $list, $children, $maxlevel, $level+1, $type, $p);
			}
			unset($children[$id]);
		}
		return $list;
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @param  Request $request
	 * @return View
	 */
	public function create(Request $request)
	{
		$parents = Department::tree();

		$row = new Approver;

		if ($departmentid = $request->old('departmentid'))
		{
			$row->departmentid = $departmentid;
		}
		if ($userid = $request->old('userid'))
		{
			$row->userid = $userid;
		}

		return view('orders::admin.approvers.edit', [
			'row' => $row,
			'parents' => $parents
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
		$parents = Department::tree();

		$row = Approver::findOrFail($id);

		if ($departmentid = $request->old('departmentid'))
		{
			$row->departmentid = $departmentid;
		}
		if ($userid = $request->old('userid'))
		{
			$row->userid = $userid;
		}

		return view('orders::admin.approvers.edit', [
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
			'departmentid' => 'required|integer',
			'userid' => 'required|integer'
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$id = $request->input('id');

		$row = Approver::findOrNew($id);
		$row->departmentid = $request->input('departmentid');
		$row->userid = $request->input('userid');

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
			$row = Approver::findOrFail($id);

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
		return redirect(route('admin.orders.approvers'));
	}
}
