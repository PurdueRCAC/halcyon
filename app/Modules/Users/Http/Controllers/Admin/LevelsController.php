<?php

namespace App\Modules\Users\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Halcyon\Access\Viewlevel as Level;
use App\Halcyon\Access\Role;
use App\Halcyon\Http\Concerns\UsesFilters;

class LevelsController extends Controller
{
	use UsesFilters;

	/**
	 * Display a listing of the resource.
	 * 
	 * @param  Request $request
	 * @return View
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = $this->getStatefulFilters($request, 'users.levels', [
			'search'   => null,
			// Paging
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			// Sorting
			'order'     => Level::$orderBy,
			'order_dir' => Level::$orderDir,
		]);

		if (!in_array($filters['order'], ['id', 'title', 'ordering']))
		{
			$filters['order'] = Level::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Level::$orderDir;
		}

		$query = Level::query();

		if ($filters['search'])
		{
			if (is_numeric($filters['search']))
			{
				$query->where('id', '=', (int)$filters['search']);
			}
			else
			{
				$query->where('title', 'like', '%' . $filters['search'] . '%');
			}
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		return view('users::admin.levels.index', [
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
	public function create(Request $request)
	{
		$row = new Level;

		$roles = Role::query()
			->orderBy('lft', 'asc')
			->get();

		if ($fields = $request->old('fields'))
		{
			$row->fill($fields);
		}

		return view('users::admin.levels.edit', [
			'row' => $row,
			'roles' => $roles
		]);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  Request $request
	 * @param  int  $id
	 * @return View
	 */
	public function edit(Request $request, $id)
	{
		$row = Level::findOrFail($id);

		$roles = Role::query()
			->orderBy('lft', 'asc')
			->get();

		if ($fields = $request->old('fields'))
		{
			$row->fill($fields);
		}

		return view('users::admin.levels.edit', [
			'row' => $row,
			'roles' => $roles
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
			'fields.title' => 'required|string|max:100',
			'fields.rules' => 'nullable|array'
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$id = $request->input('id');

		$row = Level::findOrNew($id);
		$row->fill($request->input('fields'));
		$row->rules = array_map('intval', $row->rules);

		if (!$row->save())
		{
			return redirect()->back()->withError(trans('global.messages.save failed'));
		}

		return $this->cancel()->with('success', trans('global.messages.item ' . ($id ? 'updated' : 'created')));
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
			$row = Level::find($id);

			if (!$row)
			{
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
		return redirect(route('admin.users.levels'));
	}
}
