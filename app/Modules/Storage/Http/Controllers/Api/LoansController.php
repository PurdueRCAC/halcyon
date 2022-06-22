<?php

namespace App\Modules\Storage\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Modules\Storage\Models\Loan;
use Carbon\Carbon;

/**
 * Loans
 *
 * @apiUri    /storage/loans
 */
class LoansController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /storage/loans
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"name":          "limit",
	 * 		"description":   "Number of result to return.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   20
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"name":          "page",
	 * 		"description":   "Number of where to start returning results.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"name":          "search",
	 * 		"description":   "A word or phrase to search for.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"name":          "order",
	 * 		"description":   "Field to sort results by.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "datetimecreated",
	 * 			"enum": [
	 * 				"id",
	 * 				"name",
	 * 				"datetimecreated",
	 * 				"datetimeremoved",
	 * 				"parentid"
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"name":          "order_dir",
	 * 		"description":   "Direction to sort results by.",
	 * 		"type":          "string",
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
	 * @param  Request $request
	 * @return ResourceCollection
	 */
	public function index(Request $request)
	{
		$filters = array(
			'search' => $request->input('search'),
			'resourceid' => $request->input('resourceid'),
			'groupid'    => $request->input('groupid'),
			'lendergroupid' => $request->input('lendergroupid'),
			// Paging
			'limit'     => $request->input('limit', config('list_limit', 20)),
			'page'      => $request->input('page', 1),
			// Sorting
			'order'     => $request->input('order', 'datetimestart'),
			'order_dir' => $request->input('order_dir', 'desc')
		);

		// Get records
		$query = Loan::query()
			->with('resource');

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

		if ($filters['lendergroupid'])
		{
			$query->where('lendergroupid', '=', $filters['lendergroupid']);
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
			if (!$item->hasEnd())
			{
				$item->datetimestop = null;
			}
			$item->api = route('api.storage.loans.read', ['id' => $item->id]);
		});

		return new ResourceCollection($rows);
	}

	/**
	 * Create an entry
	 *
	 * @apiMethod POST
	 * @apiUri    /storage/loans
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
	 * 		"description":   "Group id",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "lendergroupid",
	 * 		"description":   "Lender Group id",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "datetimestart",
	 * 		"description":   "Start date",
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
	 * 		"description":   "Stop date",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date-time",
	 * 			"example":   "2021-01-30T08:30:00Z"
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"201": {
	 * 			"description": "Successful entry creation"
	 * 		},
	 * 		"401": {
	 * 			"description": "Unauthorized"
	 * 		},
	 * 		"409": {
	 * 			"description": "Invalid data"
	 * 		}
	 * }
	 * @param  Request  $request
	 * @return JsonResource
	 */
	public function create(Request $request)
	{
		$rules = [
			'resourceid' => 'required|integer|min:1',
			'groupid' => 'required|integer',
			//'bytes' => 'nullable|integer',
			'lendergroupid' => 'nullable|integer',
			'datetimestart' => 'required|date',
			'datetimestop' => 'nullable|date',
			'comment'       => 'nullable|string|max:2000'
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = new Loan;
		$row->resourceid = $request->input('resourceid');
		$row->groupid = $request->input('groupid');
		if ($request->has('lendergroupid'))
		{
			$row->lendergroupid = $request->input('lendergroupid');
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

		if (!$row->endsAfterStarts())
		{
			return response()->json(['message' => trans('Field `start` cannot be after `stop`')], 409);
		}

		// Make sure we have enough bytes in the source 
		$lender = 0;
		if ($row->lendergroupid)
		{
			$lender = $row->lendergroupid;
		}
		elseif (!$row->lendergroupid && $row->bytes < 0)
		{
			$lender = $row->groupid;
		}

		if ($lender)
		{
			// Does the storagedir have any bytes yet?
			$first = Loan::query()
				->where('groupid', '=', $row->lendergroupid)
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
		$first = Loan::query()
				->where('groupid', '=', $row->groupid)
				->where('resourceid', '=', $row->resourceid)
				->where('lendergroupid', '=', $row->lendergroupid)
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
				return response()->json(['message' => trans('Failed to find `storagedirloans` counter entry')], 506);
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
				return response()->json(['message' => trans('Failed to update `storagedirloans` entry for :id', ['id' => $counter->id])], 500);
			}

			return new JsonResource($row);
		}

		$row->save();

		// Enforce proper accounting
		if ($row->lendergroupid)
		{
			// Convert to string to add negative or PHP will lose precision on large values
			//$group = $row->groupid;
			$data = $row->toArray();
			unset($data['id']);
			if (isset($data['group']))
			{
				unset($data['group']);
			}

			$counter = new Loan;
			$counter->fill($data);

			if ($counter->bytes < 0)
			{
				$counter->bytes = abs($counter->bytes);//ltrim($counter->bytes, '-');
			}
			else
			{
				$counter->bytes = '-' . $counter->bytes;
			}
			$counter->groupid = $row->lendergroupid;
			$counter->lendergroupid = $row->groupid;
			
			$counter->save();
		}

		$row->api = route('api.storage.loans.read', ['id' => $row->id]);
		if (!$row->hasEnd())
		{
			$row->datetimestop = null;
		}

		return new JsonResource($row);
	}

	/**
	 * Read an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /storage/loans/{id}
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
	 * @apiResponse {
	 * 		"200": {
	 * 			"description": "Successful entry read"
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		}
	 * }
	 * @param   integer  $id
	 * @return  JsonResource
	 */
	public function read($id)
	{
		$row = Loan::query()
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

		$row->lender;
		$row->counter;

		$row->api = route('api.storage.loans.read', ['id' => $row->id]);
		if (!$row->hasEnd())
		{
			$row->datetimestop = null;
		}

		return new JsonResource($row);
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /storage/loans/{id}
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
	 * 		"description":   "Group id",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "lendergroupid",
	 * 		"description":   "Lender Group id",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "datetimestart",
	 * 		"description":   "Start date",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date-time",
	 * 			"example":   "2021-01-30T08:30:00Z"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "datetimestop",
	 * 		"description":   "Stop date",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date-time",
	 * 			"example":   "2021-01-30T08:30:00Z"
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"202": {
	 * 			"description": "Successful entry modification"
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		},
	 * 		"409": {
	 * 			"description": "Invalid data"
	 * 		}
	 * }
	 * @param   integer  $id
	 * @param   Request  $request
	 * @return  JsonResource
	 */
	public function update($id, Request $request)
	{
		$rules = [
			'resourceid' => 'nullable|integer|min:1',
			'groupid' => 'nullable|integer',
			//'bytes' => 'nullable|integer',
			'lendergroupid' => 'nullable|integer',
			'datetimestart' => 'nullable|date',
			'datetimestop' => 'nullable|date',
			'comment'       => 'nullable|string',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = Loan::query()
			->withTrashed()
			->where('id', '=', $id)
			->first();

		if (!$row)
		{
			return response()->json(null, 404);
		}

		$counter = $row->counter;

		//$row->fill($request->all());
		if ($request->has('resourceid'))
		{
			$row->resourceid = $request->input('resourceid');
		}
		if ($request->has('groupid'))
		{
			$row->groupid = $request->input('groupid');
		}
		if ($request->has('lendergroupid'))
		{
			$row->lendergroupid = $request->input('lendergroupid');
		}
		if ($request->has('datetimestart'))
		{
			$row->datetimestart = $request->input('datetimestart');
		}
		if ($request->has('datetimestop'))
		{
			$row->datetimestop = $request->input('datetimestop');
		}
		if ($request->has('comment'))
		{
			$row->comment = $request->input('comment');
		}

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
				if (!$row->endsAfterStarts())
				{
					return response()->json(['message' => trans('Field `start` cannot be after `stop`')], 409);
				}
			}
		}

		if ($request->has('datetimestop'))
		{
			// Make sure we aren't setting the stop time before the start
			//if ($row->datetimestart != $row->getOriginal('datetimestart'))
			//{
				// Compare to new values
				if (!$row->endsAfterStarts())
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
			if ($row->bytes != $row->getOriginal('bytes') && $row->hasStarted())
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

			if ($row->lendergroupid)
			{
				// Does the storagedir have any bytes yet?
				$first = Loan::query()
					->where('groupid', '=', $row->lendergroupid)
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

		if ($row->lendergroupid)
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

		$row->api = route('api.storage.loans.read', ['id' => $row->id]);
		if (!$row->hasEnd())
		{
			$row->datetimestop = null;
		}

		return new JsonResource($row);
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /storage/loans/{id}
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
	 * @apiResponse {
	 * 		"204": {
	 * 			"description": "Successful entry deletion"
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		}
	 * }
	 * @param   integer  $id
	 * @return  Response
	 */
	public function delete($id)
	{
		$row = Loan::query()
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

			if ($row->lendergroupid && $counter)
			{
				$counter->delete();
			}
		}

		return response()->json(null, 204);
	}
}
