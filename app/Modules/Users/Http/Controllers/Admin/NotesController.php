<?php

namespace App\Modules\Users\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Modules\Users\Models\Note;
use App\Halcyon\Http\StatefulRequest;

class NotesController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * 
	 * @param  StatefulRequest $request
	 * @return View
	 */
	public function index(StatefulRequest $request)
	{
		// Get filters
		$filters = array(
			'search'   => null,
			'state'    => '*',
			'access'   => 0,
			'category_id' => 0,
			// Paging
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			// Sorting
			'order'     => Note::$orderBy,
			'order_dir' => Note::$orderDir,
		);

		$reset = false;
		foreach ($filters as $key => $default)
		{
			if ($key != 'page'
			 && $request->has($key) //&& session()->has('users.notes.filter_' . $key)
			 && $request->input($key) != session()->get('users.notes.filter_' . $key))
			{
				$reset = true;
			}
			$filters[$key] = $request->state('users.notes.filter_' . $key, $key, $default);
		}
		$filters['page'] = $reset ? 1 : $filters['page'];

		if (!in_array($filters['order'], ['id', 'subject', 'body', 'state', 'access', 'category_id']))
		{
			$filters['order'] = Note::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Note::$orderDir;
		}

		$query = Note::query()
			->with('user');

		if ($filters['search'])
		{
			if (is_numeric($filters['search']))
			{
				$query->where('id', '=', (int)$filters['search']);
			}
			else
			{
				$query->where(function($where) use ($filters)
				{
					$where->where('subject', 'like', '%' . strtolower((string)$filters['search']) . '%')
						->orWhere('body', 'like', '%' . strtolower((string)$filters['search']) . '%');
				});
			}
		}

		if (is_numeric($filters['state']))
		{
			$query->where('state', '=', (int)$filters['state']);
		}

		if ($filters['access'])
		{
			$query->where('access', '=', (int)$filters['access']);
		}

		if ($filters['category_id'])
		{
			$query->where('catid', '=', (int)$filters['category_id']);
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		return view('users::admin.notes.index', [
			'rows' => $rows,
			'filters' => $filters
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 * 
	 * @return View
	 */
	public function create()
	{
		$row = new Note;

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		return view('users::admin.notes.edit', [
			'row' => $row
		]);
	}

	/**
	 * Show the form for editing the specified resource.
	 * 
	 * @param  int  $id
	 * @return View
	 */
	public function edit($id)
	{
		$row = Note::findOrFail($id);

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		return view('users::admin.notes.edit', [
			'row' => $row
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
			//'fields.subject' => 'required|string',
			'fields.body' => 'required|string'
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$id = $request->input('id');

		$row = Note::findOrNew($id);
		$row->fill($request->input('fields'));

		if (!$row->save())
		{
			return redirect()->back()->withError(trans('global.messages.save failed'));
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
			$row = Note::findOrFail($id);

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
		return redirect(route('admin.users.notes'));
	}
}
