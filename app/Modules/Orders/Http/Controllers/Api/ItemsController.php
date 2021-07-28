<?php

namespace App\Modules\Orders\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Validator;
use App\Modules\Orders\Models\Order;
use App\Modules\Orders\Models\Product;
use App\Modules\Orders\Models\Item;
use Carbon\Carbon;

/**
 * Order Items
 * 
 * Ordered products, their quantities, etc.
 * 
 * @apiUri    /api/orders/items
 */
class ItemsController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /api/orders/items
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "state",
	 * 		"description":   "Order state.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "datetimecreated",
	 * 			"enum": [
	 * 				"active [pending_payment, pending_boassignment, pending_collection, pending_approval, pending_fulfillment]",
	 * 				"canceled",
	 * 				"complete"
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "category",
	 * 		"description":   "Orders that have products int he specified category.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "start",
	 * 		"description":   "Orders created on or after this datetime.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date-time",
	 * 			"example":   "2021-01-30T08:30:00Z"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "end",
	 * 		"description":   "Orders created before this datetime.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date-time",
	 * 			"example":   "2021-01-30T08:30:00Z"
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
	 * 			"default":   20
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
	 * 			"default":   "datetimecreated",
	 * 			"enum": [
	 * 				"id",
	 * 				"datetimecreated"
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
	 * @param  Request $request
	 * @return Response
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = array(
			'search'    => $request->input('search'),
			'state'     => $request->input('state', '*'),
			'product'   => $request->input('product', 0),
			'user'      => $request->input('user', 0),
			'recurring' => 0,
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

		$o = (new Order)->getTable();
		$i = (new Item())->getTable();
		$p = (new Product)->getTable();

		$query = Item::query()
			->select($i . '.*')
			->join($o, $o . '.id', $i . '.orderid')
			->join($p, $p . '.id', $i . '.orderproductid')
			->withTrashed()
			->where(function($where) use ($i)
				{
					$where->whereNull($i . '.datetimeremoved')
						->orWhere($i . '.datetimeremoved', '=', '0000-00-00 00:00:00');
				})
			->where(function($where) use ($o)
				{
					$where->whereNull($o . '.datetimeremoved')
						->orWhere($o . '.datetimeremoved', '=', '0000-00-00 00:00:00');
				})
			->where(function($where) use ($p)
				{
					$where->whereNull($p . '.datetimeremoved')
						->orWhere($p . '.datetimeremoved', '=', '0000-00-00 00:00:00');
				});

		if ($filters['recurring'])
		{
			$query->where($i . '.origorderitemid', '<>', 0);
		}

		if (!auth()->user()->can('manage orders'))
		{
			$filters['user'] = auth()->user()->id;
		}

		if ($filters['user'])
		{
			$query->where(function($where) use ($o)
				{
					$where->where($o . '.userid', '=', $filters['user'])
						->orWhere($o . '.submitteruserid', '=', $filters['user']);
				});
		}

		if ($filters['product'])
		{
			$query->where($i . '.orderproductid', '=', $filters['product']);
		}

		if ($filters['search'])
		{
			if (is_numeric($filters['search']))
			{
				$query->where($i . '.id', '=', $filters['search']);
			}
			else
			{
				$query->where($p . '.name', 'like', '%' . $filters['search'] . '%');
			}
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
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'])
			->appends(array_filter($filters));

		return new ResourceCollection($rows);
	}

	/**
	 * Create a new entry
	 *
	 * @apiMethod POST
	 * @apiUri    /api/orders/items
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "orderid",
	 * 		"description":   "Order ID",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "orderproductid",
	 * 		"description":   "Order product ID",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "quantity",
	 * 		"description":   "Quantity",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "price",
	 * 		"description":   "Price",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "origunitprice",
	 * 		"description":   "Original unit price",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "origorderitemid",
	 * 		"description":   "Original order item ID (recurring order)",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "timeperiodcount",
	 * 		"description":   "Original order timeperiod count (recurring order)",
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
		//$request->validate([
		$rules = [
			'orderid'         => 'required|integer|min:1',
			'orderproductid'  => 'required|integer|min:1',
			'quantity'        => 'required|integer|min:1',
			'price'           => 'required|integer',
			'origunitprice'   => 'nullable|integer',
			'origorderitemid' => 'nullable|integer',
			'timeperiodcount' => 'nullable|integer',
		]; //]);

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = new Item; //$request->all()
		$row->orderid = $request->input('orderid');
		$row->orderproductid = $request->input('orderproductid');
		$row->quantity = $request->input('quantity');
		$row->price = $request->input('price');
		if ($request->has('origunitprice'))
		{
			$row->origunitprice = $request->input('origunitprice');
		}
		if ($request->has('origorderitemid'))
		{
			$row->origorderitemid = $request->input('origorderitemid');
		}
		if ($request->has('timeperiodcount'))
		{
			$row->timeperiodcount = $request->input('timeperiodcount');
		}

		if (!$row->order)
		{
			return response()->json(['message' => trans('orders::orders.error.invalid order')], 415);
		}

		if (auth()->user()->id != $row->order->userid
		 && auth()->user()->id != $row->order->submitteruserid
		 && !auth()->user()->can('manage orders'))
		{
			return response()->json(['message' => trans('global.error.not authorized')], 403);
		}

		if (!$row->product)
		{
			return response()->json(['message' => trans('orders::orders.error.invalid product')], 415);
		}

		if (!$row->origunitprice)
		{
			$row->origunitprice = $row->product->unitprice;
		}

		$row->save();

		// Set orig item if necessary
		if ($row->product->recurringtimeperiodid > 0 && !$row->origorderitemid)
		{
			$row->origorderitemid = $row->id;
			$row->save();
		}

		$row->recurrence = $row->recurrenceRange();
		$row->api = route('api.orders.items.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /api/orders/items/{id}
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @param  integer  $id
	 * @return Response
	 */
	public function read($id)
	{
		$row = Item::findOrFail($id);
		$row->recurrence = $row->recurrenceRange();

		$row->api = route('api.orders.items.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /api/orders/items/{id}
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "quantity",
	 * 		"description":   "Quantity",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "price",
	 * 		"description":   "Price",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "datetimefulfilled",
	 * 		"description":   "Date time fulfilled",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date/time"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "timeperiodcount",
	 * 		"description":   "Original order timeperiod count (recurring order)",
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
		$rules = [
			'datetimefulfilled' => 'nullable|date',
			'quantity' => 'nullable|integer',
			'price' => 'nullable|integer',
			'timeperiodcount' => 'nullable|integer',
			'fulfilled' => 'nullable|integer',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = Item::findOrFail($id);
		//$row->fill($request->all());

		// Make sure the order still exists
		if (!$row->order)
		{
			return response()->json(['message' => trans('orders::orders.error.invalid order')], 415);
		}

		// Check permissions
		if (auth()->user()->id != $row->order->userid
		 && auth()->user()->id != $row->order->submitteruserid
		 && !auth()->user()->can('manage orders'))
		{
			return response()->json(['message' => trans('global.error.not authorized')], 403);
		}

		if ($request->has('timeperiodcount'))
		{
			$row->timeperiodcount = $request->input('timeperiodcount');
		}

		if ($request->has('quantity'))
		{
			$row->quantity = $request->input('quantity');
		}

		if ($request->has('timeperiodcount') || $request->has('quantity'))
		{
			$row->price = $row->quantity * $row->product->unitprice;
			$row->price = $row->timeperiodcount ? $row->timeperiodcount * $row->price : $row->price;
		}

		// Only admins can edit price
		if ($request->has('price'))
		{
			if (!auth()->user()->can('manage orders'))
			{
				return response()->json(['message' => trans('global.error.not authorized')], 403);
			}

			$row->price = $request->input('price');
		}

		if ($request->input('fulfilled'))
		{
			$row->datetimefulfilled = Carbon::now();
		}

		$row->save();

		$row->recurrence = $row->recurrenceRange();
		$row->api = route('api.orders.items.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /api/orders/items/{id}
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @param   integer  $id
	 * @return  Response
	 */
	public function delete($id)
	{
		$row = Item::findOrFail($id);

		// Make sure the order still exists
		if (!$row->order)
		{
			return response()->json(['message' => trans('orders::orders.error.invalid order')], 415);
		}

		// Check permissions
		if (auth()->user()->id != $row->order->userid
		 && auth()->user()->id != $row->order->submitteruserid
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

	/**
	 * Get recurring item sequence
	 *
	 * @apiMethod DELETE
	 * @apiUri    /api/orders/sequence/{id}
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @param   integer  $id
	 * @return  Response
	 */
	public function sequence($id)
	{
		$row = Item::findOrFail($id);

		$items = $row->recurrenceRange();
		$row->items = $items;

		return new JsonResource($row);

		// Fetch orderitemsequence information from the database.
		/*$i = (new Item)->getTable();
		$o = (new Order)->getTable();

		$rows = Item::query()
			->select($i . '.*', $o . '.userid', $o . '.groupid', $o . '.submitteruserid', $o . '.datetimeremoved AS ordercanceled')
			->join($o, $o . '.id', $i . '.orderid')
			->where($i . '.origorderitemid', '=', $id)
			->withTrashed()
			->whereIsActive()
			->orderBy($i . '.datetimecreated', 'asc')
			->get();

		if (count($rows) <= 0)
		{
			return response()->json(null, 204);
		}

		$paidperiods   = 0;
		$billedperiods = 0;
		$users  = array();
		$groups = array();
		$response = new Fluent();
		$datestart = null;
		//$this->orderitems = array();
		//$this->orderusers = array();
		//$this->ordergroups = array();

		foreach ($rows as $row)
		{
			if ($row->id == $id)
			{
				$datecreated = $row->datetimecreated;
				$datestart   = $row->datetimefulfilled;
			}

			if ($row->isFulfilled())
			{
				$paidperiods += $row->timeperiodcount;
			}

			if (!$row->isTrashed() && (!$row->ordercanceled || $row->ordercanceled == '0000-00-00 00:00:00'))
			{
				$billedperiods += $row->timeperiodcount;
			}

			if (!in_array($row->userid, $users))
			{
				array_push($users, $row->userid);
			}

			if ($row->groupid && !in_array($row->groupid, $groups))
			{
				array_push($groups, $row->groupid);
			}

			if (!in_array($row->submitteruserid, $users))
			{
				array_push($users, $row->submitteruserid);
			}

			array_push($response->items, array(
				'id'              => ROOT_URI . 'orderitem/' . $row['id'],
				'order'           => ROOT_URI . 'order/' . $row['orderid'],
				'created'         => $row['datetimecreated'],
				'ordercanceled'   => $row['ordercanceled'],
				'fulfilled'       => $row['datetimefulfilled'],
				'quantity'        => $row['quantity'],
				'price'           => $row['price'],
				'timeperiodcount' => $row['timeperiodcount'],
			));
		}

		// Get approvers
		$a = (new Account)->getTable();

		$approvers = Account::query()
			->select($a . '.approveruserid')
			->join($i, $i . '.orderid', $a . '.orderid')
			->where($i . '.origorderitemid', '=', $id)
			->withTrashed()
			->whereIsActive()
			->get()
			->pluck('approveruserid')
			->toArray();
		$approvers = array_unique($approvers);

		$response->approvers = $approvers;

		foreach ($approvers as $approver)
		{
			//array_push($this->orderapprovers, ROOT_URI . 'user/' . $row['approveruserid']);
			//array_push($users, $row['approveruserid']);
			if (!in_array($approver, $users))
			{
				array_push($users, $approver);
			}
		}

		// Get details about product
		$sql = "SELECT timeperiods.unixtime, timeperiods.months
			FROM orderproducts, timeperiods
			WHERE orderproducts.id = '" . $this->db->escape_string($data[0]['orderproductid']) . "'
			AND orderproducts.recurringtimeperiodid = timeperiods.id";
		$data = array();
		$rows = $this->db->query($sql, $data);

		if ($rows == 0)
		{
			return 404;
		}

		$recur_months  = $row->product->timeperiod->months; //$data[0]['months'];
		$recur_seconds = $row->product->timeperiod->unixtime; //$data[0]['unixtime'];

		$months_billed  = $billedperiods * $recur_months;
		$seconds_billed = $billedperiods * $recur_seconds;
		$months_paid    = $paidperiods * $recur_months;
		$seconds_paid   = $paidperiods * $recur_seconds;

		if ($datestart && $datestart != '0000-00-00 00:00:00')
		{
			// Calculate billed time
			$datebilleduntil = Carbon::parse($datestart)
				->modify('+' . $months_billed . ' months')
				->modify('+' . $seconds_billed . ' seconds');

			// Calculate billed time
			$sql = "SELECT '" . $this->db->escape_string($this->datestart) . "' + INTERVAL " . $this->db->escape_string($months_paid) . " MONTH + INTERVAL " . $this->db->escape_string($seconds_paid) . " SECOND AS date";
			$data = array();
			$rows = $this->db->query($sql, $data);

			if ($rows == 0)
			{
				$this->addError(__METHOD__ . '(): Failed to calculate paid until date');
				return 500;
			}

			$this->datepaiduntil = $data[0]['date'];

			$start = $this->datestart;

			foreach ($this->orderitems as &$item)
			{
				if ($item['ordercanceled'] == '0000-00-00 00:00:00')
				{
					$item['start'] = $start;

					$sql = "SELECT '" . $this->db->escape_string($start) . "' + INTERVAL " . $this->db->escape_string($recur_months * $item['timeperiodcount']) . " MONTH + INTERVAL " . $this->db->escape_string($recur_seconds * $item['timeperiodcount']) . " SECOND AS date";
					$data = array();
					$rows = $this->db->query($sql, $data);

					if ($rows == 0)
					{
						$this->addError(__METHOD__ . '(): Failed to calculate date');
						return 500;
					}

					$item['end'] = $data[0]['date'];

					$start = $item['end'];
				}
				else
				{
					$item['start'] = '0000-00-00 00:00:00';
					$item['end']   = '0000-00-00 00:00:00';
				}
			}
		}
		else
		{
			$this->datebilleduntil = '0000-00-00 00:00:00';
			$this->datepaiduntil   = '0000-00-00 00:00:00';

			foreach ($this->orderitems as &$item)
			{
				$item['start'] = '0000-00-00 00:00:00';
				$item['end']   = '0000-00-00 00:00:00';
			}
		}

		// Ensure client is authorized
		if (!in_array($this->myuserid, $users) && !$this->globalread)
		{
			return 403;
		}

		return new JsonResource($response);*/
	}
}
