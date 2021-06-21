<?php

namespace App\Modules\Storage\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Modules\Storage\Models\Purchase;
use Carbon\Carbon;

/**
 * Purchases
 *
 * @apiUri    /storage/purchases
 */
class PurchasesController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /storage/purchases
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "resourceid",
	 * 		"description":   "A Resource ID to filter by.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "groupid",
	 * 		"description":   "A Group ID to filter by.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "sellergroupid",
	 * 		"description":   "A selling Group ID to filter by.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
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
	 * 		"description":   "Number of result to return.",
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
	 * 			"default":   "datetimestart",
	 * 			"enum": [
	 * 				"id",
	 * 				"resourceid",
	 * 				"groupid",
	 * 				"datetimestart",
	 * 				"datetimestop",
	 * 				"bytes",
	 * 				"sellergroupid"
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"name":          "order_dir",
	 * 		"description":   "Direction to sort results by.",
	 * 		"required":      false,
	 * 		"default":       "desc",
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "asc",
	 * 			"enum": [
	 * 				"asc",
	 * 				"desc"
	 * 			]
	 * 		}
	 * }
	 * @param  Request  $request
	 * @return Response
	 */
	public function index(Request $request)
	{
		$filters = array(
			'resourceid' => $request->input('resourceid'),
			'groupid'    => $request->input('groupid'),
			'sellergroupid' => $request->input('sellergroupid'),
			// Paging
			'limit'     => $request->input('limit', config('list_limit', 20)),
			'page'      => $request->input('page', 1),
			// Sorting
			'order'     => $request->input('order', 'datetimestart'),
			'order_dir' => $request->input('order_dir', 'desc')
		);

		// Get records
		$query = Purchase::query();

		if ($filters['resourceid'])
		{
			$query->where('resourceid', '=', $filters['resourceid']);
		}

		if (!auth()->user()->can('manage storage'))
		{
			$filters['groupid'] = auth()->user()->groups->pluck('id')->toArray();
		}

		if ($filters['groupid'])
		{
			$query->whereIn('groupid', (array)$filters['groupid']);
		}

		if ($filters['sellergroupid'])
		{
			$query->where('sellergroupid', '=', $filters['sellergroupid']);
		}

		if ($filters['search'])
		{
			if (is_numeric($filters['search']))
			{
				$query->where('id', '=', $filters['search']);
			}
			else
			{
				$query->where('comment', 'like', '%' . $filters['search'] . '%');
			}
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page'])
			->appends($filters);

		$rows->each(function($item, $key)
		{
			$item->api = route('api.storage.purchases.read', ['id' => $item->id]);
		});

		return new ResourceCollection($rows);
	}

	/**
	 * Create an entry
	 *
	 * @apiMethod POST
	 * @apiUri    /storage/purchases
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "resourceid",
	 * 		"description":   "Resource ID",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "groupid",
	 * 		"description":   "ID of group making the purchase",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "sellergroupid",
	 * 		"description":   "ID of seller group",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "datetimestart",
	 * 		"description":   "Timestamp for when the purchase starts",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date-time",
	 * 			"example":   "2021-01-30T08:30:00Z"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "datetimestop",
	 * 		"description":   "Timestamp for when the purchase stops",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date-time",
	 * 			"example":   "2021-01-30T08:30:00Z"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "bytes",
	 * 		"description":   "Amount of space in bytes being purchases",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "comment",
	 * 		"description":   "Comments/notes",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 2000
	 * 		}
	 * }
	 * @param  Request  $request
	 * @return Response
	 */
	public function create(Request $request)
	{
		$request->validate([
			'resourceid'    => 'required|integer|min:1',
			'groupid'       => 'required|integer',
			//'bytes'         => 'nullable',
			'sellergroupid' => 'nullable|integer',
			'datetimestart' => 'required|date',
			'datetimestop'  => 'nullable|date',
			'comment'       => 'nullable|string|max:2000'
		]);

		$row = new Purchase;
		$row->resourceid = $request->input('resourceid');
		$row->groupid = $request->input('groupid');
		if ($request->has('sellergroupid'))
		{
			$row->sellergroupid = $request->input('sellergroupid');
		}
		$row->datetimestart = $request->input('datetimestart');
		if ($request->has('datetimestop'))
		{
			$row->datetimestop = $request->input('datetimestop');
			if (!$row->datetimestop)
			{
				unset($row->datetimestop);
			}
		}
		if ($request->has('bytes'))
		{
			$row->bytes = $request->input('bytes');
		}
		if ($request->has('comment'))
		{
			$row->comment = $request->input('comment');
		}
		$row->comment = $row->comment ?: '';
		//$row->fill($request->all());

		if ($row->datetimestart->timestamp < Carbon::now()->timestamp - 300)
		{
			return response()->json(['message' => trans('Field `start` cannot be before "now"')], 409);
		}

		if ($row->datetimestop && $row->datetimestart->timestamp >= $row->datetimestop->timestamp)
		{
			return response()->json(['message' => trans('Field `start` cannot be after `stop`')], 409);
		}

		// Make sure we have enough bytes in the source 
		$seller = 0;
		if ($row->sellergroupid)
		{
			$seller = $row->sellergroupid;
		}
		elseif (!$row->sellergroupid && $row->bytes < 0)
		{
			$seller = $row->groupid;
		}

		if ($seller)
		{
			// Does the storagedir have any bytes yet?
			$first = Purchase::query()
				->where('groupid', '=', $row->sellergroupid)
				->where('resourceid', '=', $row->resourceid)
				->orderBy('datetimestart', 'asc')
				->limit(1)
				->get()
				->first();

			// Haven't been sold anything and never will have anything, so don't bother.
			if (!$first)
			{
				return response()->json(['message' => trans('Have not sold anything and never will have anything')], 409);
			}

			if ($first->datetimestart->timestamp > $row->datetimestart->timestamp)
			{
				// Haven't been sold anything before this would start, so don't bother
				return response()->json(['message' => trans('Have not sold anything before this would start')], 409);
			}

			/*if ($data->min == null
			 || $data->min < abs($row->bytes))
			{
				return response()->json(['message' => trans('Not enough bytes')], 409);
			}*/
		}

		// Look for an existing entry in the same time frame and same storagedirs to update instead
		$first = Purchase::query()
				->where('groupid', '=', $row->groupid)
				->where('resourceid', '=', $row->resourceid)
				->where('sellergroupid', '=', $row->sellergroupid)
				->where('datetimestart', '=', $row->datetimestart)
				->where('datetimestop', '=', $row->datetimestop)
				->get()
				->first();

		if ($first)
		{
			$row->id = $first->id;
			$row->bytes += $first->bytes;
			$row->save();

			$counter = $row->counter;

			if (!$counter)
			{
				return response()->json(['message' => trans('Failed to find `storagedirpurchases` counter entry')], 506);
			}

			// Convert to string to add negative or PHP will lose precision on large values
			if ($row->bytes < 0)
			{
				$counter->bytes = abs($row->bytes);//ltrim($row->bytes, '-');
			}
			else
			{
				$counter->bytes = '-' . $row->bytes;
			}

			if (!$counter->save())
			{
				return response()->json(['message' => trans('Failed to update `storagedirpurchases` entry for :id', ['id' => $counter->id])], 500);
			}

			$row->api = route('api.storage.purchases.read', ['id' => $row->id]);

			return new JsonResource($row);
		}

		$row->save();

		// Enforce proper accounting
		if ($row->sellergroupid)
		{
			// Convert to string to add negative or PHP will lose precision on large values
			//$group = $row->groupid;
			$data = $row->toArray();
			unset($data['id']);
			if (isset($data['group']))
			{
				unset($data['group']);
			}

			$counter = new Purchase;
			$counter->fill($data);
			if ($counter->bytes < 0)
			{
				$counter->bytes = abs($counter->bytes);//ltrim($counter->bytes, '-');
			}
			else
			{
				$counter->bytes = '-' . $counter->bytes;
			}
			$counter->groupid = $row->sellergroupid;
			$counter->sellergroupid = $row->groupid;

			$counter->save();
		}

		$row->api = route('api.storage.purchases.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Read an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /storage/purchases/{id}
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
	 * @param  integer  $id
	 * @return  Response
	 */
	public function read($id)
	{
		$row = Purchase::query()
			->withTrashed()
			->where('id', '=', $id)
			->first();

		if (!$row)
		{
			return response()->json(null, 404);
		}

		if (!auth()->user()->can('manage storage'))
		{
			$groups = auth()->user()->groups->pluck('id')->toArray();

			if (!in_array($row->groupid, $groups))
			{
				return response()->json(null, 404);
			}
		}

		$row->seller;
		$row->counter;

		$row->api = route('api.storage.purchases.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /storage/purchases/{id}
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
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "resourceid",
	 * 		"description":   "Resource ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "groupid",
	 * 		"description":   "ID of group making the purchase",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "sellergroupid",
	 * 		"description":   "ID of seller group",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "datetimestart",
	 * 		"description":   "Timestamp for when the purchase starts",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date-time",
	 * 			"default":   "Now",
	 * 			"example":   "2021-01-30T08:30:00Z"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "datetimestop",
	 * 		"description":   "Timestamp for when the purchase stops",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date-time",
	 * 			"example":   "2021-01-30T08:30:00Z"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "bytes",
	 * 		"description":   "Amount of space in bytes being purchases",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "comment",
	 * 		"description":   "Comments/notes",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 2000
	 * 		}
	 * }
	 * @param   integer  $id
	 * @param   Request  $request
	 * @return  Response
	 */
	public function update($id, Request $request)
	{
		$request->validate([
			'resourceid'    => 'nullable|integer|min:1',
			'groupid'       => 'nullable|integer',
			//'bytes' => 'nullable|integer',
			'sellergroupid' => 'nullable|integer',
			'datetimestart' => 'nullable|date',
			'datetimestop'  => 'nullable|date',
			'comment'       => 'nullable|string'
		]);

		$row = Purchase::query()
			->withTrashed()
			->where('id', '=', $id)
			->first();

		if (!$row)
		{
			return response()->json(null, 404);
		}

		$counter = $row->counter;

		$row->fill($request->all());

		// Sanity checks if we are changing start time
		if ($request->has('datetimestart')
		 && $request->input('datetimestart') != $row->datetimestart->toDateTimeString())
		{
			// Make sure the start time isn't the past
			if ($row->datetimestart->timestamp < Carbon::now()->timestamp - 300)
			{
				return response()->json(['message' => trans('Field `start` cannot be before "now"')], 409);
			}

			// Make sure we aren't setting the start time after the start
			/*if ($row->datetimestart->timestamp >= $row->datetimestop->timestamp)
			{
				return response()->json(['message' => trans('Field `start` cannot be after `stop`')], 409);
			}*/

			if ($row->hasEnd())
			{
				// Compare to new values
				if ($row->datetimestop->timestamp <= $row->datetimestart->timestamp)
				{
					return response()->json(['message' => trans('Field `start` cannot be after `stop`')], 409);
				}
			}
			/*elseif ($row->getOriginal('datetimestop') && $row->getOriginal('datetimestop') != '0000-00-00 00:00:00')
			{
				// Compare to existing value
				if (strtotime($row->getOriginal('datetimestop')) <= $row->datetimestart->timestamp)
				{
					return response()->json(['message' => trans('Field `start` cannot be after `stop`')], 409);
				}
			}*/
		}

		if ($row->datetimestop != $row->getOriginal('datetimestop'))
		{
			// Make sure we aren't setting the stop time before the start
			//if ($row->datetimestart != $row->getOriginal('datetimestart'))
			//{
				// Compare to new values
				if ($row->datetimestop->timestamp <= $row->datetimestart->timestamp)
				{
					return response()->json(['message' => trans('Field `start` cannot be after `stop`')], 409);
				}
			/*}
			else
			{
				// Compare to existing value
				if ($row->datetimestop->timestamp <= strtotime($row->getOriginal('datetimestop')))
				{
					return response()->json(['message' => trans('Field `start` cannot be after `stop`')], 409);
				}
			}*/
		}

		// Sanity checks if we are changing bytes
		if ($request->has('bytes'))
		{
			// Can't change bytes of a entry that has already started
			if ($row->bytes != $row->getOriginal('bytes') && $row->datetimestart->timestamp <= Carbon::now()->timestamp)
			{
				return response()->json(['message' => trans('Cannot change bytes of a entry that has already started')], 409);
			}

			// Don't allow swapping of sale direction or nullation of sale
			if ($row->getOriginal('bytes') > 0 && $row->bytes <= 0)
			{
				return response()->json(['message' => trans('Invalid `bytes` value')], 415);
			}

			if ($row->getOriginal('bytes') < 0 && $row->bytes >= 0)
			{
				return response()->json(['message' => trans('Invalid `bytes` value')], 415);
			}

			if ($row->sellergroupid)
			{
				// Does the storagedir have any bytes yet?
				$first = Purchase::query()
					->where('groupid', '=', $row->sellergroupid)
					->where('resourceid', '=', $row->resourceid)
					->orderBy('datetimestart', 'asc')
					->limit(1)
					->get()
					->first();

				// Haven't been sold anything and never will have anything, so don't bother.
				if (!$first)
				{
					return response()->json(['message' => trans('Have not sold anything and never will have anything')], 409);
				}

				if ($first->datetimestart->timestamp > $row->datetimestart->timestamp)
				{
					// Haven't been sold anything before this would start, so don't bother
					return response()->json(['message' => trans('Have not sold anything before this would start')], 409);
				}

				/*if ($data->min == null
				 || $data->min < abs($row->bytes))
				{
					return response()->json(['message' => trans('Not enough bytes')], 409);
				}*/
			}

			// Are we modifying anything?
			/*if ($row->bytes == $row->getOriginal('bytes'))
			{
				// Don't attempt to change this or the WS will return an error
				unset($copyobj->bytes);
			}*/
		}

		$row->save();

		if ($row->sellergroupid)
		{
			if ($counter && $row->bytes)
			{
				// Convert to string to add negative or PHP will lose precision on large values
				if ($row->bytes < 0)
				{
					$counter->bytes = ltrim($row->bytes, '-');
				}
				else
				{
					$counter->bytes = '-' . $row->bytes;
				}

				if ($row->hasEnd())
				{
					$counter->datetimestop = $row->datetimestop;
				}
				$counter->save();
			}
		}

		$row->api = route('api.storage.purchases.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /storage/purchases/{id}
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
		$row = Purchase::query()
			->withTrashed()
			->where('id', '=', $id)
			->first();

		if ($row)
		{
			$counter = $row->counter;

			if (!$row->delete())
			{
				return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
			}

			if ($row->sellergroupid && $counter)
			{
				$counter->delete();
			}
		}

		return response()->json(null, 204);
	}
}
