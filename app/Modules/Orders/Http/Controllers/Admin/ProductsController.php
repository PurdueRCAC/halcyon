<?php

namespace App\Modules\Orders\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
	 * @return Response
	 */
	public function index(StatefulRequest $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			'state'    => 'published',
			'category'  => 0,
			// Paging
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			// Sorting
			'order'     => 'name',
			'order_dir' => Product::$orderDir,
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->state('orders.products.filter_' . $key, $key, $default);
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
			->where(function($where) use ($c)
			{
				$where->whereNull($c . '.datetimeremoved')
					->orWhere($c . '.datetimeremoved', '=', '0000-00-00 00:00:00');
			})
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
			$query->where(function($where) use ($p)
			{
				$where->whereNull($p . '.datetimeremoved')
					->orWhere($p . '.datetimeremoved', '=', '0000-00-00 00:00:00');
			});
		}
		elseif ($filters['state'] == 'trashed')
		{
			$query->where(function($where) use ($p)
			{
				$where->wherNoyeNull($p . '.datetimeremoved')
					->where($p . '.datetimeremoved', '!=', '0000-00-00 00:00:00');
			});
		}

		if ($filters['category'])
		{
			$query->where($p . '.ordercategoryid', '=', $filters['category']);
		}

		$rows = $query
			->orderBy($p . '.' . $filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page'])
			->appends(array_filter($filters));

		$categories = Category::query()
			->where(function($where)
			{
				$where->whereNull('datetimeremoved')
					->orWhere('datetimeremoved', '=', '0000-00-00 00:00:00');
			})
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
	 * @return Response
	 */
	public function create()
	{
		$row = new Product();

		$categories = Category::query()
			->where(function($where)
			{
				$where->whereNull('datetimeremoved')
					->orWhere('datetimeremoved', '=', '0000-00-00 00:00:00');
			})
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
	 * @return Response
	 */
	public function edit($id)
	{
		$row = Product::findOrFail($id);

		if ($fields = app('request')->old('fields'))
		{print_r($fields);
			$row->fill($fields);
		}

		$categories = Category::query()
			->where(function($where)
			{
				$where->whereNull('datetimeremoved')
					->orWhere('datetimeremoved', '=', '0000-00-00 00:00:00');
			})
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
	 * @return  Response
	 */
	public function store(Request $request)
	{
		$validator = Validator::make($request->all(), [
		//$request->validateWithBag('errors', [
			'fields.name' => 'required|string|max:255',
			'fields.ordercategoryid' => 'required|integer|min:1',
			'fields.unitprice' => 'required|string',
			'fields.unit' => 'required|string|min:1,max:16',
		]);

		if ($validator->fails()) //!$request->validated())
		{
			//return response()->json(['message' => $validator->messages()->first()], 409);
			return redirect()->back()->withInput()->withError($validator->messages());
		}

		$id = $request->input('id');

		$row = $id ? Product::findOrFail($id) : new Product();

		$row->fill($request->input('fields'));
		$row->mou = $row->mou ?: '';

		if (!$row->save())
		{
			$error = $row->getError() ? $row->getError() : trans('messages.save failed');

			return redirect()->back()->withInput()->withError($error);
		}

		return $this->cancel()->withSuccess(trans('messages.update success'));
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param   Request $request
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
			$request->session()->flash('success', trans('messages.item deleted', ['count' => $success]));
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
		return redirect(route('admin.orders.products'));
	}
}
