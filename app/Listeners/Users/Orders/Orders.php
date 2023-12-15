<?php
namespace App\Listeners\Users\Orders;

use Illuminate\Support\Facades\DB;
use App\Modules\Orders\Models\Order;
use App\Modules\Orders\Models\Category;
use App\Modules\Orders\Models\Product;
use App\Modules\Orders\Models\Item;
use App\Modules\Orders\Models\Account;
use App\Modules\Users\Models\User;
use App\Modules\Users\Events\UserDisplay;
use App\Modules\Users\Events\UserNotifying;
use App\Modules\Users\Entities\Notification;

/**
 * User listener for Orders
 */
class Orders
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  \Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen(UserDisplay::class, self::class . '@handleUserDisplay');
		$events->listen(UserNotifying::class, self::class . '@handleUserNotifying');
	}

	/**
	 * Display data for a user
	 *
	 * @param   UserDisplay  $event
	 * @return  void
	 */
	public function handleUserDisplay(UserDisplay $event)
	{
		$content = null;
		$user = $event->getUser();
		$total = 0;

		$route = route('site.orders.index');

		$r = ['section' => 'orders'];
		if (auth()->user()->id != $user->id)
		{
			$r['u'] = $user->id;
			$route = route('site.orders.index', ['userid' => $user->id, 'status' => '*']);
		}

		/*if ($event->getActive() == 'orders')
		{*/
			// Get filters
			$filters = array(
				'search'    => null,
				'status'    => '*',
				'category'  => '*',
				'start'     => null,
				'end'       => null,
				'type'      => 0,
				'userid'    => $user->id,
				// Paging
				'limit'     => config('list_limit', 20),
				'page'      => 1,
				// Sorting
				'order'     => Order::$orderBy,
				'order_dir' => Order::$orderDir,
			);

			/*foreach ($filters as $key => $default)
			{
				$filters[$key] = $request->input($key, $default);
			}

			if (!in_array($filters['order'], ['id', 'datetimecreated', 'datetimeremoved']))
			{
				$filters['order'] = Order::$orderBy;
			}

			if (!in_array($filters['order_dir'], ['asc', 'desc']))
			{
				$filters['order_dir'] = Order::$orderDir;
			}*/

			$order = new Order;

			$query = $order->withTrashed();

			$o = $order->getTable();
			$u = (new User())->getTable();
			$a = (new Account())->getTable();
			$i = (new Item())->getTable();

			$state = "CASE 
						WHEN (tbaccounts.datetimeremoved IS NOT NULL) THEN 7
						WHEN (
								(accounts = 0 AND ordertotal > 0) OR
								amountassigned <> ordertotal OR
								(accountsdenied > 0 AND (accountsdenied + accountsapproved) = accounts)
							) THEN 3
						WHEN (accountsassigned < accounts) THEN 2
						WHEN (accountsapproved < accounts) THEN 4
						WHEN (accountsapproved = accounts AND itemsfulfilled < items) THEN 1
						WHEN (itemsfulfilled = items AND accountspaid < accounts) THEN 5
						ELSE 6
						END";

			$query
				->select([
					//$o . '.*',
					'tbaccounts.*',
					$u . '.name',
					DB::raw($state . ' AS state')
				])
				->fromSub(function($sub) use ($o, $a, $i, $filters)
				{
					$sub->select(
						$o . '.*',
						$a . '.approveruserid',
						DB::raw('SUM(' . $i . '.price) AS ordertotal'),
						DB::raw("COUNT(" . $a . ".id) AS accounts"),
						DB::raw("COUNT(" . $i . ".id) AS items"),
						DB::raw("SUM(CASE WHEN (" . $i . ".datetimefulfilled IS NULL) THEN 0 WHEN (" . $i . ".datetimefulfilled IS NULL) THEN 0 WHEN (" . $i . ".datetimefulfilled IS NOT NULL) THEN 1 END) AS itemsfulfilled"),
						DB::raw('SUM(CASE WHEN (' . $a .'.approveruserid IS NULL) THEN 0 WHEN (' . $a .'.approveruserid = 0) THEN 0 WHEN (' . $a .'.approveruserid > 0) THEN 1 END) AS accountsassigned'),
						DB::raw('SUM(' . $a .'.amount) AS amountassigned'),
						DB::raw("SUM(CASE WHEN (" . $a .".datetimeapproved IS NULL) THEN 0 WHEN (" . $a .".datetimeapproved IS NULL) THEN 0 WHEN (" . $a .".datetimeapproved IS NOT NULL) THEN 1 END) AS accountsapproved"),
						DB::raw("SUM(CASE WHEN (" . $a .".datetimepaid IS NULL) THEN 0 WHEN (" . $a .".datetimepaid IS NULL) THEN 0 WHEN (" . $a .".datetimepaid IS NOT NULL) THEN 1 END) AS accountspaid"),
						DB::raw("SUM(CASE WHEN (" . $a .".datetimedenied IS NULL) THEN 0 WHEN (" . $a .".datetimedenied IS NULL) THEN 0 WHEN (" . $a .".datetimedenied IS NOT NULL) THEN 1 END) AS accountsdenied")
					)
					->from($o)
					->leftJoin($a, $a . '.orderid', $o . '.id')
					->leftJoin($i, $i . '.orderid', $o . '.id')
					->whereNull($i . '.datetimeremoved')
					->where($i . '.quantity', '>', 0)
					->whereNull($a . '.datetimeremoved')
					->groupBy($o . '.id')
					->groupBy($o . '.userid')
					->groupBy($o . '.submitteruserid')
					->groupBy($o . '.groupid')
					->groupBy($o . '.datetimecreated')
					->groupBy($o . '.datetimeremoved')
					->groupBy($o . '.datetimenotified')
					->groupBy($o . '.usernotes')
					->groupBy($o . '.staffnotes')
					->groupBy($o . '.notice')
					->groupBy($a . '.approveruserid');

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
						//$i = (new Item())->getTable();

						$sub->join($p, $p . '.id', $i . '.orderproductid')
							->where($p . '.ordercategoryid', '=', $filters['category']);
					}
				}, 'tbaccounts')
				->leftJoin($u, $u . '.id', 'tbaccounts.userid');

			if ($filters['search'])
			{
				if (is_numeric($filters['search']))
				{
					$query->where('tbaccounts.id', '=', $filters['search']);
				}
				else
				{
					$g = (new \App\Modules\Groups\Models\Group())->getTable();

					$query->leftJoin($g, $g . '.id', 'tbaccounts.groupid')
						->where(function($query) use ($filters, $g, $u)
						{
							$query->where($g . '.name', 'like', '%' . $filters['search'] . '%')
								->orWhere($u . '.name', 'like', '%' . $filters['search'] . '%');
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

			if ($filters['userid'])
			{
				$query->where(function($query) use ($filters)
				{
					$query->where('tbaccounts.userid', '=', $filters['userid'])
						->orWhere('tbaccounts.submitteruserid', '=', $filters['userid'])
						->orWhere('tbaccounts.approveruserid', '=', $filters['userid']);
				});

				unset($filters['userid']);
			}

			$total = $query->count();

		if (app('isAdmin'))
		{
			$rows = $query
				->orderBy($filters['order'], $filters['order_dir'])
				->paginate($filters['limit'], ['*'], 'page', $filters['page'])
				->appends(array_filter($filters));

			$categories = Category::query()
				->where('parentordercategoryid', '>', 0)
				->orderBy('name', 'asc')
				->get();

			$content = view('orders::admin.orders.user', [
				'user' => $user,
				'rows' => $rows,
				'filters' => $filters,
				'categories' => $categories,
			]);

			$total = ' (' . $total . ')';

			$route = route('admin.users.show', ['id' => $user->id, 'section' => 'orders']);
		}
		else
		{
			$total = ' <span class="badge pull-right">' . $total . '</span>';
		}

		$event->addSection(
			$route, //route('site.users.account.section', $r),
			trans('orders::orders.my orders') . $total,
			($event->getActive() == 'orders'),
			$content
		);
	}

	/**
	 * Display data for a user
	 *
	 * @param   UserNotifying  $event
	 * @return  void
	 */
	public function handleUserNotifying(UserNotifying $event)
	{
		$user = $event->user;

		$a = (new Account)->getTable();
		$o = (new Order)->getTable();

		$rows = Account::query()
			->select($a . '.*')
			->join($o, $o . '.id', $a . '.orderid')
			->where($a . '.approveruserid', '=', $user->id)
			->whereNull($a . '.datetimeremoved')
			->whereNull($a . '.datetimeapproved')
			->whereNull($a . '.datetimedenied')
			->whereNull($a . '.datetimepaid')
			->whereNull($o . '.datetimeremoved')
			->orderBy($o . '.datetimecreated', 'asc')
			->orderBy($a . '.id', 'asc')
			->get();

		foreach ($rows as $row)
		{
			$title = trans('orders::orders.orders');

			$account = $row->purchasewbse;
			$account = $account ?: $row->purchaseio;

			$content = '<a href="' . route('site.orders.read', ['id' => $row->orderid]) . '">' . trans('orders::orders.purchase account waiting approval', ['order' => $row->orderid, 'account' => $account]) . '</a>';

			$event->addNotification(new Notification($title, $content));
		}
	}
}
