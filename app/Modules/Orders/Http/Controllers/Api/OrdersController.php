<?php

namespace App\Modules\Orders\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Orders\Models\Order;
use App\Modules\Orders\Models\Category;
use App\Modules\Orders\Models\Product;
use App\Modules\Orders\Models\Item;
use App\Modules\Users\Models\User;

/**
 * Orders
 *
 * @apiUri    /api/orders
 */
class OrdersController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /api/orders
	 * @apiParameter {
	 * 		"name":          "state",
	 * 		"description":   "Order state.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "active"
	 * 		"allowedValues": "active [pending_payment, pending_boassignment, pending_collection, pending_approval, pending_fulfillment], canceled, complete"
	 * }
	 * @apiParameter {
	 * 		"name":          "category",
	 * 		"description":   "Orders that have products int he specified category.",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       0
	 * }
	 * @apiParameter {
	 * 		"name":          "start",
	 * 		"description":   "Orders created on or after this datetime.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       ""
	 * 		"allowedValues": "YYYY-MM-DD HH:mm:ss"
	 * }
	 * @apiParameter {
	 * 		"name":          "end",
	 * 		"description":   "Orders created before this datetime.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       ""
	 * 		"allowedValues": "YYYY-MM-DD HH:mm:ss"
	 * }
	 * @apiParameter {
	 * 		"name":          "search",
	 * 		"description":   "A word or phrase to search for.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       ""
	 * }
	 * @apiParameter {
	 * 		"name":          "limit",
	 * 		"description":   "Number of result per page.",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       25
	 * }
	 * @apiParameter {
	 * 		"name":          "page",
	 * 		"description":   "Number of where to start returning results.",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       1
	 * }
	 * @apiParameter {
	 * 		"name":          "order",
	 * 		"description":   "Field to sort results by.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "name",
	 * 		"allowedValues": "id, created_at"
	 * }
	 * @apiParameter {
	 * 		"name":          "order_dir",
	 * 		"description":   "Direction to sort results by.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "asc",
	 * 		"allowedValues": "asc, desc"
	 * }
	 * @apiResponse {
	 *     "current_page": 1,
	 *     "data": [],
	 *     "first_page_url": "https://yourhost/api/orders?page=1",
	 *     "from": 1,
	 *     "last_page": 2,
	 *     "last_page_url": "https://yourhost/api/orders?page=2",
	 *     "next_page_url": "https://yourhost/api/orders?page=2",
	 *     "path": "https://yourhost/api/widgets",
	 *     "per_page": 3,
	 *     "prev_page_url": null,
	 *     "to": 3,
	 *     "total": 5
	 * }
	 * @apiAuthorization  true
	 * @param  Request $request
	 * @return Response
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = array(
			'search'    => $request->input('search'),
			'state'     => $request->input('state', '*'),
			'category'  => $request->input('category', 0),
			'start'     => null,
			'end'       => null,
			// Paging
			'limit'     => $request->input('limit', config('list_limit', 20)),
			// Sorting
			'order'     => $request->input('order', 'id'),
			'order_dir' => $request->input('order_dir', 'desc'),
		);

		if (!in_array($filters['order'], ['id', 'datetimecreated', 'datetimeremoved']))
		{
			$filters['order'] = 'id';
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = 'desc';
		}

		$order = new Order;

		$query = $order->withTrashed();

		$o = $order->getTable();
		$u = (new User())->getTable();
		$a = (new Account())->getTable();
		$i = (new Item())->getTable();

		$state = "CASE 
					WHEN (tbaccounts.datetimeremoved > '0000-000-00 00:00:00') THEN 7
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
				'tbaccounts.*',
				$u . '.name',
				DB::raw($state . ' AS state')
			])
			->fromSub(function($sub) use ($o, $a, $i, $filters)
			{
				$sub->select(
					$o . '.*',
					DB::raw('SUM(' . $i . '.price) AS ordertotal'),
					DB::raw("COUNT(" . $a . ".id) AS accounts"),
					DB::raw("COUNT(" . $i . ".id) AS items"),
					DB::raw("SUM(CASE WHEN (" . $i . ".datetimefulfilled IS NULL) THEN 0 WHEN (" . $i . ".datetimefulfilled = '0000-00-00 00:00:00') THEN 0 WHEN (" . $i . ".datetimefulfilled <> '0000-00-00 00:00:00') THEN 1 END) AS itemsfulfilled"),
					DB::raw('SUM(CASE WHEN (' . $a .'.approveruserid IS NULL) THEN 0 WHEN (' . $a .'.approveruserid = 0) THEN 0 WHEN (' . $a .'.approveruserid > 0) THEN 1 END) AS accountsassigned'),
					DB::raw('SUM(' . $a .'.amount) AS amountassigned'),
					DB::raw("SUM(CASE WHEN (" . $a .".datetimeapproved IS NULL) THEN 0 WHEN (" . $a .".datetimeapproved = '0000-00-00 00:00:00') THEN 0 WHEN (" . $a .".datetimeapproved <> '0000-00-00 00:00:00') THEN 1 END) AS accountsapproved"),
					DB::raw("SUM(CASE WHEN (" . $a .".datetimepaid IS NULL) THEN 0 WHEN (" . $a .".datetimepaid = '0000-00-00 00:00:00') THEN 0 WHEN (" . $a .".datetimepaid <> '0000-00-00 00:00:00') THEN 1 END) AS accountspaid"),
					DB::raw("SUM(CASE WHEN (" . $a .".datetimedenied IS NULL) THEN 0 WHEN (" . $a .".datetimedenied = '0000-00-00 00:00:00') THEN 0 WHEN (" . $a .".datetimedenied <> '0000-00-00 00:00:00') THEN 1 END) AS accountsdenied"),
				)
				->from($o)
				->leftJoin($a, $a . '.orderid', $o . '.id')
				->leftJoin($i, $i . '.orderid', $o . '.id')
				->where($i . '.datetimeremoved', '=', '0000-00-00 00:00:00')
				->where($i . '.quantity', '>', 0)
				->where(function($where) use ($a)
				{
					$where->where($a . '.datetimeremoved', '=', '0000-00-00 00:00:00')
						->orWhereNull($a . '.datetimeremoved');
				})
				->groupBy($o . '.id');

				if ($filters['start'])
				{
					$sub->where($o . '.datetimecreated', '>=', $filters['start']);
				}

				if ($filters['end'])
				{
					$sub->where($o . '.datetimecreated', '<', $filters['end']);
				}

				if ($filters['category'])
				{
					$p = (new Product())->getTable();

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

		if ($filters['state'] != '*')
		{
			if ($filters['state'] == 'canceled')
			{
				$query->where(DB::raw($state), '=', 7);
			}
			elseif ($filters['state'] == 'complete')
			{
				$query->where(DB::raw($state), '=', 6);
			}
			elseif ($filters['state'] == 'pending_payment')
			{
				$query->where(DB::raw($state), '=', 3);
			}
			elseif ($filters['state'] == 'pending_boassignment')
			{
				$query->where(DB::raw($state), '=', 2);
			}
			elseif ($filters['state'] == 'pending_collection')
			{
				$query->where(DB::raw($state), '=', 5);
			}
			elseif ($filters['state'] == 'pending_approval')
			{
				$query->where(DB::raw($state), '=', 4);
			}
			elseif ($filters['state'] == 'pending_fulfillment')
			{
				$query->where(DB::raw($state), '=', 1);
			}
			elseif ($filters['state'] == 'active')
			{
				$query->where(DB::raw($state), '<', 6);
			}
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'])
			->appends(array_filter($filters));

		return $rows;
	}

	/**
	 * Create a new entry
	 *
	 * @apiMethod POST
	 * @apiUri    /api/orders
	 * @apiParameter {
	 * 		"name":          "usernotes",
	 * 		"description":   "Submitter notes.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       ""
	 * }
	 * @apiParameter {
	 * 		"name":          "description",
	 * 		"description":   "Longer description of a tag",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "namespace",
	 * 		"description":   "Namespace for tag",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @return Response
	 */
	public function create(Request $request)
	{
		$request->validate([
			'name' => 'required'
		]);

		$row = Order::create($request->all());

		return $row;
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /api/orders/{id}
	 * @apiParameter {
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"type":          "integer",
	 * 		"required":      true,
	 * 		"default":       null
	 * }
	 * @return Response
	 */
	public function read($id)
	{
		$row = Order::findOrFail($id);

		$row->state = $row->status;

		$row->accounts;

		$row->items->each(function ($item, $key)
		{
			$item->product;
		});

		return $row;
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /api/orders/{id}
	 * @apiParameter {
	 * 		"name":          "id",
	 * 		"description":   "Tag entry identifier",
	 * 		"type":          "integer",
	 * 		"required":      true,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "name",
	 * 		"description":   "Tag text",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "slug",
	 * 		"description":   "Normalized text (alpha-numeric, no punctuation)",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "description",
	 * 		"description":   "Longer description of a tag",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "namespace",
	 * 		"description":   "Namespace for tag",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "substitutes",
	 * 		"description":   "Comma-separated list of aliases or alternatives",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @param   Request $request
	 * @return  Response
	 */
	public function update(Order $row, Request $request)
	{
		$request->validate([
			'name' => 'required|max:255',
		]);

		$row->update($request->all());

		return $row;
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /api/orders/{id}
	 * @apiParameter {
	 * 		"name":          "id",
	 * 		"description":   "Tag entry identifier",
	 * 		"type":          "integer",
	 * 		"required":      true,
	 * 		"default":       null
	 * }
	 * @return  Response
	 */
	public function destroy(Order $row)
	{
		if (!$row->trashed())
		{
			$row->delete();
		}

		return response()->json();
	}
}
