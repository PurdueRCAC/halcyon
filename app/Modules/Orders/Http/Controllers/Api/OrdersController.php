<?php

namespace App\Modules\Orders\Http\Controllers\Api;

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
use App\Modules\Orders\Http\Resources\OrderResource;
use App\Modules\Orders\Http\Resources\OrderResourceCollection;

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
	 * 		"in":            "query",
	 * 		"name":          "state",
	 * 		"description":   "Order state.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "active",
	 * 			"enum": [
	 * 				"active [pending_payment, pending_boassignment, pending_collection, pending_approval, pending_fulfillment]",
	 * 				"caceled",
	 * 				"complete"
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "category",
	 * 		"description":   "Orders that have products in the specified category.",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "start",
	 * 		"description":   "Orders created on or after this datetime.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date (YYYY-MM-DD HH:mm:ss)"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "end",
	 * 		"description":   "Orders created before this datetime.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date (YYYY-MM-DD HH:mm:ss)"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "search",
	 * 		"description":   "A word or phrase to search for.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "limit",
	 * 		"description":   "Number of result per page.",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   25
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "page",
	 * 		"description":   "Number of where to start returning results.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   1
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "order",
	 * 		"description":   "Field to sort results by.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "created_at",
	 * 			"enum": [
	 * 				"id",
	 * 				"created_at"
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "order_dir",
	 * 		"description":   "Direction to sort results by.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "asc",
	 * 			"enum": [
	 * 				"asc",
	 * 				"desc"
	 * 			]
	 * 		}
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
					DB::raw("SUM(CASE WHEN (" . $a .".datetimedenied IS NULL) THEN 0 WHEN (" . $a .".datetimedenied = '0000-00-00 00:00:00') THEN 0 WHEN (" . $a .".datetimedenied <> '0000-00-00 00:00:00') THEN 1 END) AS accountsdenied")
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
				->groupBy($o . '.id')
				->groupBy($o . '.userid')
				->groupBy($o . '.datetimecreated')
				->groupBy($o . '.datetimeremoved')
				->groupBy($o . '.usernotes') 
				->groupBy($o . '.staffnotes')
				->groupBy($o . '.notice')
				->groupBy($o . '.submitteruserid')
				->groupBy($o . '.groupid');

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

		return new OrderResourceCollection($rows);
	}

	/**
	 * Create a new entry
	 *
	 * @apiMethod POST
	 * @apiUri    /api/orders
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "userid",
	 * 		"description":   "User ID",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "groupid",
	 * 		"description":   "Group ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "usernotes",
	 * 		"description":   "User notes",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "staffnotes",
	 * 		"description":   "Staff notes",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "notices",
	 * 		"description":   "Notice state",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @param  Request  $request
	 * @return Response
	 */
	public function create(Request $request)
	{
		$request->validate([
			'userid' => 'nullable',
			'groupid' => 'nullable|integer',
			'submitteruserid' => 'nullable|integer',
			'usernotes' => 'nullable|string',
			'staffnotes' => 'nullable|string',
			'notice' => 'nullable|integer',
		]);

		if (!$request->has('items'))
		{
			return response()->json(['message' => 'No items found'], 415);
		}

		$userid = auth()->user() ? auth()->user()->id : 0;

		if ($request->has('userid'))
		{
			$userid = $request->input('userid');
			// Allow passing a username as $userid
			if (!is_numeric($userid))
			{
				$user = User::findByUsername($userid);
				if (!$user)
				{
					return response()->json(['message' => 'Invalid userid'], 415);
				}
				$userid = $user->id;
			}
		}

		$items = (array)$request->input('items', []);
		$orderid = 0;

		// Create record
		$row = new Order;
		$row->userid = (int)$userid;
		$row->groupid = (int)$request->input('groupid', 0);
		$row->submitteruserid = (int)$request->input('submitteruserid', auth()->user() ? auth()->user()->id : 0);
		$row->usernotes = $request->input('usernotes', '');
		$row->staffnotes = $request->input('staffnotes', '');
		$row->notice = (int)$request->input('notice', 1);

		// If we sent an itemsequence we are copying another order. GO and fetch all this
		if ($request->has('orderitemsequence'))
		{
			$sequences = (array)$request->input('orderitemsequence');

			$items = array();
			foreach ($sequences as $sequence)
			{
				// Fetch order information
				$item = Item::query()
					->where('origorderitemid', $sequence)
					->where(function($where)
					{
						$where->whereNull('datetimeremoved')
							->orWhere('datetimeremoved', '=', '0000-00-00 00:00:00');
					})
					->orderBy('datetimecreated', 'desc')
					->limit(1)
					->first();

				if (!$orderitem)
				{
					return response()->json(['message' => 'Failed to find order information for orderitemid #' . $sequence], 404);
				}

				//create a new class.
				$item->id = null;
				$item->datetimecreated = null;

				$items[] = (array)$item;

				$orderid = $item->orderid;
				$row->userid = $item->userid;
				$row->groupid = $item->groupid;
			}

			// Fetch accounts information
			$accounts = Account::query()
				->where('orderid', '=', $orderid)
				->get();
		}

		if ($row->groupid && !$row->group)
		{
			return response()->json(['message' => 'Invalid group ID'], 404);
		}

		$row->save();

		// Create each item in order
		foreach ($items as $i)
		{
			$item = new Item;
			$item->orderid = $row->id;
			$item->orderproductid = $i['product'];
			$item->quantity = $i['quantity'];

			$total = $item->product->unitprice * $item->quantity;

			$item->price = $total;
			if (isset($i['price']))
			{
				$item->price = $i['price'];
			}
			$item->origunitprice = $item->product->unitprice;

			if ($total != $item->price && !auth()->user()->can('manage orders'))
			{
				return response()->json(['message' => 'Total and item price do not match'], 403);
			}

			$item->save();
		}

		// Clear the cart
		$cart = app('cart');
		$cart->forget(auth()->user()->username);

		// ADD FORLOOP ABOVE FOR THE ACCOUNTS: AND TRANSLATE IT
		/*if ($request->has('orderitemsequence')
		 && $request->has('accounts'))
		{
			$accounts = $request->input('accounts');

			$numaccounts = count($accounts);
			$remainder = $numaccounts ? $total % $numaccounts : 0;
			$remainder_check = $remainder;

			foreach ($accounts as $account)
			{
				$amount = ($total - $remainder) / $numaccounts;

				if ($remainder_check != 0)
				{
					$amount = $amount + 1;
					$remainder_check = $remainder_check - 1;
				}

				// set amount to each account
				$account->amount = $amount;
				$account->id = null;

				$account->save();
			}
		}*/

		return new OrderResource($row);
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /api/orders/{id}
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @return Response
	 */
	public function read($id)
	{
		$row = Order::findOrFail($id);

		return new OrderResource($row);
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /api/orders/{id}
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "userid",
	 * 		"description":   "User ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "groupid",
	 * 		"description":   "Group ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "usernotes",
	 * 		"description":   "User notes",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "staffnotes",
	 * 		"description":   "Staff notes",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "notices",
	 * 		"description":   "Notice state",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @param   integer $id
	 * @param   Request $request
	 * @return  Response
	 */
	public function update($id, Request $request)
	{
		$request->validate([
			'userid' => 'nullable|integer',
			'groupid' => 'nullable|integer',
			'submitteruserid' => 'nullable|integer',
			'usernotes' => 'nullable|string|max:2000',
			'staffnotes' => 'nullable|string|max:2000',
			'notice' => 'nullable|integer',
			'accounts' => 'nullable|array',
			'restore' => 'nullable|integer',
		]);

		//$row = Order::findOrFail($id);
		$row = Order::query()
			->withTrashed()
			->where('id', '=', $id)
			->first();

		if (!$row)
		{
			return response()->json(['message' => trans('global.error.not found')], 404);
		}

		$row->userid = $request->input('userid', $row->userid);
		$row->groupid = $request->input('groupid', $row->groupid);
		$row->submitteruserid = $request->input('submitteruserid', $row->submitteruserid);
		$row->usernotes = $request->input('usernotes', $row->usernotes);
		$row->staffnotes = $request->input('staffnotes', $row->staffnotes);
		$row->notice = $request->input('notice', $row->notice);

		if ($row->groupid && !$row->group)
		{
			return response()->json(['message' => 'Invalid group ID'], 404);
		}

		// Ensure client is authorized
		if (auth()->user()->id != $row->userid
		 && auth()->user()->id != $row->submitteruserid
		 && !auth()->user()->can('manage orders'))
		{
			return response()->json(['message' => trans('global.error.not authorized')], 403);
		}

		if ($request->input('restore') && auth()->user()->can('manage orders'))
		{
			// [!] Hackish workaround for resetting date fields
			//     that don't have a `null` default value.
			//     TODO: Change the table schema!
			$db = app('db');
			$db->table($row->getTable())
				->update([
					'datetimeremoved' => '0000-00-00 00:00:00'
				]);

			//$row->datetimeremoved = '0000-00-00 00:00:00';
		}

		// Check if we need to actually do anything
		if ($request->has('userid')
		 || $request->has('groupid')
		 || $request->has('submitteruserid')
		 || $request->has('usernotes')
		 || $request->has('staffnotes')
		 || $request->has('notice'))
		{
			$row->save();
		}

		if ($request->has('accounts'))
		{
			// Create account records
			$accounts = (array)$request->input('accounts');

			foreach ($accounts as $a)
			{
				$account = new Account;
				$account->fill($a);
				$account->orderid = $id;
				$account->save();
			}
		}

		return new OrderResource($row);
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /api/orders/{id}
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @param   integer $id
	 * @return  Response
	 */
	public function delete($id)
	{
		//$row = Order::findOrFail($id);
		// We need to handle it this way to account for differences
		// in datetime fields to how Laravel expects them to be
		$row = Order::query()
			->withTrashed()
			->where('id', '=', $id)
			->first();

		if (!$row)
		{
			return response()->json(['message' => trans('global.error.not found')], 404);
		}

		// Ensure client is authorized
		if (auth()->user()->id != $row->userid
		 && auth()->user()->id != $row->submitteruserid
		 && !auth()->user()->can('manage orders'))
		{
			return response()->json(['message' => trans('global.error.not authorized')], 403);
		}

		if (!$row->isTrashed())
		{
			if (!$row->delete())
			{
				return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
			}
		}

		return response()->json(null, 204);
	}
}
