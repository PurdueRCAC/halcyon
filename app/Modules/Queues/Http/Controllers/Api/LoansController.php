<?php

namespace App\Modules\Queues\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Queues\Models\Loan;
use App\Modules\Queues\Models\Size;
use Carbon\Carbon;

/**
 * Queue Loans
 *
 * @apiUri    /queues/loans
 */
class LoansController extends Controller
{
	/**
	 * Display a listing of loans for a queue
	 *
	 * @apiMethod GET
	 * @apiUri    /queues/loans
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "queueid",
	 * 		"description":   "ID of the queue the loan whent to",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "lenderqueueid",
	 * 		"description":   "ID of the lender queue",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "state",
	 * 		"description":   "Sate of the loan.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"enum": [
	 * 				"ended",
	 * 				"ongoing"
	 * 			]
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
	 * 				"queueid",
	 * 				"lenderqueueid",
	 * 				"datetimestart",
	 * 				"datetimestop"
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
	 * @return Response
	 */
	public function index(Request $request)
	{
		$filters = array(
			//'search'   => $request->input('search'),
			'state'    => $request->input('state'),
			'queueid'   => $request->input('queueid', 0),
			'lenderqueueid' => $request->input('lenderqueueid', 0),
			// Paging
			'limit'    => $request->input('limit', config('list_limit', 20)),
			'page'     => $request->input('page', 1),
			// Sorting
			'order'     => $request->input('order', 'datetimestart'),
			'order_dir' => $request->input('order_dir', 'desc')
		);

		if (!in_array($filters['order'], ['id', 'queueid', 'lenderqueueid', 'datetimestart', 'datetimestop']))
		{
			$filters['order'] = 'datetimestart';
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = 'desc';
		}

		// Build query
		$query = Loan::query();

		if ($filters['state'] == 'ended')
		{
			$query->whereNotNull('datetimestop');
		}
		elseif ($filters['state'] == 'ongoing')
		{
			$query->whereNull('datetimestop');
		}

		if ($filters['queueid'] > 0)
		{
			$query->where('queueid', '=', (int)$filters['queueid']);
		}

		if ($filters['lenderqueueid'])
		{
			$query->where('lenderqueueid', '=', (int)$filters['lenderqueueid']);
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page'])
			->appends(array_filter($filters));

		$rows->each(function($item, $key)
		{
			if (!$item->hasEnd())
			{
				$item->datetimestop = null;
			}
			$item->api = route('api.queues.loans.read', ['id' => $item->id]);
		});

		return new ResourceCollection($rows);
	}

	/**
	 * Create a queue loan
	 *
	 * @apiMethod POST
	 * @apiUri    /queues/loans
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "queueid",
	 * 		"description":   "ID of the queue being loaned to",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "lenderqueueid",
	 * 		"description":   "ID of the queue being loaned",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "datetimestart",
	 * 		"description":   "Date/time of when the loan starts",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date-time",
	 * 			"example":   "2021-01-30T09:30:00Z"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "datetimestop",
	 * 		"description":   "Date/time of when the loan stops",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date-time",
	 * 			"example":   "2021-01-30T09:30:00Z"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "nodecount",
	 * 		"description":   "Node count",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "corecount",
	 * 		"description":   "Core count",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "serviceunits",
	 * 		"description":   "Service units",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "float"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "comment",
	 * 		"description":   "Comments on this loan",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 2000
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"201": {
	 * 			"description": "Successful entry creation"
	 * 		},
	 * 		"409": {
	 * 			"description": "Invalid data"
	 * 		}
	 * }
	 * @param  Request $request
	 * @return Response
	 */
	public function create(Request $request)
	{
		$rules = [
			'queueid'       => 'required|integer',
			'lenderqueueid' => 'required|integer',
			'datetimestart' => 'nullable|date',
			'datetimestop'  => 'nullable|date',
			'nodecount'     => 'nullable|numeric',
			'corecount'     => 'nullable|integer',
			'comment'       => 'nullable|string|max:2000',
			'serviceunits'  => 'nullable|numeric|between:-999999999.99,999999999.99',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = new Loan;
		$row->queueid = $request->input('queueid');
		$row->lenderqueueid = $request->input('lenderqueueid');
		$row->datetimestart = $request->input('datetimestart');
		if (!$row->datetimestart)
		{
			$row->datetimestart = Carbon::now()->toDateTimeFormat();
		}
		if ($request->has('datetimestop'))
		{
			$row->datetimestop = $request->input('datetimestop');
			if (!$row->datetimestop)
			{
				unset($row->datetimestop);
			}
		}
		if ($request->has('nodecount'))
		{
			$row->nodecount = $request->input('nodecount');
		}
		if ($request->has('corecount'))
		{
			$row->corecount = $request->input('corecount');
		}
		if ($request->has('comment'))
		{
			$row->comment = $request->input('comment');
		}
		$row->comment = $row->comment ?: '';
		if ($request->has('serviceunits'))
		{
			$row->serviceunits = (float)$request->input('serviceunits');
		}

		if (!$row->endsAfterStarts())
		{
			return response()->json(['message' => trans('queues::queues.error.start cannot be after end')], 409);
		}

		if (!$row->queue)
		{
			return response()->json(['message' => trans('queues::queues.invalid queue id')], 415);
		}

		if (!$row->source)
		{
			return response()->json(['message' => trans('queues::queues.invalid lender queue id')], 415);
		}

		if ($row->lender && ($row->nodecount || $row->corecount))
		{
			// Does the queue have any cores yet?
			$count = Size::query()
				->where('queueid', '=', (int)$row->lenderqueueid)
				->orderBy('datetimestart', 'asc')
				->get()
				->first();

			if (!$count)
			{
				$count = Loan::query()
					->where('queueid', '=', (int)$row->lenderqueueid)
					->orderBy('datetimestart', 'asc')
					->get()
					->first();
			}

			if (!$count)
			{
				return response()->json(['message' => trans('queues::queues.error.queue is empty')], 409);
			}
			elseif ($count->datetimestart > $row->datetimestart)
			{
				return response()->json(['message' => trans('queues::queues.queue has not started')], 409);
			}
		}

		// Look for an existing entry in the same time frame and same queues to update instead
		$cquery = Loan::query()
			->where('queueid', '=', (int)$row->queueid)
			->where('lenderqueueid', '=', $row->lenderqueueid)
			->where('datetimestart', '=', $row->datetimestart);

		if ($row->hasEnd())
		{
			$cquery->where('datetimestop', '=', $row->datetimestop);
		}
		else
		{
			$cquery->whereNull('datetimestop');
		}

		$exist = $cquery
			->orderBy('datetimestart', 'asc')
			->get()
			->first();

		if ($exist)
		{
			$exist->nodecount = $row->nodecount;
			$exist->corecount = $row->corecount;

			if (!$exist->save())
			{
				return response()->json(['message' => trans('global.messages.create failed')], 500);
			}

			// Find counter entry to update as well
			$cquery = Loan::query()
				->where('queueid', '=', $row->lenderqueueid)
				->where('lenderqueueid', '=', (int)$row->queueid)
				->where('datetimestart', '=', $row->datetimestart);

			if ($row->hasEnd())
			{
				$cquery->where('datetimestop', '=', $row->datetimestop);
			}
			else
			{
				$cquery->whereNull('datetimestop');
			}

			$counter = $cquery
				->orderBy('datetimestart', 'asc')
				->get()
				->first();

			if ($counter)
			{
				$counter->corecount = -$exist->corecount;

				if (!$counter->save())
				{
					return response()->json(['message' => trans('queues::queues.error.failed to update counter', ['id' => $exist->id])], 500);
				}
			}
			/*else
			{
				return response()->json(['message' => trans('queues::queues.error.failed to find counter')], 506);
			}*/

			return new JsonResource($exist);
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.create failed')], 500);
		}

		// Enforce proper accounting
		$counter = new Loan;
		$counter->queueid = $row->lenderqueueid;
		$counter->lenderqueueid = $row->queueid;
		$counter->datetimestart = $row->datetimestart;
		if ($row->hasEnd())
		{
			$counter->datetimestop = $row->datetimestop;
		}
		$counter->nodecount = $row->nodecount;
		$counter->corecount = -$row->corecount;

		if (!$counter->save())
		{
			return response()->json(['message' => trans('global.messages.create failed')], 500);
		}

		$row->api = route('api.queues.loans.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Read a queue loan
	 *
	 * @apiMethod GET
	 * @apiUri    /queues/loans/{id}
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
		$row = Loan::findOrFail($id);

		$row->api = route('api.queues.loans.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Update a queue loan
	 *
	 * @apiMethod PUT
	 * @apiUri    /queues/loans/{id}
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
	 * 		"name":          "datetimestart",
	 * 		"description":   "Date/time of when the loan starts",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date-time",
	 * 			"example":   "2021-01-30T09:30:00Z"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "datetimestop",
	 * 		"description":   "Date/time of when the loan stops",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date-time",
	 * 			"example":   "2021-01-30T09:30:00Z"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "nodecount",
	 * 		"description":   "Node count",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "corecount",
	 * 		"description":   "Core count",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "serviceunits",
	 * 		"description":   "Service units",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "float"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "comment",
	 * 		"description":   "Comments on this loan",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 2000
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"204": {
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
			'datetimestart' => 'nullable|date',
			'datetimestop'  => 'nullable|date',
			'nodecount'     => 'nullable|numeric',
			'corecount'     => 'nullable|integer',
			'comment'       => 'nullable|string|max:2000',
			'serviceunits'  => 'nullable|numeric|between:-999999999.99,999999999.99',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = Loan::findOrFail($id);

		// Find counter entry to update as well
		$updatecounter = false;

		$cquery = Loan::query()
			->where('queueid', '=', $row->lenderqueueid)
			->where('lenderqueueid', '=', (int)$row->queueid)
			->where('datetimestart', '=', $row->datetimestart);

		if ($row->hasEnd())
		{
			$cquery->where('datetimestop', '=', $row->datetimestop);
		}
		else
		{
			$cquery->whereNull('datetimestop');
		}

		$counter = $cquery
			->orderBy('datetimestart', 'asc')
			->get()
			->first();

		if ($request->has('datetimestart'))
		{
			$row->datetimestart = $request->input('datetimestart');
		}

		if ($request->has('datetimestop') && $request->input('datetimestop'))
		{
			$row->datetimestop = $request->input('datetimestop');
			$updatecounter = true;
		}

		if (!$row->endsAfterStarts())
		{
			return response()->json(['message' => trans('queues::queues.error.start cannot be after stop')], 409);
		}

		// Sanity checks if we are changing coreecount
		$cores = $request->input('corecount');

		if ($request->has('corecount') && $cores != $row->corecount)
		{
			$updatecounter = true;

			// Can't change corecount of a entry that has already started
			if ($row->hasStarted() && $cores != $row->corecount)
			{
				//return response()->json(['message' => trans('queues::queues.error.corecount cannot be modified')], 409);
			}

			// Don't allow swapping of sale direction or nullation of sale
			if ($row->corecount > 0 && $cores <= 0)
			{
				return response()->json(['message' => trans('queues::queues.error.invalid corecount')], 415);
			}

			if ($row->corecount < 0 && $cores >= 0)
			{
				return response()->json(['message' => trans('queues::queues.error.invalid corecount')], 415);
			}

			$row->corecount = $cores;

			// if we are adjusting the source of the loan, the lender is itself. make sure we check core counts agains the source
			if ($cores < 0)
			{
				$lenderid = $row->queueid;
			}
			else
			{
				$lenderid = $row->lenderqueueid;
			}

			// Does the queue have any cores yet?
			$count = Size::query()
				->where('queueid', '=', (int)$lenderid)
				->orderBy('datetimestart', 'asc')
				->get()
				->first();

			if (!$count)
			{
				return response()->json(['message' => trans('queues::queues.error.queue is empty')], 409);
			}
			elseif ($count->datetimestart > $row->datetimestart)
			{
				return response()->json(['message' => trans('queues::queues.error.queue has not started')], 409);
			}

			// Make sure we have enough cores in the source 
			/*$sql = "SET @tot:=0, @tot2:=0";
			$this->db->execute($sql, RCACDB_CACHE);

			$stop_sql = "";
			if (isset($copyobj->stop) && $copyobj->stop)
			{
				$stop_sql = " AND date < '" . $this->db->escape_string($copyobj->stop) . "'";
			}

			$start_sql = "";
			if (!isset($copyobj->start))
			{
				$start_sql = $start;
			}
			else
			{
				$start_sql = $copyobj->start;
			}

			$sql = "SELECT MIN(tot) AS min, date FROM (
				(SELECT * FROM (
					SELECT id, corecount, date, (@tot:=@tot + tb1.corecount) AS tot FROM (
						SELECT id, corecount, datetimestart AS date FROM queuesizes WHERE queueid = '" . $this->db->escape_string($lenderid) . "'
						UNION SELECT id, -corecount, datetimestop AS date FROM queuesizes WHERE queueid = '" . $this->db->escape_string($lenderid) . "'
						UNION SELECT id, corecount, datetimestart AS date FROM queueloans WHERE corecount < 0 AND queueid = '" . $this->db->escape_string($lenderid) . "'
						UNION SELECT id, -corecount, datetimestop AS date FROM queueloans WHERE corecount < 0 AND queueid = '" . $this->db->escape_string($lenderid) . "'
					) AS tb1 WHERE date <> '0000-00-00 00:00:00' ORDER BY date
				) AS tb2 WHERE date >= '" . $this->db->escape_string($start_sql) . "'" . $stop_sql . ")
				UNION
				(SELECT * FROM (
					SELECT id, corecount, date, (@tot2:=@tot2 + tb3.corecount) AS tot FROM (
						SELECT id, corecount, datetimestart AS date FROM queuesizes WHERE queueid = '" . $this->db->escape_string($lenderid) . "'
						UNION SELECT id, -corecount, datetimestop AS date FROM queuesizes WHERE queueid = '" . $this->db->escape_string($lenderid) . "'
						UNION SELECT id, corecount, datetimestart AS date FROM queueloans WHERE corecount < 0 AND queueid = '" . $this->db->escape_string($lenderid) . "'
						UNION SELECT id, -corecount, datetimestop AS date FROM queueloans WHERE corecount < 0 AND queueid = '" . $this->db->escape_string($lenderid) . "'
					) AS tb3 WHERE date <> '0000-00-00 00:00:00' AND date < '" . $this->db->escape_string($start_sql) . "' ORDER BY date
				) AS tb4 ORDER BY date DESC LIMIT 1)
			) AS tb5;";

			$data = array();
			$rows = $this->db->query($sql, $data, RCACDB_CACHE);

			//if ($data[0]['min'] == null || $data[0]['min'] < $row->corecount)
			//{
				//return 409;
			//}*/
		}

		if ($request->has('comment'))
		{
			$row->comment = $request->input('comment');
		}
		$row->comment = $row->comment ?: '';

		if ($request->has('serviceunits'))
		{
			$row->serviceunits = (float)$request->input('serviceunits');
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.create failed')], 500);
		}

		if ($updatecounter)
		{
			if ($counter)
			{
				$counter->corecount = -$row->corecount;
				$counter->datetimestop = $row->datetimestop;

				if (!$counter->save())
				{
					return response()->json(['message' => trans('queues::queues.error.failed to update counter', ['id' => $counter->id])], 500);
				}
			}
			/*else
			{
				return response()->json(['message' => trans('queues::queues.error.failed to find counter')], 506);
			}*/
		}

		$row->api = route('api.queues.loans.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Delete a queue loan
	 *
	 * @apiMethod DELETE
	 * @apiUri    /queues/loans/{id}
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
	 * @return  Response
	 */
	public function delete($id)
	{
		$row = Loan::findOrFail($id);

		$cquery = Loan::query()
			->where('queueid', '=', $row->lenderqueueid)
			->where('lenderqueueid', '=', (int)$row->queueid)
			->where('datetimestart', '=', $row->datetimestart);

		if ($row->hasEnd())
		{
			$cquery->where('datetimestop', '=', $row->datetimestop);
		}
		else
		{
			$cquery->whereNull('datetimestop');
		}

		$counter = $cquery
			->orderBy('datetimestart', 'asc')
			->get()
			->first();

		if ($row->hasStarted())
		{
			$row->datetimestop = Carbon::now();
			$row->save();

			if ($counter)
			{
				$counter->datetimestop = Carbon::now();
				$counter->save();
			}
		}
		else
		{
			if ($counter)
			{
				if (!$counter->delete())
				{
					return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
				}
			}

			if (!$row->delete())
			{
				return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
			}
		}

		return response()->json(null, 204);
	}
}
