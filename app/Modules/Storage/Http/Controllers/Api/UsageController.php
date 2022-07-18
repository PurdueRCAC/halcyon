<?php

namespace App\Modules\Storage\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Modules\Storage\Http\Resources\UsageResource;
use App\Modules\Storage\Models\Usage;
use App\Modules\Storage\Models\Directory;
use Carbon\Carbon;

/**
 * Usage
 *
 * @apiUri    /storage/usage
 */
class UsageController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /storage/usage
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "storagedirid",
	 * 		"description":   "Storage directory ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @param  Request  $request
	 * @return Response
	 */
	public function index(Request $request)
	{
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
					GROUP BY $d.resourceid, $u.storagedirid, $u.quota, $u.space, $u.lastinterval, $u.datetimerecorded,
						day, $u.id
					ORDER BY $u.storagedirid,
						$u.datetimerecorded DESC) AS tb1
			GROUP BY tb1.resourceid, tb1.storagedirid, tb1.quota, tb1.space, tb1.lastcheck, tb1.lastinterval"
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
			//$row->api = route('api.storage.usage.read', ['id' => $row->id]);

			// [!] Legacy compatibility
			if (request()->segment(1) == 'ws')
			{
				$row->storage = '/ws/storagedir/' . $row->storagedirid;
				$row->resource = '/ws/resource/' . $row->resourceid;
			}
		}

		/*$filters = array(
			'storagedirid' => $request->input('storagedirid'),
			// Paging
			'limit'     => $request->input('limit', config('list_limit', 20)),
			'page'      => $request->input('page', 1),
			// Sorting
			'order'     => $request->input('order', 'datetimestart'),
			'order_dir' => $request->input('order_dir', 'desc')
		);

		$query = Usage::query();

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
			->paginate($filters['limit'], ['*'], 'page', $filters['page'])
			->appends($filters);*/

		return $rows; //new ResourceCollection($rows);
	}

	/**
	 * Create an entry
	 *
	 * @apiMethod POST
	 * @apiUri    /storage/usage
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "storagedirid",
	 * 		"description":   "Storage directory ID",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "quota",
	 * 		"description":   "Quota value",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "space",
	 * 		"description":   "Space value",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "filequota",
	 * 		"description":   "File quota value",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "files",
	 * 		"description":   "Files value",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
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
	 * @return Response
	 */
	public function create(Request $request)
	{
		$rules = [
			'storagedirid' => 'required|integer|min:1',
			'quota'        => 'required|integer',
			'space'        => 'nullable|integer',
			'filequota'    => 'nullable|integer',
			'files'        => 'nullable|integer',
		];
		// [!] Legacy compatibility
		if (request()->segment(1) == 'ws')
		{
			$rules = [
				'storagedir' => 'required|string',
				'quota'      => 'required',
				'space'      => 'nullable',
				'filequota'  => 'nullable|integer',
				'files'      => 'nullable|integer',
			];
		}
		//$request->validate($rules);

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = new Usage;
		$row->quota = 0;
		$row->filequota = 0;
		$row->files = 0;
		$row->space = 0;
	
		if ($request->has('storagedirid') || $request->has('storagedir'))
		{
			$row->storagedirid = $request->input('storagedirid', $request->input('storagedir'));
		}
		if ($request->has('quota'))
		{
			$row->quota = $request->input('quota');
		}
		if ($request->has('space'))
		{
			$row->space = $request->input('space');
		}
		if ($request->has('filequota'))
		{
			$row->filequota = $request->input('filequota');
		}
		if ($request->has('files'))
		{
			$row->files = $request->input('files');
		}

		if (!$row->directory)
		{
			return response()->json(['message' => trans('Invalid storagedirid specified')], 409);
		}

		// Does the storagedir have any bytes yet?
		$last = Usage::query()
			->where('storagedirid', '=', $row->storagedirid)
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

		return new UsageResource($row);
	}

	/**
	 * Read an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /storage/usage/{id}
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
	 * @return  Response
	 */
	public function read($id)
	{
		$row = Usage::findOrFail($id);

		return new UsageResource($row);
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
	 * 		"in":            "body",
	 * 		"name":          "storagedirid",
	 * 		"description":   "Storage directory ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "quota",
	 * 		"description":   "Quota value",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "space",
	 * 		"description":   "Space value",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "filequota",
	 * 		"description":   "File quota value",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "files",
	 * 		"description":   "Files value",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
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
	 * @return  Response
	 */
	public function update($id, Request $request)
	{
		$rules = [
			'storagedirid' => 'nullable|integer|min:1',
			'storagedir'   => 'nullable|string',
			'quota'        => 'nullable|integer',
			'space'        => 'nullable|integer',
			'filequota'    => 'nullable|integer',
			'files'        => 'nullable|integer',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = Usage::findOrFail($id);

		if ($request->has('storagedirid') || $request->has('storagedir'))
		{
			$row->storagedirid = $request->input('storagedirid', $request->input('storagedir'));

			if (!$row->directory)
			{
				return response()->json(['message' => trans('Invalid storagedirid specified')], 409);
			}
		}
		if ($request->has('quota'))
		{
			$row->quota = $request->input('quota');
		}
		if ($request->has('space'))
		{
			$row->space = $request->input('space');
		}
		if ($request->has('filequota'))
		{
			$row->filequota = $request->input('filequota');
		}
		if ($request->has('files'))
		{
			$row->files = $request->input('files');
		}

		$row->save();

		return new UsageResource($row);
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /storage/usage/{id}
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
		$row = Usage::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
		}

		return response()->json(null, 204);
	}
}
