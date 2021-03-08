<?php

namespace App\Modules\Orders\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Modules\Orders\Models\Account;
use Carbon\Carbon;

/**
 * Order Accounts
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

		$query = Account::query();

		if ($filters['approveruserid'])
		{
			$query->where('approveruserid', '=', $filters['approveruserid']);
		}

		if ($filters['orderid'])
		{
			$query->where('orderid', '=', $filters['orderid']);
		}

		if (!is_null($filters['notice']))
		{
			$query->where('notice', '=', $filters['notice']);
		}

		if ($filters['fund'])
		{
			$query->where(function($where)
			{
				$where->where('purchaseio', 'like', '%' . $filters['fund'] . '%')
					->orWhere('purchasewbse', 'like', '%' . $filters['fund'] . '%');
			});
		}

		if ($filters['doc'])
		{
			$query->where('paymentdocid', 'like', '%' . $filters['doc'] . '%');;
		}

		if ($filters['search'])
		{
			if (is_numeric($filters['search']))
			{
				$query->where('id', '=', $filters['search']);
			}
			else
			{
				$query->where('budgetjustifaction', 'like', '%' . $filters['search'] . '%');
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
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "usernotes",
	 * 		"description":   "Submitter notes.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "description",
	 * 		"description":   "Longer description of a tag",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "namespace",
	 * 		"description":   "Namespace for tag",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
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
			'approveruserid' => 'nullable|integer',
		]);

		if (!$request->has('purchaseio')
		 && !$request->has('purchasewbse'))
		{
			return response()->json(['message' => trans('orders::orders.errors.missing required field')], 412);
		}

		$row = new Account;
		$row->fill($request->all());

		if (!$row->order)
		{
			return response()->json(['message' => trans('orders::orders.errors.invalid orderid')], 415);
		}

		// Select approvers of this order
		$approvers = Account::query()
			->where('orderid', '=', $row->orderid)
			->where(function($where)
			{
				$where->whereNull('datetimeremoved')
					->orWhere('datetimeremoved', '=', '0000-00-00 00:00:00');
			})
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

		// auto approve for orders less than 1000. Should not effect recurring orders.
		if (config('orders.admin_user') && $submitter != config('orders.admin_user'))
		{
			if ($row->amount > 5000 && $row->amount <= 100000)
			{
				$row->approveruserid = $row->order->userid;
				$row->datetimeapproved = Carbon::now()->toDateTimeString();
			}
		}

		$row->save();

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
	 * @return Response
	 */
	public function read($id)
	{
		$row = Account::findOrFail($id);
		//$row->status;

		return new JsonResource($row);
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
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
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "name",
	 * 		"description":   "Tag text",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "slug",
	 * 		"description":   "Normalized text (alpha-numeric, no punctuation)",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "description",
	 * 		"description":   "Longer description of a tag",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "namespace",
	 * 		"description":   "Namespace for tag",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "substitutes",
	 * 		"description":   "Comma-separated list of aliases or alternatives",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @param   Request $request
	 * @return  Response
	 */
	public function update($id, Request $request)
	{
		$request->validate([
			//'orderid' => 'required|integer',
			'amount' => 'nullable|string',
			'purchasefund' => 'nullable|string|max:8',
			'purchasecostcenter' => 'nullable|string|max:10',
			'purchaseorder' => 'nullable|string|max:10',
			'purchaseio' => 'nullable|string|max:10',
			'purchasewbse' => 'nullable|string|max:17',
			'budgetjustification' => 'nullable|string|max:2000',
			'approveruserid' => 'nullable|integer',
			'approved' => 'nullable|integer',
			'paid' => 'nullable|integer',
			'denied' => 'nullable|integer',
			'datetimepaymentdoc' => 'nullable|string',
			'paymentdocid' => 'nullable|string',
			'notice' => 'nullable|integer',
		]);

		$row = Account::findOrFail($id);
		$row->fill($request->all());

		// Select approvers of this order
		$approvers = Account::query()
			->where('orderid', '=', $row->orderid)
			->where(function($where)
			{
				$where->whereNull('datetimeremoved')
					->orWhere('datetimeremoved', '=', '0000-00-00 00:00:00');
			})
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
			$row->notice = 3;
		}

		if ($request->has('approved'))
		{
			if ($request->input('approved'))
			{
				$row->datetimeapproved = Carbon::now()->toDateTimeString();
				$row->notice = 4;
			}
			else
			{
				$row->datetimeapproved = null;
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

		if ($request->input('purchaseio')
		 || $request->input('purchasewbse'))
		{
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

		return new JsonResource($row);
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
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
	 * @return  Response
	 */
	public function destroy($id)
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
