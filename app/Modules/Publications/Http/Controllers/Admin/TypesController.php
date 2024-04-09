<?php

namespace App\Modules\Publications\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Modules\Publications\Models\Type;
use App\Halcyon\Http\Concerns\UsesFilters;

class TypesController extends Controller
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
		$filters = $this->getStatefulFilters($request, 'publications.types', [
			'search'   => null,
			// Paging
			'limit'    => config('list_limit', 20),
			'page'     => 1,
			// Sorting
			'order'     => Type::$orderBy,
			'order_dir' => Type::$orderDir,
		]);

		if (!in_array($filters['order'], ['id', 'name', 'alias', 'publications_count']))
		{
			$filters['order'] = Type::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Type::$orderDir;
		}

		// Get records
		$query = Type::query();

		if ($filters['search'])
		{
			$query->whereSearch($filters['search']);
		}

		$rows = $query
			->withCount('publications')
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		return view('publications::admin.types.index', [
			'rows' => $rows,
			'filters' => $filters,
		]);
	}

	/**
	 * Show the form for creating a new article
	 *
	 * @param   Request $request
	 * @return  View
	 */
	public function create(Request $request)
	{
		$row = new Type();

		if ($fields = $request->old())
		{
			$row->fill($fields);
		}

		return view('publications::admin.types.edit', [
			'row' => $row
		]);
	}

	/**
	 * Show the form for editing the specified entry
	 *
	 * @param   Request $request
	 * @param   int  $id
	 * @return  View
	 */
	public function edit(Request $request, $id)
	{
		$row = Type::findOrFail($id);

		if ($fields = $request->old())
		{
			$row->fill($fields);
		}

		return view('publications::admin.types.edit', [
			'row' => $row
		]);
	}

	/**
	 * Store a newly created entry
	 *
	 * @param   Request  $request
	 * @return  RedirectResponse
	 */
	public function store(Request $request)
	{
		$rules = [
			'name' => 'required|string|max:50',
			'alias' => 'nullable|string|max:50'
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
		$row->name = $request->input('name');
		if ($request->has('alias'))
		{
			$row->alias = $request->input('alias');
		}

		if (!$row->save())
		{
			return redirect()->back()->withError(trans('global.messages.save failed'));
		}

		return $this->cancel()->with('success', trans('global.messages.item ' . ($id ? 'updated' : 'created')));
	}

	/**
	 * Remove the specified entry
	 *
	 * @param   Request  $request
	 * @return  RedirectResponse
	 */
	public function delete(Request $request)
	{
		// Incoming
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		foreach ($ids as $id)
		{
			$row = Type::find($id);

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
	 * Return to default page
	 *
	 * @return  RedirectResponse
	 */
	public function cancel()
	{
		return redirect(route('admin.publications.types'));
	}
}
