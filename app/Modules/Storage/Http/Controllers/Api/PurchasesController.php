<?php

namespace App\Modules\Storage\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Modules\Storage\Models\Purchase;

/**
 * Purchases
 *
 * @apiUri    /api/storage/purchases
 */
class PurchasesController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /storage/purchases
	 * @apiParameter {
	 * 		"name":          "limit",
	 * 		"description":   "Number of result to return.",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       25
	 * }
	 * @apiParameter {
	 * 		"name":          "page",
	 * 		"description":   "Number of where to start returning results.",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       0
	 * }
	 * @apiParameter {
	 * 		"name":          "search",
	 * 		"description":   "A word or phrase to search for.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       ""
	 * }
	 * @apiParameter {
	 * 		"name":          "sort",
	 * 		"description":   "Field to sort results by.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 *      "default":       "created",
	 * 		"allowedValues": "id, name, datetimecreated, datetimeremoved, parentid"
	 * }
	 * @apiParameter {
	 * 		"name":          "sort_dir",
	 * 		"description":   "Direction to sort results by.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "desc",
	 * 		"allowedValues": "asc, desc"
	 * }
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

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'])
			->appends($filters);

		return new ResourceCollection($rows);
	}

	/**
	 * Create an entry
	 *
	 * @apiMethod POST
	 * @apiUri    /storage/purchases
	 * @apiParameter {
	 *      "name":          "name",
	 *      "description":   "The name of the resource type",
	 *      "type":          "string",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @return Response
	 */
	public function create(Request $request)
	{
		$request->validate([
			'resourceid' => 'required|integer|min:1',
			'groupid' => 'required|integer',
			//'bytes' => 'nullable|integer',
			'sellergroupid' => 'nullable|integer',
			'datetimestart' => 'required|date',
			'datetimestop' => 'nullable|date',
		]);

		$row = new Purchase;
		$row->fill($request->all());

		if (strtotime($row->datetimestart) < Carbon::now()->timestamp - 300)
		{
			return response()->json(['message' => trans('Field `start` cannot be before "now"')], 409);
		}

		if (strtotime($row->datetimestart) >= strtotime($row->datetimestop))
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
				->where('groupid', '=', $row->groupid)
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

			if (strtotime($first->datetimestart) > strtotime($row->datetimestart))
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
				$counter->bytes = ltrim($row->bytes, '-');
			}
			else
			{
				$counter->bytes = '-' . $row->bytes;
			}

			if (!$counter->save())
			{
				return response()->json(['message' => trans('Failed to update `storagedirpurchases` entry for :id', ['id' => $counter->id])], 500);
			}

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

			$counter = new Purchase;
			$counter->fill($data);
			if ($counter->bytes < 0)
			{
				$counter->bytes = ltrim($counter->bytes, '-');
			}
			else
			{
				$counter->bytes = '-' . $counter->bytes;
			}
			$counter->groupid = $row->sellergroupid;
			$counter->sellergroupid = $row->groupid;
			$counter->save();
		}

		return new JsonResource($row);
	}

	/**
	 * Read an entry
	 *
	 * @apiMethod POST
	 * @apiUri    /storage/purchases/{id}
	 * @apiParameter {
	 *      "name":          "id",
	 *      "description":   "The ID of the resource type",
	 *      "type":          "integer",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @return  Response
	 */
	public function read($id)
	{
		$row = Purchase::findOrFail($id);

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

		return new JsonResource($row);
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /storage/purchases/{id}
	 * @apiParameter {
	 *      "name":          "id",
	 *      "description":   "The ID of the resource type",
	 *      "type":          "integer",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @apiParameter {
	 *      "name":          "name",
	 *      "description":   "The name of the resource type",
	 *      "type":          "string",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @return  Response
	 */
	public function update($id, Request $request)
	{
		$request->validate([
			'resourceid' => 'required|integer|min:1',
			'groupid' => 'required|integer',
			//'bytes' => 'nullable|integer',
			'sellergroupid' => 'nullable|integer',
			'datetimestart' => 'required|date',
			'datetimestop' => 'nullable|date',
		]);

		$row = Purchase::findOrFail($id);
		$row->fill($request->all());

		// Sanity checks if we are changing start time
		if ($row->datetimestart != $row->getOriginal('datetimestart'))
		{
			// Make sure the start time isn't the past
			if (strtotime($row->datetimestart) < Carbon::now()->timestamp - 300)
			{
				return response()->json(['message' => trans('Field `start` cannot be before "now"')], 409);
			}

			// Make sure we aren't setting the start time after the start
			/*if (strtotime($row->datetimestart) >= strtotime($row->datetimestop))
			{
				return response()->json(['message' => trans('Field `start` cannot be after `stop`')], 409);
			}*/

			if ($row->datetimestop)
			{
				// Compare to new values
				if (strtotime($row->datetimestop) <= strtotime($row->datetimestart))
				{
					return response()->json(['message' => trans('Field `start` cannot be after `stop`')], 409);
				}
			}
			elseif ($row->getOriginal('datetimestop') && $row->getOriginal('datetimestop') != '0000-00-00 00:00:00')
			{
				// Compare to existing value
				if (strtotime($row->getOriginal('datetimestop')) <= strtotime($row->datetimestart))
				{
					return response()->json(['message' => trans('Field `start` cannot be after `stop`')], 409);
				}
			}
		}

		if ($row->datetimestop != $row->getOriginal('datetimestop'))
		{
			// Make sure we aren't setting the stop time before the start
			if ($row->datetimestart != $row->getOriginal('datetimestart'))
			{
				// Compare to new values
				if (strtotime($row->datetimestop) <= strtotime($row->datetimestart))
				{
					return response()->json(['message' => trans('Field `start` cannot be after `stop`')], 409);
				}
			}
			else
			{
				// Compare to existing value
				if (strtotime($row->datetimestop) <= strtotime($row->getOriginal('datetimestop')))
				{
					return response()->json(['message' => trans('Field `start` cannot be after `stop`')], 409);
				}
			}
		}

		// Sanity checks if we are changing bytes
		if ($request->has('bytes'))
		{
			// Can't change bytes of a entry that has already started
			if (strtotime($row->datetimestart) <= Carbon::now()->timestamp)
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
					->where('groupid', '=', $row->groupid)
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

				if (strtotime($first->datetimestart) > strtotime($row->datetimestart))
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
			$counter = $row->counter;

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
			}

			$counter->save();
		}

		return new JsonResource($row);
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /storage/purchases/{id}
	 * @apiParameter {
	 *      "name":          "id",
	 *      "description":   "The ID of the resource type",
	 *      "type":          "integer",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @return  Response
	 */
	public function delete($id)
	{
		$row = Purchase::findOrFail($id);

		$counter = $row->counter;

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
		}

		if ($row->sellergroupid && $counter)
		{
			$counter->delete();
		}

		return response()->json(null, 204);
	}
}
