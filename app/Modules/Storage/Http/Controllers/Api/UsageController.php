<?php

namespace App\Modules\Storage\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\DB;
use App\Modules\Storage\Models\Usage;
use App\Modules\Storage\Models\Directory;
use Carbon\Carbon;

class UsageController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /storage/loans
	 * @apiParameter {
	 * 		"name":          "limit",
	 * 		"description":   "Number of result to return.",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   25
	 * 		}
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
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "asc",
	 * 			"enum": [
	 * 				"asc",
	 * 				"desc"
	 * 			]
	 * 		}
	 * }
	 * @return Response
	 */
	public function index(Request $request)
	{
		$filters = array(
			'storagedirid' => $request->input('storagedirid'),
			// Paging
			'limit'     => $request->input('limit', config('list_limit', 20)),
			// Sorting
			'order'     => $request->input('order', 'datetimestart'),
			'order_dir' => $request->input('order_dir', 'desc')
		);

		// Get records
		$u = (new Usage)->getTable();
		$d = (new Directory)->getTable();

		$rows = DB::select(
			"SELECT resourceid,
				storagedirid,
				quota AS lastquota,
				space AS lastspace,
				lastcheck,
				lastinterval,
				LEAST(1, (SUM(tb1.var) / SUM(tb1.max)) * GREATEST(1, 5 * POW((space / quota) , 28))) AS normalvariability FROM
					(SELECT $u.id,
						$d.resourceid,
						$u.storagedirid,
						$u.quota,
						$u.space,
						$u.lastinterval,
						MAX($u.datetimerecorded) AS lastcheck,
						LEFT($u.datetimerecorded, 10) AS day,
						(((COUNT(DISTINCT $u.space)-1) / COUNT($u.space)) * EXP(-(((UNIX_TIMESTAMP(LEFT(NOW(), 10)) - UNIX_TIMESTAMP(LEFT($u.datetimerecorded, 10)))/86400)+1)*0.25)) as var,
							(EXP(-(((UNIX_TIMESTAMP(LEFT(NOW(), 10)) - UNIX_TIMESTAMP(LEFT($u.datetimerecorded, 10)))/86400)+1)*0.25)) AS max
					FROM $u,
						$d
					WHERE $u.datetimerecorded >= DATE_SUB(NOW(), INTERVAL 10 DAY) AND
						$u.storagedirid <> 0
						AND ($u.quota <> 0 OR $u.space <> 0)
						AND $d.id = $u.storagedirid
					GROUP BY $u.storagedirid,
						day, $u.id
					ORDER BY $u.storagedirid,
						$u.datetimerecorded DESC) AS tb1
			GROUP BY tb1.storagedirid, tb1.quota, tb1.space, tb1.lastcheck, tb1.lastinterval"
		);

		foreach ($rows as $row)
		{
			$lastinterval = 0;

			if ($row->lastinterval == 0)
			{
				$data = Usage::query()
					->where('storagedirid', '=', $row->storagedirid)
					->orderBy('datetimerecorded', 'desc')
					->limit(2)
					->get();

				$lastinterval = 0;

				if (count($data) >= 2)
				{
					$lastinterval = strtotime($data[0]->datetimerecorded) - strtotime($data[1]->datetimerecorded);
				}
			}
			else
			{
				$lastinterval = $row->lastinterval;
			}

			$row->lastinterval = $lastinterval;
		}

		/*$query = Usage::query();

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

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'])
			->appends($filters);*/

		return new ResourceCollection($rows);
	}

	/**
	 * Create an entry
	 *
	 * @apiMethod POST
	 * @apiUri    /storage/usage
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
			'storagedirid' => 'required|integer|min:1',
			'quota' => 'required',
			//'space' => 'nullable|integer',
		]);

		$row = new Usage;
		$row->fill($request->all());

		// Does the storagedir have any bytes yet?
		$last = Usage::query()
			->where('storagedirid', '=', $row->storagedirid)
			->where('resourceid', '=', $row->resourceid)
			->orderBy('datetimerecorded', 'desc')
			->limit(1)
			->get()
			->first();

		if ($last)
		{
			$row->lastinterval = Carbon::now()->timestamp - strtotime($last->datetimerecorded);
		}

		$row->datetimerecorded = Carbon::now()->toDateTimeString();

		$row->save();

		return new JsonResource($row);
	}

	/**
	 * Read an entry
	 *
	 * @apiMethod POST
	 * @apiUri    /storage/usage/{id}
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
	public function read($id)
	{
		$row = Usage::findOrFail($id);

		return new JsonResource($row);
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /storage/usage/{id}
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
			'storagedirid' => 'nullable|integer|min:1',
			'quota' => 'nullable',
			'space' => 'nullable',
		]);

		$row = Usage::findOrFail($id);
		$row->fill($request->all());
		$row->save();

		return new JsonResource($row);
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /storage/usage/{id}
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
	public function delete($id)
	{
		$row = Usage::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
		}

		return response()->json(null, 204);
	}
}
