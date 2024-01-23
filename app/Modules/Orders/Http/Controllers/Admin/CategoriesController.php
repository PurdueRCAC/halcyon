<?php

namespace App\Modules\Orders\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use App\Modules\Orders\Models\Category;
use App\Halcyon\Http\StatefulRequest;

class CategoriesController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @param   StatefulRequest  $request
	 * @return  View
	 */
	public function index(StatefulRequest $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			'parent'    => 1, // Root node
			'state'     => 'published',
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			'order'     => Category::$orderBy,
			'order_dir' => Category::$orderDir,
		);

		$reset = false;
		$request = $request->mergeWithBase();
		foreach ($filters as $key => $default)
		{
			if ($key != 'page'
			 && $request->has($key) //&& session()->has('orders.categories.filter_' . $key)
			 && $request->input($key) != session()->get('orders.categories.filter_' . $key))
			{
				$reset = true;
			}
			$filters[$key] = $request->state('orders.categories.filter_' . $key, $key, $default);
		}
		$filters['page'] = $reset ? 1 : $filters['page'];

		if (!in_array($filters['order'], ['id', 'name']))
		{
			$filters['order'] = Category::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Category::$orderDir;
		}

		$query = Category::query()
			->withTrashed();

		if ($filters['parent'] > 1)
		{
			$query->where('parentordercategoryid', '=', $filters['parent']);
		}
		else
		{
			$query->where('parentordercategoryid', '>', 0);
		}

		if ($filters['search'])
		{
			$query->where('name', 'like', '%' . $filters['search'] . '%');
		}

		if ($filters['state'] == 'published')
		{
			$query->whereNull('datetimeremoved');
		}
		elseif ($filters['state'] == 'trashed')
		{
			//$query->onlyTrashed();
			$query->whereNotNull('datetimeremoved');
		}

		$rows = $query
			->withCount('products')
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page'])
			->appends(array_filter($filters));

		return view('orders::admin.categories.index', [
			'filters' => $filters,
			'rows'    => $rows,
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return  View
	 */
	public function create()
	{
		$row = new Category();

		$categories = Category::query()
			->where('id', '!=', 1)
			->orderBy('name', 'asc')
			->get();

		return view('orders::admin.categories.edit', [
			'row' => $row,
			'categories' => $categories
		]);
	}

	/**
	 * Show the form for editing the specified entry
	 *
	 * @param   int   $id
	 * @return  View
	 */
	public function edit($id)
	{
		$row = Category::withTrashed()->find($id);

		$categories = Category::query()
			->where('id', '!=', $id)
			->where('id', '!=', 1)
			->orderBy('name', 'asc')
			->get();

		return view('orders::admin.categories.edit', [
			'row' => $row,
			'categories' => $categories
		]);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param   Request  $request
	 * @return  RedirectResponse
	 */
	public function store(Request $request)
	{
		$request->validate([
			'fields.name' => 'required|string|max:64',
			'fields.description' => 'nullable|string|max:2000',
			'fields.parentordercategoryid' => 'nullable|integer',
		]);

		$id = $request->input('id');

		$row = Category::findOrNew($id);
		$row->fill($request->input('fields'));
		if (!$row->description)
		{
			$row->description = '';
		}

		if ($request->input('state') == 'trashed')
		{
			if (!$row->trashed())
			{
				$row->delete();
			}
		}
		else
		{
			if ($row->trashed())
			{
				$row->restore();
			}

			//$row->state = $request->input('state') == 'published' ? 1 : 0;
		}

		if (!$row->save())
		{
			return redirect()->back()->withError(trans('global.messages.save failed'));
		}

		return $this->cancel()->with('success', trans('global.messages.item saved'));
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
		$row = Category::findOrFail($id);
		$move = ($request->segment(4) == 'orderup') ? -1 : +1;

		if (!$row->move($move))
		{
			$request->session()->flash('error', trans('global.messages.move failed'));
		}

		// Redirect
		return $this->cancel();
	}

	/**
	 * Method to save the submitted ordering values for records.
	 *
	 * @param   Request  $request
	 * @return  RedirectResponse
	 */
	public function saveorder(Request $request)
	{
		// Get the input
		$pks   = $request->input('id', []);
		$order = $request->input('sequence', []);

		// Sanitize the input
		$pks   = array_map('intval', $pks);
		$order = array_map('intval', $order);

		// Save the ordering
		$return = Category::saveOrder($pks, $order);

		if ($return === false)
		{
			// Reorder failed
			$request->session()->flash('error', trans('global.error.reorder failed'));
		}
		else
		{
			// Reorder succeeded.
			$request->session()->flash('success', trans('global.messages.ordering saved'));
		}

		// Redirect back to the listing
		return $this->cancel();
	}

	/**
	 * Remove the specified entry
	 *
	 * @param   Request  $request
	 * @return  RedirectResponse
	 */
	public function delete(Request $request)
	{
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		foreach ($ids as $id)
		{
			$row = Category::findOrFail($id);

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
		return redirect(route('admin.orders.categories'));
	}
}
