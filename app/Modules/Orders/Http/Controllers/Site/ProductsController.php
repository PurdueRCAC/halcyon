<?php

namespace App\Modules\Orders\Http\Controllers\Site;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Modules\Orders\Models\Order;
use App\Modules\Orders\Models\Category;
use App\Modules\Orders\Models\Product;
use App\Modules\Orders\Models\Item;
use App\Modules\Users\Models\User;
use App\Halcyon\Http\StatefulRequest;

class ProductsController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * 
	 * @param  StatefulRequest  $request
	 * @return View
	 */
	public function index(StatefulRequest $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			'category'  => '*',
			'public'    => 1,
			'restricteddata' => '*',
			// Paging
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			// Sorting
			'order'     => 'sequence',
			'order_dir' => Product::$orderDir,
		);
		if (auth()->user() && auth()->user()->can('manage orders'))
		{
			$filters['public'] = '*';
		}

		$reset = false;
		$request = $request->mergeWithBase();
		foreach ($filters as $key => $default)
		{
			if ($key != 'page'
			 && $request->has($key) && session()->has('orders.site.products.filter_' . $key)
			 && $request->input($key) != session()->get('orders.site.products.filter_' . $key))
			{
				$reset = true;
			}
			$filters[$key] = $request->state('orders.site.products.filter_' . $key, $key, $default);
		}
		$filters['page'] = $reset ? 1 : $filters['page'];

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
			->select($p . '.*', $c . '.name AS category_name')
			->join($c, $c . '.id', $p . '.ordercategoryid')
			->whereNull($c . '.datetimeremoved');

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

		if (!auth()->user() || !auth()->user()->can('manage orders'))
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

		if ($filters['category'] != '*')
		{
			$query->where($p . '.ordercategoryid', '=', (int)$filters['category']);
		}

		if ($filters['restricteddata'] != '*')
		{
			$query->where($p . '.restricteddata', '=', (int)$filters['restricteddata']);
		}

		$rows = $query
			->orderBy($p . '.' . $filters['order'], $filters['order_dir'])
			->get();
			//->paginate($filters['limit'])
			//->appends(array_filter($filters));

		$categories = Category::query()
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
	 * @return View
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
			->whereNull($c . '.datetimeremoved');

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
			// Nothing here
		}
		elseif ($filters['state'] == 'trashed')
		{
			$query->onlyTrashed();
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
	 * @return View
	 */
	public function create()
	{
		$row = new Product();
		$row->public = 1;

		$categories = Category::query()
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
	 * @param  int  $id
	 * @return View
	 */
	public function edit($id)
	{
		$row = Product::findOrFail($id);

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		$categories = Category::query()
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
	 * @return  RedirectResponse
	 */
	public function store(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'fields.name' => 'required|string|max:255',
			'fields.ordercategoryid' => 'required|integer|min:1',
			'fields.description' => 'nullable|string|max:2000',
			'fields.mou' => 'nullable|string|max:255',
			'fields.unit' => 'nullable|string|max:16',
			'fields.unitprice' => 'nullable|string',
			'fields.recurringtimeperiodid' => 'nullable|integer',
			'fields.sequence' => 'nullable|integer|min:1',
			'fields.successororderproductid' => 'nullable|integer|min:1',
			'fields.terms' => 'nullable|string|max:2000',
			'fields.restricteddata' => 'nullable|integer',
			'fields.resourceid' => 'nullable|integer',
		]);

		if ($validator->fails())
		{
			return redirect()->back()->withInput()->withError($validator->messages());
		}

		$id = $request->input('id');

		$row = $id ? Product::findOrFail($id) : new Product();
		$row->fill($request->input('fields'));
		$row->terms = $row->terms ?: '';
		$row->description = $row->description ?: '';
		$row->mou = $row->mou ?: '';
		$row->restricteddata = $request->has('fields.restricteddata') ? $request->input('fields.restricteddata') : 0;

		if (!$row->save())
		{
			return redirect()->back()->withError(trans('global.messages.save failed'));
		}

		return $this->cancel()->with('success', trans('global.messages.item updated'));
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param   Request $request
	 * @param   int  $id
	 * @return  RedirectResponse
	 */
	public function delete(Request $request, $id)
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
				$request->session()->flash('error', trans('global.messages.delete failed'));
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
	 * @return  RedirectResponse
	 */
	public function cancel()
	{
		return redirect(route('site.orders.products'));
	}
}
