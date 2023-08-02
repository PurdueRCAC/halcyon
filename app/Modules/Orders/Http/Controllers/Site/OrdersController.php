<?php

namespace App\Modules\Orders\Http\Controllers\Site;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;
use Illuminate\Support\Facades\Storage;
use App\Modules\Orders\Helpers\Export;
use App\Modules\Orders\Models\Order;
use App\Modules\Orders\Models\Category;
use App\Modules\Orders\Models\Product;
use App\Modules\Orders\Models\Item;
use App\Modules\Orders\Models\Account;
use App\Modules\Users\Models\User;
use App\Modules\Users\Models\UserUsername;
use App\Halcyon\Http\StatefulRequest;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use Carbon\Carbon;

class OrdersController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * 
	 * @param  StatefulRequest  $request
	 * @return View|StreamedResponse
	 */
	public function index(StatefulRequest $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			'status'    => 'active',
			'category'  => '*',
			'product'   => '*',
			'start'     => null,
			'end'       => null,
			'type'      => 0,
			'userid'    => 0,
			// Paging
			'limit'     => config('list_limit', '20'),
			'page'      => 1,
			// Sorting
			'order'     => Order::$orderBy,
			'order_dir' => Order::$orderDir,
		);

		$reset = false;
		$request = $request->mergeWithBase();
		foreach ($filters as $key => $default)
		{
			if ($key != 'page'
			 && $request->has($key) && session()->has('orders.site.filter_' . $key)
			 && $request->input($key) != session()->get('orders.site.filter_' . $key))
			{
				$reset = true;
			}
			$filters[$key] = $request->state('orders.site.filter_' . $key, $key, $default);
		}
		$filters['page'] = $reset ? 1 : $filters['page'];

		if (!in_array($filters['order'], ['id', 'state', 'userid', 'datetimecreated', 'datetimeremoved']))
		{
			$filters['order'] = Order::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Order::$orderDir;
		}

		if (auth()->user() && !auth()->user()->can('manage orders'))
		{
			$filters['userid'] = auth()->user()->id;
		}

		$order = new Order;

		$query = $order->withTrashed();

		$o = $order->getTable();
		$u = (new User())->getTable();
		$uu = (new UserUsername())->getTable();
		$a = (new Account())->getTable();
		$i = (new Item())->getTable();

		$state = "CASE
					WHEN (tbaccounts.datetimeremoved IS NOT NULL) THEN 7
					WHEN (
							(accounts = 0 AND ordertotal > 0) OR
							amountassigned < ordertotal OR
							(accountsdenied > 0 AND (accountsdenied + accountsapproved) = accounts)
						) THEN 3
					WHEN (accountsassigned < accounts) THEN 2
					WHEN (accountsapproved < accounts) THEN 4
					WHEN (accountsapproved = accounts AND itemsfulfilled < items_count) THEN 1
					WHEN (itemsfulfilled = items_count AND accountspaid < accounts) THEN 5
					ELSE 6
					END";

		$subitems = Order::query()
			->withTrashed()
			->select(
				$o . '.*',
				DB::raw("SUM(" . $i . ".price) AS ordertotal"),
				DB::raw("COUNT(" . $i . ".id) AS items_count"),
				DB::raw("SUM(CASE 
					WHEN (" . $i . ".datetimefulfilled IS NULL) THEN 0 
					WHEN (" . $i . ".datetimefulfilled IS NOT NULL) THEN 1
				END) AS itemsfulfilled")
			)
			->leftJoin($i, $i . '.orderid', $o . '.id')
			//->join($p, $p . '.id', $i . '.orderproductid')
			->whereNull($i . '.datetimeremoved')
			->where($i . '.quantity', '>', 0)
			->groupBy($o . '.id')
			->groupBy($o . '.userid')
			->groupBy($o . '.datetimecreated')
			->groupBy($o . '.datetimeremoved')
			->groupBy($o . '.usernotes')
			->groupBy($o . '.staffnotes')
			->groupBy($o . '.notice')
			->groupBy($o . '.submitteruserid')
			->groupBy($o . '.groupid');
		if ($filters['product'] != '*')
		{
			$subitems->where($i . '.orderproductid', '=', $filters['product']);
		}
		/*if ($filters['userid'])
		{
			$subitems->leftJoin($a, function ($join) use ($a, $o)
				{
					$join->on($a . '.orderid', $o . '.id')
						->on(function($where) use ($a)
						{
							$where->whereNull($a . '.datetimeremoved');
						});
				});
			$subitems->where(function($query) use ($filters, $o, $a)
			{
				$query->where($o . '.userid', '=', $filters['userid'])
					->orWhere($o . '.submitteruserid', '=', $filters['userid'])
					->orWhere($a . '.approveruserid', '=', $filters['userid']);
			});
		}*/

		$query
			->select([
				//$o . '.*',
				'tbaccounts.*',
				$u . '.name',
				'tbitems.items_count',
				'tbitems.ordertotal',
				//'accounts',
				//'items',
				'tbitems.itemsfulfilled',
				/*'accountsassigned',
				'amountassigned',
				'accountsapproved',
				'accountspaid',
				'accountsdenied',
				DB::raw('SUM(' . $i . '.price) AS ordertotal'),
				DB::raw("SUM(" . $i . ".id) AS accounts"),
				DB::raw("COUNT(orderitems.id) AS items"),
				DB::raw("SUM(CASE WHEN (" . $i . ".datetimefulfilled IS NULL) THEN 0 WHEN (" . $i . ".datetimefulfilled IS NULL) THEN 0 WHEN (" . $i . ".datetimefulfilled IS NOT NULL) THEN 1 END) AS itemsfulfilled"),
				DB::raw('SUM(CASE WHEN (' . $a .'.approveruserid IS NULL) THEN 0 WHEN (' . $a .'.approveruserid = 0) THEN 0 WHEN (' . $a .'.approveruserid > 0) THEN 1 END) AS accountsassigned'),
				DB::raw('SUM(' . $a .'.amount) AS amountassigned'),
				DB::raw("SUM(CASE WHEN (" . $a .".datetimeapproved IS NULL) THEN 0 WHEN (" . $a .".datetimeapproved IS NULL) THEN 0 WHEN (" . $a .".datetimeapproved IS NOT NULL) THEN 1 END) AS accountsapproved"),
				DB::raw("SUM(CASE WHEN (" . $a .".datetimepaid IS NULL) THEN 0 WHEN (" . $a .".datetimepaid IS NULL) THEN 0 WHEN (" . $a .".datetimepaid IS NOT NULL) THEN 1 END) AS accountspaid"),
				DB::raw("SUM(CASE WHEN (" . $a .".datetimedenied IS NULL) THEN 0 WHEN (" . $a .".datetimedenied IS NULL) THEN 0 WHEN (" . $a .".datetimedenied IS NOT NULL) THEN 1 END) AS accountsdenied"),*/
				DB::raw($state . ' AS state')
			])
			->fromSub(function($sub) use ($o, $a, $i, $filters)
			{
				$sub->select(
					//$o . '.*',
					DB::raw("DISTINCT $o.*"),//, $a.approveruserid"),
					//DB::raw('SUM(' . $i . '.price) AS ordertotal'),
					DB::raw("COUNT(" . $a . ".id) AS accounts"),
					//DB::raw("COUNT(" . $i . ".id) AS items_count"),
					//DB::raw("SUM(CASE WHEN (" . $i . ".datetimefulfilled IS NULL) THEN 0 WHEN (" . $i . ".datetimefulfilled IS NULL) THEN 0 WHEN (" . $i . ".datetimefulfilled IS NOT NULL) THEN 1 END) AS itemsfulfilled"),
					DB::raw('SUM(CASE WHEN (' . $a .'.approveruserid IS NULL) THEN 0 WHEN (' . $a .'.approveruserid = 0) THEN 0 WHEN (' . $a .'.approveruserid > 0) THEN 1 END) AS accountsassigned'),
					DB::raw('SUM(' . $a .'.amount) AS amountassigned'),
					DB::raw("SUM(CASE WHEN (" . $a .".datetimeapproved IS NULL) THEN 0 WHEN (" . $a .".datetimeapproved IS NULL) THEN 0 WHEN (" . $a .".datetimeapproved IS NOT NULL) THEN 1 END) AS accountsapproved"),
					DB::raw("SUM(CASE WHEN (" . $a .".datetimepaid IS NULL) THEN 0 WHEN (" . $a .".datetimepaid IS NULL) THEN 0 WHEN (" . $a .".datetimepaid IS NOT NULL) THEN 1 END) AS accountspaid"),
					DB::raw("SUM(CASE WHEN (" . $a .".datetimedenied IS NULL) THEN 0 WHEN (" . $a .".datetimedenied IS NULL) THEN 0 WHEN (" . $a .".datetimedenied IS NOT NULL) THEN 1 END) AS accountsdenied")
				)
				->from($o)
				//->leftJoin($a, $a . '.orderid', $o . '.id')
				->leftJoin($a, function ($join) use ($a, $o)
				{
					$join->on($a . '.orderid', $o . '.id')
						->on(function($where) use ($a)
						{
							$where->whereNull($a . '.datetimeremoved');
						});
				})
				//->leftJoin($i, $i . '.orderid', $o . '.id')
				//->whereNull($i . '.datetimeremoved')
				//->where($i . '.quantity', '>', 0)
				/*->whereNull($a . '.datetimeremoved')*/
				->groupBy($o . '.id')
				->groupBy($o . '.userid')
				->groupBy($o . '.datetimecreated')
				->groupBy($o . '.datetimeremoved')
				->groupBy($o . '.usernotes') 
				->groupBy($o . '.staffnotes')
				->groupBy($o . '.notice')
				->groupBy($o . '.submitteruserid')
				//->groupBy($a . '.approveruserid')
				->groupBy($o . '.groupid');

				if ($filters['search'] && (is_numeric($filters['search']) || preg_match('/^[a-z]\d+$/', $filters['search'])))
				{
					$sub->where(function($query) use ($filters, $a, $o)
					{
						$query->where($a . '.purchaseio', '=', $filters['search'])
							->orWhere($o . '.id', '=', $filters['search'])
							->orWhere($a . '.purchasewbse', '=', $filters['search']);
						//$query->where($o . '.usernotes', 'like', '%' . $filters['search'] . '%')
						//	->orWhere($u . '.name', 'like', '%' . $filters['search'] . '%');
					});
				}

				if ($filters['start'])
				{
					$sub->where($o . '.datetimecreated', '>=', $filters['start']);
				}

				if ($filters['end'])
				{
					$sub->where($o . '.datetimecreated', '<', $filters['end']);
				}

				if ($filters['category'] != '*' || $filters['product'] != '*')
				{
					$sub->join($i, $i . '.orderid', $o . '.id');
				}

				if ($filters['userid'])
				{
					$sub->where(function($query) use ($filters, $o, $a)
					{
						$query->where($o . '.userid', '=', $filters['userid'])
							->orWhere($o . '.submitteruserid', '=', $filters['userid'])
							->orWhere($a . '.approveruserid', '=', $filters['userid']);
					});
				}

				if ($filters['category'] != '*')
				{
					$p = (new Product())->getTable();
					//$i = (new Item())->getTable();

					$sub->join($p, $p . '.id', $i . '.orderproductid')
						->where($p . '.ordercategoryid', '=', $filters['category']);
				}
				if ($filters['product'] != '*')
				{
					$sub->where($i . '.orderproductid', '=', $filters['product']);
				}
			}, 'tbaccounts')
			->joinSub($subitems, 'tbitems', function ($join) {
				$join->on('tbaccounts.id', '=', 'tbitems.id');
			})
			->leftJoin($u, $u . '.id', 'tbaccounts.userid');
			//->leftJoin($a, $a . '.orderid', $o . '.id');
			/*->leftJoin($i, $i . '.orderid', $o . '.id')

			->whereNull($a . '.datetimeremoved');*/

		if ($filters['search'])
		{
			/*if (is_numeric($filters['search']))
			{
				$query->where('tbaccounts.id', '=', $filters['search']);
			}
			else*/
			if (!is_numeric($filters['search']) && !preg_match('/^[a-z]\d+$/', $filters['search']))
			{
				$g = (new \App\Modules\Groups\Models\Group())->getTable();

				$query->leftJoin($g, $g . '.id', 'tbaccounts.groupid')
					->leftJoin($uu, $uu . '.userid', $u . '.id')
					->where(function($query) use ($filters, $g, $u, $uu)
					{
						$query->where($g . '.name', 'like', '%' . $filters['search'] . '%')
							->orWhere($u . '.name', 'like', '%' . $filters['search'] . '%')
							->orWhere($uu . '.username', 'like', '%' . $filters['search'] . '%');
					});
			}
		}

		if ($filters['status'] != '*')
		{
			if ($filters['status'] == 'canceled')
			{
				$query->where(DB::raw($state), '=', 7);
			}
			elseif ($filters['status'] == 'complete')
			{
				$query->where(DB::raw($state), '=', 6);
			}
			elseif ($filters['status'] == 'pending_payment')
			{
				$query->where(DB::raw($state), '=', 3);
			}
			elseif ($filters['status'] == 'pending_boassignment')
			{
				$query->where(DB::raw($state), '=', 2);
			}
			elseif ($filters['status'] == 'pending_collection')
			{
				$query->where(DB::raw($state), '=', 5);
			}
			elseif ($filters['status'] == 'pending_approval')
			{
				$query->where(DB::raw($state), '=', 4);
			}
			elseif ($filters['status'] == 'pending_fulfillment')
			{
				$query->where(DB::raw($state), '=', 1);
			}
			elseif ($filters['status'] == 'active')
			{
				//$query->whereIn('state', [1, 2, 3, 4, 5]);
				$query->where(DB::raw($state), '<', 6);
			}
		}

		/*if ($filters['userid'])
		{
			$query->where(function($query) use ($filters)
			{
				$query->where('tbaccounts.userid', '=', $filters['userid'])
					->orWhere('tbaccounts.submitteruserid', '=', $filters['userid'])
					->orWhere('tbaccounts.approveruserid', '=', $filters['userid']);
			});
		}

		if ($filters['category'] != '*')
		{
			$p = (new Product())->getTable();
			//$i = (new Item())->getTable();

			$query->join($i, $i . '.orderid', $o . '.id')
				->join($p, $p . '.id', $i . '.orderproductid')
				->where($p . '.ordercategoryid', '=', $filters['category']);
		}*/

		if ($request->has('export'))
		{
			$rows = $query
				->orderBy($filters['order'], $filters['order_dir'])
				->get();

			return Export::toCsv($rows, $request->input('export'));
		}

		$rows = $query
			//->withCount('items')
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page'])
			->appends(array_filter($filters));

		$categories = Category::query()
			->where('parentordercategoryid', '>', 0)
			->orderBy('name', 'asc')
			->get();

		$query = Product::query();

		if (!auth()->user() || !auth()->user()->can('manage orders'))
		{
			$query->where('public', '=', 1);
		}
		if ($filters['category'] != '*')
		{
			$query->where('ordercategoryid', '=', $filters['category']);
		}

		$products = $query
			->orderBy('name', 'asc')
			->get();

		return view('orders::site.orders.index', [
			'rows'    => $rows,
			'filters' => $filters,
			'categories' => $categories,
			'products' => $products
		]);
	}

	/**
	 * Show the specified resource.
	 * 
	 * @param  StatefulRequest  $request
	 * @return View
	 */
	public function recurring(StatefulRequest $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			'product'   => 0,
			'userid'    => 0,
			// Paging
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			// Sorting
			'order'     => 'id',
			'order_dir' => 'asc',
		);

		/*$k = array();
		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->input($key, $default);
			if (!in_array($key, ['page', 'order', 'order_dir']))
			{
				$k[$key] = $filters[$key];
			}
		}
		$k = json_encode($k);
		
		if (session()->get('filters.orders.recur') && session()->get('filters.orders.recur') != $k)
		{
			$filters['page'] = 1;
		}
		session()->put('orders.recur', $k);*/

		$reset = false;
		foreach ($filters as $key => $default)
		{
			if ($key != 'page' && session()->get('orders.recur.filter_' . $key) != $request->mergeWithBase()->input($key))
			{
				$reset = true;
			}
			$filters[$key] = $request->state('orders.recur.filter_' . $key, $key, $default);
		}
		$filters['page'] = $reset ? 1 : $filters['page'];

		if (!in_array($filters['order'], ['id', 'product', 'billeduntil', 'paiduntil']))
		{
			$filters['order'] = 'id';
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = 'asc';
		}

		$o = (new Order)->getTable();
		$i = (new Item())->getTable();
		$p = (new Product)->getTable();

		$query = Item::query()
			->select($i . '.*')
			->where($i . '.origorderitemid', '<>', 0)
			->join($o, $o . '.id', $i . '.orderid')
			->join($p, $p . '.id', $i . '.orderproductid')
			->withTrashed()
			->whereNull($i . '.datetimeremoved')
			->whereNull($o . '.datetimeremoved')
			->whereNull($p . '.datetimeremoved');

		if (auth()->user())
		{
			if (!auth()->user()->can('manage orders'))
			{
				$query->where(function($where) use ($o)
				{
					$where->where($o . '.userid', '=', auth()->user()->id)
						->orWhere($o . '.submitteruserid', '=', auth()->user()->id);
				});
			}
		}

		if ($filters['search'])
		{
			if (is_numeric($filters['search']))
			{
				$query->where($i . '.id', '=', $filters['search']);
			}
			else
			{
				$u = (new User())->getTable();
				$g = (new \App\Modules\Groups\Models\Group())->getTable();

				$query->leftJoin($u, $u . '.id', $o . '.userid');
				$query->leftJoin($g, $g . '.id', $o . '.groupid')
					->where(function($query) use ($filters, $g, $u)
					{
						$query->where($g . '.name', 'like', $filters['search'] . '%')
							->orWhere($g . '.name', 'like', '%' . $filters['search'] . '%')
							->orWhere($u . '.name', 'like', $filters['search'] . '%')
							->orWhere($u . '.name', 'like', '%' . $filters['search'] . '%');
					});
			}
		}

		if ($filters['product'] && $filters['product'] != '*')
		{
			$query->where($i . '.orderproductid', '=', $filters['product']);
		}

		if ($filters['order'] == 'product')
		{
			$query->orderBy($p . '.name', $filters['order_dir']);
		}
		else
		{
			$query->orderBy($filters['order'], $filters['order_dir']);
		}

		$rows = $query
			->paginate($filters['limit'], ['*'], 'page', $filters['page'])
			->appends(array_filter($filters));

		$products = Product::query()
			->where('recurringtimeperiodid', '>', 0)
			->orderBy('recurringtimeperiodid', 'asc')
			->orderBy('name', 'asc')
			->get();

		return view('orders::site.recurring.index', [
			'rows'    => $rows,
			'filters' => $filters,
			'products' => $products
		]);
	}

	/**
	 * Show the form for editing the specified resource.
	 * 
	 * @param  int  $id
	 * @return View
	 */
	public function recurringitem($id)
	{
		$item = Item::findOrFail($id);

		$items = $item->recurrenceRange();

		return view('orders::site.recurring.read', [
			'item' => $item,
			'items' => $items
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 * 
	 * @return View
	 */
	public function create()
	{
		app('pathway')
			->append(
				trans('orders::orders.orders'),
				route('site.orders.index')
			)
			->append(
				trans('orders::orders.new order'),
				route('site.orders.create')
			);

		return view('resources::site.create');
	}

	/**
	 * Show the form for editing the specified resource.
	 * 
	 * @param  int  $id
	 * @return View
	 */
	public function edit($id)
	{
		$order = Order::query()
			->withTrashed()
			->where('id', '=', $id)
			->first();

		if (!$order)
		{
			abort(404, 'Order Not Found');
		}

		$myorder = (auth()->user()->id == $order->submitteruserid || auth()->user()->id == $order->userid);
		$canEdit = (auth()->user()->can('edit orders') || (auth()->user()->can('edit.own orders') && $myorder));
		$approvers = $order->accounts->pluck('approveruserid')->toArray();

		if (!$myorder && !$canEdit && !in_array(auth()->user()->id, $approvers))
		{
			abort(403, 'Not Authorized');
		}

		app('pathway')
			->append(
				trans('orders::orders.orders'),
				route('site.orders.index')
			)
			->append(
				'#' . $id,
				route('site.orders.read', ['id' => $id])
			);

		$products = Product::query()
			->orderBy('name', 'asc')
			->get();

		return view('orders::site.orders.edit', [
			'order' => $order,
			'products' => $products
		]);
	}

	/**
	 * Remove the specified resource from storage.
	 * 
	 * @param  Request $request
	 * @return View
	 */
	public function cart(Request $request)
	{
		$cart = app('cart');
		$cart->restore(auth()->user()->username);

		return view('orders::site.orders.cart', [
			'cart' => $cart
		]);
	}

	/**
	 * Display the data being imported
	 * 
	 * @param  Request $request
	 * @return RedirectResponse|View
	 */
	public function import(Request $request)
	{
		if (!$request->has('file'))
		{
			return redirect()->route('site.orders.index')->withError(trans('orders::orders.errors.file not found'));
		}

		// Doing this by file extension is iffy at best but
		// detection by contents productes `txt`
		$parts = explode('.', $request->file('file')->getClientOriginalName());
		$extension = end($parts);
		$extension = strtolower($extension);

		if (!in_array($extension, ['csv', 'xlsx', 'ods']))
		{
			return redirect()->route('site.orders.index')->withError(trans('orders::orders.errors.invalid file type'));
		}

		$file = $request->file('file')->store('temp');
		$path = storage_path('app/' . $file);

		try
		{
			$data = $this->getSpreadsheetData($path);
		}
		catch (\Exception $e)
		{
			Storage::disk('local')->delete($file);

			return redirect()->route('site.orders.index')->withError($e->getMessage());
		}

		return view('orders::site.orders.import', [
			'file' => $file,
			'headers' => $data->headers,
			'data' => $data->cells
		]);
	}

	/**
	 * Process the imported data
	 * 
	 * @param  Request $request
	 * @return RedirectResponse
	 */
	public function process(Request $request)
	{
		if (!$request->has('file'))
		{
			return redirect()->route('site.orders.index')->withError(trans('orders::orders.errors.file not found'));
		}

		$file = base64_decode($request->input('file'));
		$path = storage_path('app/' . $file);

		if (!Storage::disk('local')->exists($file))
		{
			return redirect()->route('site.orders.index')->withError(trans('orders::orders.errors.file not found'));
		}

		$updated = 0;

		try
		{
			$data = $this->getSpreadsheetData($path);

			foreach ($data->cells as $item)
			{
				if (!$item->id)
				{
					continue;
				}

				$order = Order::find($item->id);

				if (!$order)
				{
					continue;
				}

				// Do we have any account info?
				if (!$item->purchaseio
				 && !$item->purchasewbse)
				{
					continue;
				}

				// Was a doc ID assigned?
				if (!$item->paymentdocid)
				{
					continue;
				}

				foreach ($order->accounts as $account)
				{
					if (($account->purchaseio == $item->purchaseio || $account->purchasewbse == $item->purchasewbse)
					 && $item->paymentdocid != $account->paymentdocid)
					{
						$account->paymentdocid = $item->paymentdocid;
						$account->datetimepaymentdoc = Carbon::now()->toDateTimeString();
						if ($item->datetimepaymentdoc)
						{
							$account->datetimepaymentdoc = $item->datetimepaymentdoc;
						}
						$account->datetimepaid = Carbon::now()->toDateTimeString();
						$account->save();

						$updated++;
					}
				}

				if ($order->status == 'complete')
				{
					$order->update(['notice' => 7]); // Complete
				}
			}

			// Clean up
			Storage::disk('local')->delete($file);
		}
		catch (\Exception $e)
		{
			// Clean up
			Storage::disk('local')->delete($file);

			return redirect()->route('site.orders.index')->withError($e->getMessage());
		}

		if ($updated)
		{
			$request->session()->flash('success', trans('orders::orders.accounts updated', ['count' => $updated]));
		}

		return redirect()->route('site.orders.index');
	}

	/**
	 * Read the data from the spreadsheet
	 * 
	 * @param  string  $path
	 * @return Fluent
	 */
	private function getSpreadsheetData($path)
	{
		$parts = explode('.', $path);
		$extension = end($parts);
		$extension = strtolower($extension);
		$headers = array();
		$data = array();

		/*$handle = fopen($path, 'r');

		if ($handle !== false)
		{
			while (!feof($handle))
			{
				$line = fgetcsv($handle, 0, ',');

				if ($row == 0)
				{
					$headers = $line;
					$row++;
					continue;
				}

				$item = new Fluent;
				foreach ($headers as $k => $v)
				{
					$v = strtolower($v);
					$item->{$v} = $line[$k];
				}

				$data[] = $item;

				$row++;
			}
			fclose($handle);
		}*/

		if ($extension == 'csv' || $extension == 'txt')
		{
			$reader = new CsvReader();
		}
		else
		{
			$reader = new XlsxReader();
		}

		$reader->open($path);

		foreach ($reader->getSheetIterator() as $sheet)
		{
			foreach ($sheet->getRowIterator() as $i => $row)
			{
				// do stuff with the row
				$cells = $row->getCells();

				if (empty($headers))
				{
					foreach ($cells as $j => $cell)
					{
						$headers[] = trim($cell->getValue());
					}

					continue;
				}

				$item = new Fluent;
				foreach ($headers as $k => $v)
				{
					$v = strtolower($v);
					$item->{$v} = $cells[$k]->getValue();
				}

				$data[] = $item;
			}
		}

		$reader->close();

		$info = new Fluent;
		$info->headers = $headers;
		$info->cells = collect($data);

		return $info;
	}
}
