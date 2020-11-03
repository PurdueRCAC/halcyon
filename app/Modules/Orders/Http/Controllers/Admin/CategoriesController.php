<?php

namespace App\Modules\Orders\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Modules\Orders\Models\Category;
use App\Halcyon\Http\StatefulRequest;

class CategoriesController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @param   Request  $request
	 * @return  Response
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

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->state('orders.categories.filter_' . $key, $key, $default);
		}

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
			$query->where(function($where)
			{
				$where->where('datetimeremoved', '=', '0000-00-00 00:00:00')
					->orWhereNull('datetimeremoved');
			});
		}
		elseif ($filters['state'] == 'trashed')
		{
			//$query->onlyTrashed();
			$query->where(function($where)
			{
				$where->whereNotNull('datetimeremoved')
					->where('datetimeremoved', '!=', '0000-00-00 00:00:00');
			});
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
	 * @return  Response
	 */
	public function create()
	{
		$row = new Category();

		$categories = Category::query()
			->where('id', '!=', 1)
			->where(function($where)
			{
				$where->whereNull('datetimeremoved')
					->orWhere('datetimeremoved', '=', '0000-00-00 00:00:00');
			})
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
				$row->datetimeremoved = '0000-00-00 00:00:00';
				//$row->restore();
			}

			//$row->state = $request->input('state') == 'published' ? 1 : 0;
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
			->where(function($where)
			{
				$where->whereNull('datetimeremoved')
					->orWhere('datetimeremoved', '=', '0000-00-00 00:00:00');
			})
			->orderBy('name', 'asc')
			->get();

		return view('orders::admin.categories.edit', [
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
		return redirect(route('admin.orders.categories'));
	}
}
