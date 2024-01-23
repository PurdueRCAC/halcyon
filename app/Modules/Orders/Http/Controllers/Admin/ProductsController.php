<?php

namespace App\Modules\Orders\Http\Controllers\Admin;

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
	 * @param   StatefulRequest  $request
	 * @return  View
	 */
	public function index(StatefulRequest $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			'state'    => 'published',
			'category'  => 0,
			'restricteddata' => '*',
			'public'    => '*',
			'recurrence' => '*',
			// Paging
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			// Sorting
			'order'     => 'name',
			'order_dir' => Product::$orderDir,
		);

		$reset = false;
		$request = $request->mergeWithBase();
		foreach ($filters as $key => $default)
		{
			if ($key != 'page'
			 && $request->has($key) //&& session()->has('orders.products.filter_' . $key)
			 && $request->input($key) != session()->get('orders.products.filter_' . $key))
			{
				$reset = true;
			}
			$filters[$key] = $request->state('orders.products.filter_' . $key, $key, $default);
		}
		$filters['page'] = $reset ? 1 : $filters['page'];

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
			->whereNull($c . '.datetimeremoved')
			->withTrashed();

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
			$query->whereNull($p . '.datetimeremoved');
		}
		elseif ($filters['state'] == 'trashed')
		{
			$query->whereNotNull($p . '.datetimeremoved');
		}

		if ($filters['category'])
		{
			$query->where($p . '.ordercategoryid', '=', $filters['category']);
		}

		if ($filters['public'] != '*')
		{
			$query->where($p . '.public', '=', $filters['public']);
		}

		if ($filters['restricteddata'] != '*')
		{
			$query->where($p . '.restricteddata', '=', $filters['restricteddata']);
		}

		if ($filters['recurrence'] != '*')
		{
			$query->where($p . '.recurringtimeperiodid', '=', $filters['recurrence']);
		}

		$rows = $query
			->with('timeperiod')
			->with('resource')
			->orderBy($p . '.' . $filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page'])
			->appends(array_filter($filters));

		$categories = Category::query()
			->where('parentordercategoryid', '>', 0)
			->orderBy('name', 'asc')
			->get();

		return view('orders::admin.products.index', [
			'rows'    => $rows,
			'filters' => $filters,
			'categories' => $categories
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @param  Request $request
	 * @return View
	 */
	public function create(Request $request)
	{
		$row = new Product();

		if ($fields = $request->old('fields'))
		{
			$row->fill($fields);
		}

		$categories = Category::query()
			->where('parentordercategoryid', '>', 0)
			->orderBy('name', 'asc')
			->get();

		return view('orders::admin.products.edit', [
			'row' => $row,
			'categories' => $categories
		]);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  Request $request
	 * @param  int  $id
	 * @return View
	 */
	public function edit(Request $request, $id)
	{
		$row = Product::findOrFail($id);

		if ($fields = $request->old('fields'))
		{
			$row->fill($fields);
		}

		$categories = Category::query()
			->where('parentordercategoryid', '>', 0)
			->orderBy('name', 'asc')
			->get();

		return view('orders::admin.products.edit', [
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
			'fields.ticket' => 'nullable|integer',
			'fields.resourceid' => 'nullable|integer',
		]);

		if ($validator->fails())
		{
			return redirect()->back()->withInput()->withError($validator->messages());
		}

		$id = $request->input('id');

		$row = Product::findOrNew($id);
		$row->fill($request->input('fields'));
		$row->mou = $row->mou ?: '';
		$row->terms = $row->terms ?: '';
		$row->description = $row->description ?: '';
		$row->restricteddata = $request->has('fields.restricteddata') ? $request->input('fields.restricteddata') : 0;
		$row->ticket = $request->has('fields.ticket') ? $request->input('fields.ticket') : 0;

		if (!$row->save())
		{
			return redirect()->back()->withInput()->withError(trans('global.messages.save failed'));
		}

		return $this->cancel()->withSuccess(trans('global.messages.update success'));
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
		$row = Product::findOrFail($id);
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
		$return = Product::saveOrder($pks, $order);

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
	 * Remove the specified resource from storage.
	 *
	 * @param   Request $request
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
		return redirect(route('admin.orders.products'));
	}
}
