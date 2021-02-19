<?php

namespace App\Modules\Orders\Http\Controllers\Site;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Orders\Models\Order;
use App\Modules\Orders\Models\Category;
use App\Modules\Orders\Models\Product;
use App\Modules\Orders\Models\Item;
use App\Modules\Users\Models\User;

class ProductsController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * 
	 * @param  Request  $request
	 * @return Response
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			'category'  => 0,
			'public'    => 1,
			'restricteddata' => '*',
			// Paging
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			// Sorting
			'order'     => 'sequence',
			'order_dir' => Product::$orderDir,
		);

		foreach ($filters as $key => $default)
		{
			// Check the session
			$old = $request->session()->get('orders.products.' . $key, $default);

			// Check request
			$val = $request->input($key);

			// Save the new value only if it was set in this request.
			if ($request->exists($key)) //$val !== null)
			{
				// Save to session
				$request->session()->put('orders.products.' . $key, $val);
			}
			else
			{
				$val = $old;
			}

			$filters[$key] = $val;
		}

		if (!in_array($filters['order'], ['id', 'name', 'unitprice', 'ordercategoryid', 'sequence']))
		{
			$filters['order'] = 'sequence';
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Product::$orderDir;
		}

		$p = (new Product)->getTable();
		$c = (new Category)->getTable();

		$query = Product::query()
			->withTrashed()
			->whereIsActive()
			->select($p . '.*', $c . '.name AS category_name')
			->join($c, $c . '.id', $p . '.ordercategoryid')
			->where(function($where) use ($c)
			{
				$where->whereNull($c . '.datetimeremoved')
					->orWhere($c . '.datetimeremoved', '=', '0000-00-00 00:00:00');
			});

		if ($filters['search'])
		{
			if (is_numeric($filters['search']))
			{
				$query->where($p . '.id', '=', $filters['search']);
			}
			else
			{
				$query->where($p . '.name', 'like', '%' . $filters['search'] . '%');
			}
		}

		if (!auth()->user())
		{
			$query->where($p . '.public', '=', 1);
		}
		else
		{
			if ($filters['public'] != '*')
			{
				$query->where($p . '.public', '=', $filters['public']);
			}
			else
			{
				$access = auth()->user()->getAuthorisedViewLevels();
				$access[] = 0;
				$query->whereIn($p . '.public', $access);
			}
		}

		if ($filters['category'])
		{
			$query->where($p . '.ordercategoryid', '=', $filters['category']);
		}

		if ($filters['restricteddata'] != '*')
		{
			$query->where($p . '.restricteddata', '=', $filters['restricteddata']);
		}

		$rows = $query
			->orderBy($p . '.' . $filters['order'], $filters['order_dir'])
			->get();
			//->paginate($filters['limit'])
			//->appends(array_filter($filters));

		$categories = Category::query()
			->withTrashed()
			->whereIsActive()
			->where('parentordercategoryid', '>', 0)
			->orderBy('name', 'asc')
			->get();

		$cart = app('cart');
		$cart->restore(auth()->user()->username);

		return view('orders::site.products.index', [
			'rows'    => $rows,
			'filters' => $filters,
			'categories' => $categories,
			'cart' => $cart
		]);
	}

	/**
	 * Display a listing of the resource.
	 * 
	 * @param  Request  $request
	 * @return Response
	 */
	public function manage(Request $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			'state'     => 'published',
			'category'  => 0,
			'access'    => '*',
			'restricteddata' => '*',
			// Paging
			'limit'     => config('list_limit', 20),
			// Sorting
			'order'     => 'name',
			'order_dir' => Product::$orderDir,
		);

		foreach ($filters as $key => $default)
		{
			// Check the session
			$old = $request->session()->get('orders.products.filter_' . $key, $default);

			// Check request
			$val = $request->input('filter_' . $key);

			// Save the new value only if it was set in this request.
			if ($request->exists('filter_' . $key)) //$val !== null)
			{
				// Save to session
				$request->session()->put('orders.products.filter_' . $key, $val);
			}
			else
			{
				$val = $old;
			}

			$filters[$key] = $val;
		}

		if (!in_array($filters['order'], ['id', 'name', 'unitprice', 'ordercategoryid', 'sequence', 'datetimecreated', 'datetimeremoved']))
		{
			$filters['order'] = 'name';
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Product::$orderDir;
		}

		$p = (new Product)->getTable();
		$c = (new Category)->getTable();

		$query = Product::query()
			->select($p . '.*', $c . '.name AS category_name')
			->join($c, $c . '.id', $p . '.ordercategoryid')
			->where($c . '.datetimeremoved', '=', '0000-00-00 00:00:00');

		if ($filters['search'])
		{
			if (is_numeric($filters['search']))
			{
				$query->where($p . '.id', '=', $filters['search']);
			}
			else
			{
				$query->where($p . '.name', 'like', '%' . $filters['search'] . '%');
			}
		}

		if ($filters['state'] == 'published')
		{
			$query->where($p . '.datetimeremoved', '=', '0000-00-00 00:00:00');
		}
		elseif ($filters['state'] == 'trashed')
		{
			$query->withTrashed()->where($p . '.datetimeremoved', '!=', '0000-00-00 00:00:00');
			//$query->onlyTrashed();
		}
		else
		{
			$query->withTrashed();
		}

		if ($filters['access'] != '*')
		{
			$query->where($p . '.public', '=', $filters['access']);
		}

		if ($filters['category'])
		{
			$query->where($p . '.ordercategoryid', '=', $filters['category']);
		}

		$rows = $query
			->orderBy($p . '.' . $filters['order'], $filters['order_dir'])
			->paginate($filters['limit'])
			->appends(array_filter($filters));

		$categories = Category::query()
			->withTrashed()
			->whereIsActive()
			->where('parentordercategoryid', '>', 0)
			->orderBy('name', 'asc')
			->get();

		return view('orders::site.products.manage', [
			'rows'    => $rows,
			'filters' => $filters,
			'categories' => $categories,
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 * 
	 * @return Response
	 */
	public function create()
	{
		$row = new Product();
		$row->public = 1;

		$categories = Category::query()
			->withTrashed()
			->whereIsActive()
			->where('parentordercategoryid', '>', 0)
			->orderBy('name', 'asc')
			->get();

		return view('orders::site.products.edit', [
			'row' => $row,
			'categories' => $categories
		]);
	}

	/**
	 * Show the form for editing the specified resource.
	 * 
	 * @param  integer  $id
	 * @return Response
	 */
	public function edit($id)
	{
		$row = Product::findOrFail($id);

		$categories = Category::query()
			->withTrashed()
			->whereIsActive()
			->where('parentordercategoryid', '>', 0)
			->orderBy('name', 'asc')
			->get();

		return view('orders::site.products.edit', [
			'row' => $row,
			'categories' => $categories
		]);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param   Request $request
	 * @return  Response
	 */
	public function store(Request $request)
	{
		$request->validate([
			'name' => 'required|string|max:64',
			'ordercategoryid' => 'required|integer|min:1',
			'description' => 'nullable|string|max:2000',
			'mou' => 'nullable|string|max:255',
			'unit' => 'nullable|string|max:16',
			'unitprice' => 'nullable|integer',
			'recurringtimeperiodid' => 'nullable|integer',
			'sequence' => 'nullable|integer|min:1',
			'successororderproductid' => 'nullable|integer|min:1',
			'terms' => 'nullable|string|max:2000',
			'restricteddata' => 'nullable|integer',
			'resourceid' => 'nullable|integer|min:1',
		]);

		$id = $request->input('id');

		$row = $id ? Product::findOrFail($id) : new Product();
		$row->fill($request->input('fields'));

		if (!$row->save())
		{
			$error = $row->getError() ? $row->getError() : trans('global.messages.save failed');

			return redirect()->back()->withError($error);
		}

		return $this->cancel()->withSuccess(trans('global.messages.update success'));
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param   integer  $id
	 * @return  Response
	 */
	public function delete($id)
	{
		// Incoming
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		foreach ($ids as $id)
		{
			// Delete the entry
			// Note: This is recursive and will also remove all descendents
			$row = Product::findOrFail($id);

			if (!$row->delete())
			{
				$request->session()->flash('error', $row->getError());
				continue;
			}

			$success++;
		}

		if ($success)
		{
			$request->session()->flash('success', trans('global.messages.item deleted', $success));
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
		return redirect(route('site.orders.products'));
	}
}
