<?php

namespace App\Modules\Publications\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Modules\Publications\Models\Type;
use App\Modules\Publications\Models\Author;
use App\Halcyon\Http\StatefulRequest;
use Carbon\Carbon;

class AuthorsController extends Controller
{
	/**
	 * Display a listing of entries.
	 * 
	 * @param  StatefulRequest $request
	 * @return View
	 */
	public function index(StatefulRequest $request)
	{
		// Get filters
		$filters = array(
			'search'   => null,
			'publication_id'   => 0,
			// Paging
			'limit'    => config('list_limit', 20),
			'page'     => 1,
			// Sorting
			'order'     => Author::$orderBy,
			'order_dir' => Author::$orderDir,
		);

		$reset = false;
		$request = $request->mergeWithBase();
		foreach ($filters as $key => $default)
		{
			if ($key != 'page'
			 && $request->has($key) //&& session()->has('publications.authors.filter_' . $key)
			 && $request->input($key) != session()->get('publications.authors.filter_' . $key))
			{
				$reset = true;
			}
			$filters[$key] = $request->state('publications.authors.' . $key, $key, $default);
		}

		$filters['page'] = $reset ? 1 : $filters['page'];

		if (!in_array($filters['order'], ['id', 'name', 'created_at', 'publication_id']))
		{
			$filters['order'] = Author::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Author::$orderDir;
		}

		$query = Author::query();

		if ($filters['publication_id'])
		{
			$query->where('publication_id', '=', (int)$filters['publication_id']);
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page'])
			->appends(array_filter($filters));

		return view('publications::admin.authors.index', [
			'rows'    => $rows,
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
		$row = new Author;

		if ($fields = $request->old('fields'))
		{
			$row->fill($fields);
		}

		return view('publications::admin.authors.edit', [
			'row' => $row,
		]);
	}

	/**
	 * Show the form for editing the specified entry
	 *
	 * @param   Request $request
	 * @param   int  $id
	 * @return  View
	 */
	public function edit($id)
	{
		$row = Author::withTrashed()->findOrFail($id);

		if ($fields = $request->old('fields'))
		{
			$row->fill($fields);
		}

		return view('publications::admin.authors.edit', [
			'row' => $row,
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
			'publication_id' => 'required|integer',
			'name' => 'required|string|max:255',
			'user_id' => 'nullable|integer',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$id = $request->input('id');

		$row = $id ? Author::findOrFail($id) : new Author();
		$row->fill($request->input('fields'));

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
	 * @param   int  $id
	 * @return  RedirectResponse
	 */
	public function delete(Request $request, $id = null)
	{
		// Incoming
		$ids = $request->input('id', $id);
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		foreach ($ids as $id)
		{
			// Delete the entry
			// Note: This is recursive and will also remove all descendents
			$row = Author::withTrashed()->find($id);

			if (!$row)
			{
				continue;
			}

			if ($row->trashed())
			{
				if (!$row->forceDelete())
				{
					$request->session()->flash('error', trans('global.messages.delete failed'));
					continue;
				}
			}
			else
			{
				if (!$row->delete())
				{
					$request->session()->flash('error', trans('global.messages.delete failed'));
					continue;
				}
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
	 * Sets the state of one or more entries
	 * 
	 * @param   Request $request
	 * @return  RedirectResponse
	 */
	public function restore(Request $request)
	{
		// Incoming
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		// Check for an ID
		if (count($ids) < 1)
		{
			$request->session()->flash('warning', trans('menus::menus.select to restore'));
			return $this->cancel();
		}

		$success = 0;

		// Update record(s)
		foreach ($ids as $id)
		{
			$row = Author::withTrashed()->findOrFail(intval($id));

			if (!$row->restore())
			{
				$request->session()->flash('error', trans('global.messages.delete failed'));
				continue;
			}

			$success++;
		}

		// Set message
		if ($success)
		{
			$request->session()->flash('success', trans('menus::menus.items restored', ['count' => $success]));
		}

		return $this->cancel();
	}

	/**
	 * Reorder entries
	 * 
	 * @param   int  $id
	 * @param   Request $request
	 * @return  RedirectResponse
	 */
	public function reorder($id, Request $request)
	{
		// Get the element being moved
		$row = Author::findOrFail($id);
		$move = ($request->segment(4) == 'orderup') ? -1 : +1;

		if (!$row->move($move))
		{
			$request->session()->flash('error', trans('global.messages.move failed'));
		}

		// Redirect
		return $this->cancel();
	}

	/**
	 * Return to default page
	 *
	 * @return  RedirectResponse
	 */
	public function cancel()
	{
		return redirect(route('admin.publications.authors'));
	}
}
