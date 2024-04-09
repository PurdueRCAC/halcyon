<?php

namespace App\Modules\Issues\Http\Controllers\Site;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Halcyon\Http\Concerns\UsesFilters;
use App\Modules\Issues\Models\Issue;
use App\Modules\Issues\Models\Issueresource;
use App\Modules\Issues\Models\Comment;
use App\Modules\Issues\Models\ToDo;
use App\Halcyon\Utility\PorterStemmer;

class TodosController extends Controller
{
	use UsesFilters;

	/**
	 * Display a listing of articles
	 *
	 * @param  Request $request
	 * @return View
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = $this->getStatefulFilters($request, 'issues.todos', [
			'search'    => null,
			'timeperiod' => null,
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			'order'     => Issue::$orderBy,
			'order_dir' => Issue::$orderDir,
		]);

		if (!in_array($filters['order'], ['id', 'userid', 'name', 'description', 'datetimecreated', 'recurringtimeperiodid']))
		{
			$filters['order'] = ToDo::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = ToDo::$orderDir;
		}

		$query = ToDo::query();

		if ($filters['search'])
		{
			$query->where(function($where)
			{
				$where->where('name', 'like', '%' . $filters['search'] . '%')
					->orWhere('description', 'like', '%' . $filters['search'] . '%');
			});
		}

		if ($filters['timeperiod'])
		{
			$query->where('recurringtimeperiodid', '=', $filters['timeperiod']);
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		return view('issues::site.todos.index', [
			'filters' => $filters,
			'rows'    => $rows
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
		$row = new ToDo();

		if ($fields = $request->old('fields'))
		{
			$row->fill($fields);
		}

		return view('issues::site.todos.edit', [
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
		$row = ToDo::findOrFail($id);

		if ($fields = $request->old('fields'))
		{
			$row->fill($fields);
		}

		return view('issues::site.todos.edit', [
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
			'fields.name' => 'required|string|max:255',
			'fields.description' => 'nullable|string|max:2000',
			'fields.recurringtimeperiodid' => 'nullable|integer',
			'fields.userid' => 'nullable|integer'
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$id = $request->input('id');

		$row = ToDo::findOrNew($id);
		$row->fill($request->input('fields'));

		if (!$row->save())
		{
			return redirect()->back()->with('error', 'Failed to create item.');
		}

		return redirect(route('site.issues.todos'))->withSuccess(trans('global.messages.item created'));
	}

	/**
	 * Remove the specified entry
	 *
	 * @param   Request  $requesy
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
			// Delete the entry
			$row = ToDo::findOrFail($id);

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

		return redirect(route('site.issues.todos'));
	}
}
