<?php

namespace App\Modules\Orders\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Modules\Orders\Models\Account;
use App\Modules\Orders\Models\Order;
use App\Modules\Users\Models\User;
use Carbon\Carbon;

/**
 * Order Purchase Accounts
 * 
 * @apiUri    /api/orders/accounts
 */
class AccountsController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /api/orders/accounts
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "state",
	 * 		"description":   "Order state.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "active",
	 * 		"allowedValues": "active [pending_payment, pending_boassignment, pending_collection, pending_approval, pending_fulfillment], canceled, complete"
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
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "",
	 * 		"allowedValues": "YYYY-MM-DD HH:mm:ss"
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "end",
	 * 		"description":   "Orders created before this datetime.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "",
	 * 		"allowedValues": "YYYY-MM-DD HH:mm:ss"
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
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "name",
	 * 		"allowedValues": "id, created_at"
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
			'orderid'   => $request->input('orderid', 0),
			'approveruserid' => $request->input('approveruserid', 0),
			'notice' => $request->input('notice'),
			'fund' => $request->input('fund'),
			'doc' => $request->input('doc'),
			// Paging
			'limit'     => $request->input('limit', config('list_limit', 20)),
			// Sorting
			'order'     => $request->input('order', 'datetimecreated'),
			'order_dir' => $request->input('order_dir', 'desc'),
		);

		if (!in_array($filters['order'], ['id', 'datetimecreated', 'datetimeremoved', 'amount', 'datetimeapproved', 'datetimedenied', 'datetimepaid']))
		{
			$filters['order'] = 'datetimecreated';
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = 'desc';
		}

		$a = (new Account)->getTable();
		$o = (new Order)->getTable();

		$query = Account::query()
			->select($a . '.*');

		if (!auth()->user()->can('manage orders'))
		{
			$userid = auth()->user()->id;

			$query->join($o, $o . '.id', $a . '.orderid');
			$query->where(function($where) use ($userid, $a, $o)
			{
				$where->where($a . '.approveruserid', '=', $userid)
					->orWhere($o . '.userid', '=', $userid)
					->orWhere($o . '.submitteruserid', '=', $userid);
			});
		}

		if ($filters['approveruserid'])
		{
			$query->where($a . '.approveruserid', '=', $filters['approveruserid']);
		}

		if ($filters['orderid'])
		{
			$query->where($a . '.orderid', '=', $filters['orderid']);
		}

		if (!is_null($filters['notice']))
		{
			$query->where($a . '.notice', '=', $filters['notice']);
		}

		if ($filters['fund'])
		{
			$query->where(function($where) use ($a, $filters)
			{
				$filters['fund'] = preg_replace('/[^a-zA-Z0-9]+/', '', $filters['fund']);

				$where->where($a . '.purchaseio', 'like', '%' . $filters['fund'] . '%')
					->orWhere($a . '.purchasewbse', 'like', '%' . $filters['fund'] . '%');
			});
		}

		if ($filters['doc'])
		{
			$query->where($a . '.paymentdocid', 'like', '%' . $filters['doc'] . '%');;
		}

		if ($filters['search'])
		{
			if (is_numeric($filters['search']))
			{
				$query->where($a . '.id', '=', $filters['search']);
			}
			else
			{
				$query->where($a . '.budgetjustifaction', 'like', '%' . $filters['search'] . '%');
			}
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
	 * @apiUri    /api/orders/accounts
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
	 * 		"name":          "amount",
	 * 		"description":   "Amount, formatted or not",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"example":   "2,400.00"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "purchasefund",
	 * 		"description":   "Purchase fund",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 8
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "purchasecostcenter",
	 * 		"description":   "Purchase cost center",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 10
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "purchaseorder",
	 * 		"description":   "Purchase order",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 10
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "purchaseio",
	 * 		"description":   "Purchase IO",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 10
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "purchasewbse",
	 * 		"description":   "Purchase WBSE",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 17
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "budgetjustification",
	 * 		"description":   "Budget justification",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 2000
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "approveruserid",
	 * 		"description":   "Approver user ID",
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
			'orderid' => 'required|integer',
			'amount' => 'required|string',
			'purchasefund' => 'nullable|string|max:8',
			'purchasecostcenter' => 'nullable|string|max:10',
			'purchaseorder' => 'nullable|string|max:10',
			'purchaseio' => 'nullable|string|max:10',
			'purchasewbse' => 'nullable|string|max:17',
			'budgetjustification' => 'nullable|string|max:2000',
			'approveruserid' => 'nullable',
		]);

		if (!$request->has('purchaseio')
		 && !$request->has('purchasewbse'))
		{
			return response()->json(['message' => trans('orders::orders.errors.missing required field')], 412);
		}

		$data = $request->all();

		if ($request->has('approveruserid') && !is_numeric($data['approveruserid']))
		{
			$user = User::createFromUsername($data['approveruserid']);

			if ($user && $user->id)
			{
				$data['approveruserid'] = $user->id;
			}
			else
			{
				unset($data['approveruserid']);
			}
		}

		$row = new Account;
		$row->fill($data);
		$row->budgetjustification = $row->budgetjustification ?: '';

		if (!$row->order)
		{
			return response()->json(['message' => trans('orders::orders.errors.invalid orderid')], 415);
		}

		// Select approvers of this order
		$approvers = Account::query()
			->where('orderid', '=', $row->orderid)
			->withTrashed()
			->whereIsActive()
			->get()
			->pluck('approveruserid')
			->toArray();

		// Ensure the client is authenticated
		if (!in_array(auth()->user()->id, $approvers)
		 && auth()->user()->id != $row->order->userid
		 && auth()->user()->id != $row->order->submitteruserid
		 && !auth()->user()->can('manage orders'))
		{
			return response()->json(['message' => trans('global.not authorized')], 403);
		}

		// If you are an approver already and are adding another account, set the approve to yourself
		if (in_array(auth()->user()->id, $approvers))
		{
			$row->approveruserid = auth()->user()->id;
		}

		if ($row->approveruserid)
		{
			if (!$row->approver)
			{
				return response()->json(['message' => trans('orders::orders.errors.invalid approverid')], 415);
			}

			$row->notice = 3;
		}

		// auto approve for orders less than 1000. Should not effect recurring orders.
		if (config('module.orders.admin_user') && auth()->user()->id != config('module.orders.admin_user'))
		{
			if ($row->amount > 5000 && $row->amount <= 100000)
			{
				$row->approveruserid = $row->order->userid;
				$row->datetimeapproved = Carbon::now()->toDateTimeString();
			}
		}

		$row->save();
		$row->api = route('api.orders.accounts.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /api/orders/accounts/{id}
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
		$row = Account::findOrFail($id);
		$row->api = route('api.orders.accounts.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /api/orders/accounts/{id}
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "amount",
	 * 		"description":   "Amount, formatted or not",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"example":   "2,400.00 or 240000"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "purchasefund",
	 * 		"description":   "Purchase fund",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 8
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "purchasecostcenter",
	 * 		"description":   "Purchase cost center",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 10
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "purchaseorder",
	 * 		"description":   "Purchase order",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 10
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "purchaseio",
	 * 		"description":   "Purchase IO",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 10
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "purchasewbse",
	 * 		"description":   "Purchase WBSE",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 17
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "budgetjustification",
	 * 		"description":   "Budget justification",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 2000
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "approveruserid",
	 * 		"description":   "Approver user ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "approved",
	 * 		"description":   "Has the payment been approved?",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "boolean"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "paid",
	 * 		"description":   "Has the payment been paid?",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "boolean"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "denied",
	 * 		"description":   "Has the payment been denied?",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "boolean"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "reset",
	 * 		"description":   "Reset paid/denied status?",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "boolean"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "datetimepaymentdoc",
	 * 		"description":   "Timestamp for the payment doc",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date-time",
	 * 			"example":   "2021-01-30T08:30:00Z"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "paymentdocid",
	 * 		"description":   "Payment doc ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "boolean"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "notice",
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
			// Fields
			'amount' => 'nullable|string',
			'purchasefund' => 'nullable|string|max:8',
			'purchasecostcenter' => 'nullable|string|max:10',
			'purchaseorder' => 'nullable|string|max:10',
			'purchaseio' => 'nullable|string|max:10',
			'purchasewbse' => 'nullable|string|max:17',
			'budgetjustification' => 'nullable|string|max:2000',
			'approveruserid' => 'nullable',
			'datetimepaymentdoc' => 'nullable|date',
			'paymentdocid' => 'nullable|string',
			'notice' => 'nullable|integer',
			// Actions
			'approved' => 'nullable|integer',
			'paid' => 'nullable|integer',
			'denied' => 'nullable|integer',
			'reset' => 'nullable|integer',
		]);

		$row = Account::findOrFail($id);
		if ($request->has('purchasefund'))
		{
			$row->purchasefund = $request->input('purchasefund');
		}
		if ($request->has('purchasecostcenter'))
		{
			$row->purchasecostcenter = $request->input('purchasecostcenter');
		}
		if ($request->has('purchaseorder'))
		{
			$row->purchaseorder = $request->input('purchaseorder');
		}
		if ($request->has('budgetjustification'))
		{
			$row->budgetjustification = $request->input('budgetjustification');
		}
		if ($request->has('approveruserid'))
		{
			$approveruserid = $request->input('approveruserid');

			if (!is_numeric($approveruserid))
			{
				$user = User::createFromUsername($approveruserid);

				if ($user && $user->id)
				{
					$approveruserid = $user->id;
				}
			}

			$row->approveruserid = $approveruserid;
		}
		if ($request->has('datetimepaymentdoc'))
		{
			$row->datetimepaymentdoc = $request->input('datetimepaymentdoc');
		}
		if ($request->has('paymentdocid'))
		{
			$row->paymentdocid = $request->input('paymentdocid');
		}
		if ($request->has('notice'))
		{
			$row->notice = $request->input('notice');
		}
		//$row->fill($request->all());

		// Select approvers of this order
		$approvers = Account::query()
			->where('orderid', '=', $row->orderid)
			->withTrashed()
			->whereIsActive()
			->get()
			->pluck('approveruserid')
			->toArray();

		// Ensure the client is authenticated
		if (auth()->user()->id != $row->approveruserid
		 && auth()->user()->id != $row->order->userid
		 && auth()->user()->id != $row->order->submitteruserid
		 && !auth()->user()->can('manage orders'))
		{
			return response()->json(['message' => trans('global.not authorized')], 403);
		}

		if ($request->has('amount'))
		{
			$row->amount = $request->input('amount');

			// auto approve for orders less than 1000. Should not effect recurring orders.
			if (config('orders.admin_user') && $submitter != config('orders.admin_user'))
			{
				if ($row->amount > 5000 && $row->amount <= 100000)
				{
					$row->approveruserid = $row->order->userid;
					$row->datetimeapproved = Carbon::now()->toDateTimeString();
				}
			}
		}

		if ($request->has('approveruserid'))
		{
			$row->approveruserid = $request->input('approveruserid');
			$row->notice = 3;
		}

		if ($request->has('approved'))
		{
			if ($request->input('approved'))
			{
				$row->datetimeapproved = Carbon::now()->toDateTimeString();
				$row->notice = 4;
			}
		}

		if ($request->input('paid'))
		{
			$row->datetimepaid = Carbon::now()->toDateTimeString();

			if (!$request->input('paymentdocid')
			 || !$request->input('datetimepaymentdoc'))
			{
				return response()->json(['message' => trans('orders::orders.errors.missing required field')], 412);
			}
		}

		if ($request->input('denied'))
		{
			$row->datetimedenied = Carbon::now()->toDateTimeString();
			$row->notice = 5;
		}

		if ($request->input('reset') && auth()->user()->can('manage orders'))
		{
			// [!] Hackish workaround for resetting date fields
			//     that don't have a `null` default value.
			//     TODO: Change the table schema!
			/*$db = app('db');
			$db->table($row->getTable())
				->where('id', '=', $id)
				->update([
					'datetimepaid' => '0000-00-00 00:00:00',
					'datetimeapproved' => '0000-00-00 00:00:00',
					'datetimedenied' => '0000-00-00 00:00:00'
				]);

			DB::statement(DB::raw());*/

			$row->forceRestore(['datetimepaid', 'datetimeapproved', 'datetimedenied']);

			if ($row->approveruserid)
			{
				$row->notice = 3;
			}
			else
			{
				$row->notice = 2;
			}
		}

		if ($request->has('purchaseio')
		 || $request->has('purchasewbse'))
		{
			$row->purchaseio = $request->input('purchaseio');
			$row->purchasewbse = $request->input('purchasewbse');

			if ($row->purchaseio)
			{
				$row->purchasewbse = 0;
			}
			elseif ($row->purchasewbse)
			{
				$row->purchaseio = 0;
			}
		}

		$row->save();
		$row->api = route('api.orders.accounts.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /api/orders/accounts/{id}
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
		$row = Account::findOrFail($id);

		// Ensure the client is authenticated
		if (auth()->user()->id != $row->approveruserid
		 && auth()->user()->id != $row->order->userid
		 && auth()->user()->id != $row->order->submitteruserid
		 && !auth()->user()->can('manage orders'))
		{
			return response()->json(['message' => trans('global.not authorized')], 403);
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
