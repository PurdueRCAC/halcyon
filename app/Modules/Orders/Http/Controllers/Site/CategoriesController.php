<?php

namespace App\Modules\Orders\Http\Controllers\Site;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Modules\Orders\Models\Category;

class CategoriesController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @param   Request  $request
	 * @return  Response
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			'parent'    => 1, // Root node
			'state'     => 'published',
			'limit'     => config('list_limit', 20),
			'order'     => Category::$orderBy,
			'order_dir' => Category::$orderDir,
		);

		foreach ($filters as $key => $default)
		{
			// Check the session
			$old = $request->session()->get('orders.categories.' . $key, $default);

			// Check request
			$val = $request->input($key);

			// Save the new value only if it was set in this request.
			if ($request->exists($key)) //$val !== null)
			{
				// Save to session
				$request->session()->put('orders.categories.' . $key, $val);
			}
			else
			{
				$val = $old;
			}

			$filters[$key] = $val;
		}

		if (!in_array($filters['order'], ['id', 'name']))
		{
			$filters['order'] = Category::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Category::$orderDir;
		}

		$query = Category::query()->withTrashed();

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
			$query->whereIsActive();
		}
		elseif ($filters['state'] == 'trashed')
		{
			$query->whereIsTrashed();
		}

		$rows = $query
			->withCount('products')
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'])
			->appends(array_filter($filters));

		return view('orders::site.categories.index', [
			'filters' => $filters,
			'rows'    => $rows,
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return  Response
	 */
	public function create()
	{
		$row = new Category();

		$categories = Category::query()
			->where('id', '!=', 1)
			->where('datetimeremoved', '=', '0000-00-00 00:00:00')
			->orderBy('name', 'asc')
			->get();

		return view('orders::site.categories.edit', [
			'row' => $row,
			'categories' => $categories
		]);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param   Request  $request
	 * @return  Response
	 */
	public function store(Request $request)
	{
		$request->validate([
			'fields.name' => 'required'
		]);

		$id = $request->input('id');

		$row = $id ? Category::findOrFail($id) : new Category();

		$row->fill($request->input('fields'));

		if ($request->input('state') == 'trashed' && !$row->trashed())
		{
			$row->delete();
		}
		elseif ($request->input('state') != 'trashed' && $row->trashed())
		{
			$row->datetimeremoved = '0000-00-00 00:00:00';
			//$row->restore();
		}

		if (!$row->save())
		{
			$error = $row->getError() ? $row->getError() : trans('messages.save failed');

			return redirect()->back()->withError($error);
		}

		return $this->cancel()->with('success', trans('messages.item saved'));
	}

	/**
	 * Show the form for editing the specified entry
	 *
	 * @param   integer   $id
	 * @return  Response
	 */
	public function edit($id)
	{
		$row = Category::withTrashed()->find($id);

		$categories = Category::query()
			->where('id', '!=', $id)
			->where('id', '!=', 1)
			->where('datetimeremoved', '=', '0000-00-00 00:00:00')
			->orderBy('name', 'asc')
			->get();

		return view('orders::site.categories.edit', [
			'row' => $row,
			'categories' => $categories
		]);
	}

	/**
	 * Remove the specified entry
	 *
	 * @param   integer   $id
	 * @return  Response
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
		return redirect(route('site.orders.categories'));
	}
}
