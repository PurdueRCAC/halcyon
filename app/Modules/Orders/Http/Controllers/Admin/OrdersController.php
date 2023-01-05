<?php

namespace App\Modules\Orders\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Modules\Orders\Models\Order;
use App\Modules\Orders\Models\Category;
use App\Modules\Orders\Models\Product;
use App\Modules\Orders\Models\Item;
use App\Modules\Orders\Models\Account;
use App\Modules\Users\Models\User;
use App\Modules\Users\Models\UserUsername;
use App\Halcyon\Http\StatefulRequest;
use Carbon\Carbon;

class OrdersController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * 
	 * @param   StatefulRequest  $request
	 * @return Response
	 */
	public function index(StatefulRequest $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			'status'    => 'active',
			'category'  => '*',
			'group'     => 0,
			'start'     => null,
			'end'       => null,
			'type'      => 0,
			'timeframe' => 7,
			// Paging
			'limit'     => config('list_limit', 20),
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
			 && $request->has($key) //&& session()->has('orders.filter_' . $key)
			 && $request->input($key) != session()->get('orders.filter_' . $key))
			{
				$reset = true;
			}
			$filters[$key] = $request->state('orders.filter_' . $key, $key, $default);
		}
		$filters['page'] = $reset ? 1 : $filters['page'];

		if (!in_array($filters['order'], ['id', 'datetimecreated', 'datetimeremoved', 'userid', 'ordertotal', 'usernotes']))
		{
			$filters['order'] = Order::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Order::$orderDir;
		}

		$order = new Order;

		//$query = Order::query();

		$query = $order->withTrashed();

		$o = $order->getTable();
		$u = (new User())->getTable();
		$uu = (new UserUsername())->getTable();
		$a = (new Account())->getTable();
		$i = (new Item())->getTable();
		//$p = (new Product())->getTable();

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
		/*if ($filters['product'] != '*')
		{
			$subitems->where($i . '.orderproductid', '=', $filters['product']);
		}*/
		/*"SELECT DISTINCT orders.id, orders.userid, orders.submitteruserid, orders.groupid, orders.datetimecreated, orders.datetimeremoved,
			GROUP_CONCAT(orderproducts.ordercategoryid SEPARATOR ',') AS ordercategoryid,
			SUM(orderitems.price) AS ordertotal,
			COUNT(orderitems.id) AS items,
			SUM(CASE 
				WHEN (orderitems.datetimefulfilled IS NULL) THEN 0 
				WHEN (orderitems.datetimefulfilled IS NOT NULL) THEN 1
			END) AS itemsfulfilled
			FROM orders
			LEFT JOIN orderitems ON orders.id = orderitems.orderid
			JOIN orderproducts ON orderitems.orderproductid = orderproducts.id
			WHERE orderitems.datetimeremoved IS NULL
			AND orderitems.quantity > 0 GROUP by orders.id";*/

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
					DB::raw("DISTINCT $o.*"),
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
				->leftJoin($a, function ($join) use ($a, $o)
				{
					$join->on($a . '.orderid', $o . '.id')
						->on(function($where) use ($a)
						{
							$where->whereNull($a . '.datetimeremoved');
						});
				})
				->groupBy($o . '.id')
				->groupBy($o . '.userid')
				->groupBy($o . '.datetimecreated')
				->groupBy($o . '.datetimeremoved')
				->groupBy($o . '.usernotes') 
				->groupBy($o . '.staffnotes')
				->groupBy($o . '.notice')
				->groupBy($o . '.submitteruserid')
				->groupBy($o . '.groupid');

				if ($filters['search'] && (is_numeric($filters['search']) || preg_match('/^[a-z]\d+$/', $filters['search'])))
				{
					$sub->where(function($query) use ($filters, $a, $o)
					{
						$query->where($a . '.purchaseio', '=', $filters['search'])
							->orWhere($o . '.id', '=', $filters['search'])
							->orWhere($a . '.purchasewbse', '=', $filters['search']);
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

				if ($filters['category'] != '*')
				{
					$p = (new Product())->getTable();

					$sub->join($i, $i . '.orderid', $o . '.id');
					$sub->join($p, $p . '.id', $i . '.orderproductid')
						->where($p . '.ordercategoryid', '=', $filters['category']);
				}
			}, 'tbaccounts')
			->joinSub($subitems, 'tbitems', function ($join) {
				$join->on('tbaccounts.id', '=', 'tbitems.id');
			})
			->leftJoin($u, $u . '.id', 'tbaccounts.userid');

		if ($filters['search'])
		{
			// We search by WSBE accounts above, so ignore them here
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

		if ($filters['group'])
		{
			$query->where($o . '.id', '=', $filters['group']);
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
				$query->where(DB::raw($state), '<', 6);
			}
		}

		if ($filters['order'] == 'userid')
		{
			$query->orderBy('name', $filters['order_dir']);
		}

		if ($request->has('export'))
		{
			$rows = $query
				->orderBy($filters['order'], $filters['order_dir'])
				->get();

			return $this->export($rows, $request->input('export'));
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page'])
			->appends(array_filter($filters));

		$categories = Category::query()
			->where('parentordercategoryid', '>', 0)
			->orderBy('name', 'asc')
			->get();

		return view('orders::admin.orders.index', [
			'rows'    => $rows,
			'filters' => $filters,
			'categories' => $categories
		]);
	}

	/**
	 * Download a list of records
	 * 
	 * @param  object  $rows
	 * @return Response
	 */
	public function export($rows, $export)
	{
		$data = array();
		$data[] = array(
			//trans('orders::orders.type'),
			trans('orders::orders.id'),
			trans('orders::orders.created'),
			trans('orders::orders.status'),
			trans('orders::orders.submitter'),
			trans('orders::orders.user'),
			trans('orders::orders.group'),
			trans('orders::orders.department'),
			trans('orders::orders.quantity'),
			trans('orders::orders.price'),
			trans('orders::orders.total'),
			'purchaseio',
			'purchasewbse',
			'paymentdocid',
			trans('orders::orders.product'),
			trans('orders::orders.notes'),
		);

		$orders = array();
		foreach ($rows as $row)
		{
			if (in_array($row->id, $orders))
			{
				continue;
			}

			$orders[] = $row->id;

			$submitter = '';
			$user = '';
			$group = '';
			$department = '';

			if ($row->groupid)
			{
				$group = $row->group ? $row->group->name : '';
				if ($row->group)
				{
					$first = $row->group->departmentList()->first();
					if ($first)
					{
						$department = $first->name;
					}
				}
			}

			if ($row->userid)
			{
				$user = $row->user ? $row->user->name : '';
			}

			if ($row->submitteruserid)
			{
				$submitter = $row->submitter ? $row->submitter->name : '';
			}

			//unset($row->state);

			$products = '';
			if ($export != 'items')
			{
				$products = array();
				foreach ($row->items as $item)
				{
					$products[] = $item->product ? $item->product->name : 'product #' . $item->orderproductid;
				}
				$products = implode(', ', $products);
			}

			if ($export != 'accounts')
			{
				$data[] = array(
					//'order',
					$row->id,
					$row->datetimecreated->format('Y-m-d'),
					trans('orders::orders.' . $row->status),
					$submitter,
					$user,
					$group,
					$department,
					'',
					'',
					$row->formatNumber($row->ordertotal),
					'',
					'',
					'',
					$products,
					$row->usernotes
				);
			}

			if ($export == 'items')
			{
				foreach ($row->items()->get() as $item)
				{
					$data[] = array(
						//'item',
						$item->orderid,
						$item->datetimecreated->format('Y-m-d'),
						$item->isFulfilled() ? 'fullfilled' : 'pending',
						$submitter,
						$user,
						$group,
						$department,
						$item->quantity,
						$row->formatNumber($item->origunitprice),
						$row->formatNumber($item->price),
						'',
						'',
						'',
						$item->product ? $item->product->name : $item->orderproductid,
						$row->usernotes
					);
				}
			}

			if ($export == 'accounts')
			{
				foreach ($row->accounts()->get() as $account)
				{
					$data[] = array(
						//'account',
						$account->orderid,
						$account->datetimecreated->format('Y-m-d'),
						trans('orders::orders.' . $account->status),
						$submitter,
						$user,
						$group,
						$department,
						'',
						'',
						$row->formatNumber($account->amount),
						($account->purchaseio ? $account->purchaseio : ''),
						($account->purchasewbse ? $account->purchasewbse : ''),
						($account->paymentdocid ? $account->paymentdocid : ''),
						$products,
						$row->usernotes
					);
				}
			}
		}

		$filename = 'orders_data.csv';

		$headers = array(
			'Content-type' => 'text/csv',
			'Content-Disposition' => 'attachment; filename=' . $filename,
			'Pragma' => 'no-cache',
			'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
			'Expires' => '0',
			'Last-Modified' => gmdate('D, d M Y H:i:s') . ' GMT'
		);

		$callback = function() use ($data)
		{
			$file = fopen('php://output', 'w');

			foreach ($data as $datum)
			{
				fputcsv($file, $datum);
			}
			fclose($file);
		};

		return response()->streamDownload($callback, $filename, $headers);

		// Set headers and output
		return new Response($output, 200, [
			'Content-Type' => 'text/csv;charset=UTF-8',
			'Content-Disposition' => 'attachment; filename="' . $file . '.csv"',
			'Last-Modified' => gmdate('D, d M Y H:i:s') . ' GMT'
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 * 
	 * @return Response
	 */
	public function create()
	{
		$order = new Order();

		$categories = Category::query()
			->where('parentordercategoryid', '>', 0)
			->orderBy('name', 'asc')
			->get();

		return view('orders::admin.orders.edit', [
			'order' => $order,
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
		$order = Order::find($id);

		$products = Product::query()
			->orderBy('name', 'asc')
			->get();

		$categories = Category::query()
			->where('parentordercategoryid', '>', 0)
			->orderBy('name', 'asc')
			->get();

		return view('orders::admin.orders.edit', [
			'order' => $order,
			'categories' => $categories,
			'products' => $products,
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
			'fields.userid' => 'required'
		]);

		$id = $request->input('id');

		$row = $id ? Order::findOrFail($id) : new Order();

		$row->fill($request->input('fields'));

		if (!$row->save())
		{
			return redirect()->back()->withError(trans('global.messages.save failed'));
		}

		return $this->cancel()->withSuccess(trans('global.messages.update success'));
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param   Request  $request
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
			$row = Order::findOrFail($id);

			if (!$row->delete())
			{
				$request->session()->flash('error', trans('global.messages.delete failed'));
				continue;
			}

			//$row->update(['notice' => 8]);

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
	 * @return  Response
	 */
	public function cancel()
	{
		return redirect(route('admin.orders.index'));
	}

	/**
	 * Show the form for editing the specified resource.
	 * 
	 * @return Response
	 */
	public function stats(Request $request)
	{
		$start = Carbon::now()->modify('-30 days');
		$today = Carbon::now()->modify('+1 day');

		// Get filters
		$filters = array(
			'start' => $start->format('Y-m-d'),
			'end' => $today->format('Y-m-d'),
			'recurring' => -1,
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->input($key, $default);
		}

		if ($filters['end'] < $filters['start'])
		{
			$request->session()->flash('error', trans('orders::orders.errors.end cannot be before start'));
			$filters['end'] = Carbon::parse($filters['start'])->modify('+1 week')->format('Y-m-d');
		}

		$categories = Category::query()
			->where('parentordercategoryid', '>', 0)
			->orderBy('name', 'asc')
			->get();

		$stats = Order::stats($filters['start'], $filters['end'], $filters['recurring']);

		return view('orders::admin.orders.stats', [
			'categories' => $categories,
			'filters' => $filters,
			'stats' => $stats,
		]);
	}
}
