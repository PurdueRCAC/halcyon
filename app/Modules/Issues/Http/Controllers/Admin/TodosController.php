<?php

namespace App\Modules\Issues\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Halcyon\Http\StatefulRequest;
use App\Modules\Issues\Models\Issue;
use App\Modules\Issues\Models\Issueresource;
use App\Modules\Issues\Models\Comment;
use App\Modules\Issues\Models\ToDo;
use App\Halcyon\Utility\PorterStemmer;

class TodosController extends Controller
{
	/**
	 * Display a listing of articles
	 *
	 * @param  StatefulRequest $request
	 * @return Response
	 */
	public function index(StatefulRequest $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			'timeperiod' => null,
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			'order'     => Issue::$orderBy,
			'order_dir' => Issue::$orderDir,
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->state('issues.todos.filter_' . $key, $key, $default);
		}

		if (!in_array($filters['order'], array_keys((new ToDo)->getAttributes())))
		{
			$filters['order'] = ToDo::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = ToDo::$orderDir;
		}

		$query = ToDo::query()
			->withTrashed()
			->whereIsActive();

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

		return view('issues::admin.todos.index', [
			'filters' => $filters,
			'rows'    => $rows
		]);
	}

	/**
	 * Show the form for creating a new article
	 *
	 * @return  Response
	 */
	public function create()
	{
		$row = new ToDo();

		return view('issues::admin.todos.edit', [
			'row' => $row
		]);
	}

	/**
	 * Show the form for editing the specified entry
	 *
	 * @param   integer  $id
	 * @return  Response
	 */
	public function edit($id)
	{
		$row = ToDo::findOrFail($id);

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		return view('issues::admin.todos.edit', [
			'row' => $row
		]);
	}

	/**
	 * Store a newly created entry
	 *
	 * @param   Request  $request
	 * @return  Response
	 */
	public function store(Request $request)
	{
		$request->validate([
			'fields.name' => 'required|string|max:255',
			'fields.description' => 'nullable|string|max:2000',
			'fields.recurringtimeperiodid' => 'nullable|integer',
			'fields.userid' => 'nullable|integer'
		]);

		$id = $request->input('id');

		$row = $id ? ToDo::findOrFail($id) : new ToDo();
		$row->fill($request->input('fields'));

		if (!$row->save())
		{
			return redirect()->back()->with('error', 'Failed to create item.');
		}

		return $this->cancel()->withSuccess(trans('global.messages.item created'));
	}

	/**
	 * Remove the specified entry
	 *
	 * @param   Request  $requesy
	 * @return  Response
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
	 * Return to default page
	 *
	 * @return  Response
	 */
	public function cancel()
	{
		return redirect(route('admin.issues.todos'));
	}
}
