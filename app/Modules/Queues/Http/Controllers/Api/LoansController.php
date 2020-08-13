<?php

namespace App\Modules\Queues\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Queues\Models\Loan;
use Carbon\Carbon;

/**
 * Queue Loans
 *
 * @apiUri    /api/queues/loans
 */
class LoansController extends Controller
{
	/**
	 * Display a listing of purchases for a queue
	 *
	 * @apiMethod GET
	 * @apiUri    /api/queues/loans
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "limit",
	 * 		"description":   "Number of result to return.",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       25
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "page",
	 * 		"description":   "Number of where to start returning results.",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       0
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "search",
	 * 		"description":   "A word or phrase to search for.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       ""
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "order",
	 * 		"description":   "Field to sort results by.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "datetimestart",
	 * 		"allowedValues": "id, queueid, lenderqueueid, datetimestart, datetimestop"
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "order_dir",
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
			//'search'   => $request->input('search'),
			'state'    => $request->input('state'),
			'queueid'   => $request->input('queueid', 0),
			'lenderqueueid' => $request->input('lenderqueueid', 0),
			// Paging
			'limit'    => $request->input('limit', config('list_limit', 20)),
			//'start' => $request->input('limitstart', 0),
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
			$query->whereNotNull('datetimestop')
				->where('datetimestop', '!=', '0000-00-00 00:00:00');
		}
		elseif ($filters['state'] == 'ongoing')
		{
			$query->where('datetimestop', '=', '0000-00-00 00:00:00')
				->orWhereNull('datetimestop');
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
			->paginate($filters['limit'])
			->appends(array_filter($filters));

		return new ResourceCollection($rows);
	}

	/**
	 * Create a queue purchase
	 *
	 * @apiMethod POST
	 * @apiUri    /api/queues/loans
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "queueid",
	 *      "description":   "ID of the queue being loaned to",
	 *      "type":          "integer",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "lenderqueueid",
	 *      "description":   "ID of the queue being loaned",
	 *      "type":          "integer",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "datetimestart",
	 *      "description":   "Date/time (YYYY-MM-DD hh:mm:ss) of when the loan starts",
	 *      "type":          "string",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "datetimestop",
	 *      "description":   "Date/time (YYYY-MM-DD hh:mm:ss) of when the loan stops",
	 *      "type":          "string",
	 *      "required":      false,
	 *      "default":       ""
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "nodecount",
	 *      "description":   "Node count",
	 *      "type":          "integer",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "corecount",
	 *      "description":   "Core count",
	 *      "type":          "integer",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @return Response
	 */
	public function create(Request $request)
	{
		$request->validate([
			'queueid' => 'required|integer',
			'lenderqueueid' => 'required|integer',
			'datetimestart' => 'nullable|datetime',
			'datetimestop' => 'nullable|datetime',
			'nodecount' => 'nullable|integer',
			'corecount' => 'nullable|integer',
		]);

		$row = new Loan;
		$row->fill($request->all());

		if (!$row->datetimestart)
		{
			$row->datetimestart = Carbon::now()->toDateTimeFormat();
		}

		if (!$row->queue)
		{
			return response()->json(['message' => trans('queues::queues.invalid queue id')], 415);
		}

		if (!$row->source)
		{
			return response()->json(['message' => trans('queues::queues.invalid lender queue id')], 415);
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('messages.create failed')], 500);
		}

		// Does the queue have any cores yet?
		$count = Loan::query()
			->where('queueid', '=', (int)$row->queueid)
			->orderBy('datetimestart', 'asc')
			->get()
			->first();

		if (!$count)
		{
			return response()->json(['message' => trans('queues::queues.Have not been sold anything and never will have anything')], 409);
		}
		elseif ($count->datetimestart > $row->datetimestart)
		{
			return response()->json(['message' => trans('queues::queues.Have not been sold anything before this would start')], 409);
		}

		// Look for an existing entry in the same time frame and same queues to update instead
		$exist = Size::query()
			->where('queueid', '=', (int)$row->queueid)
			->where('lenderqueueid', '=', $row->lenderqueueid)
			->where('datetimestart', '=', $row->datetimestart)
			->where('datetimestop', '=', $row->datetimestop)
			->orderBy('datetimestart', 'asc')
			->get()
			->first();

		if ($exist)
		{
			$exist->nodecount = $row->nodecount;
			$exist->corecount = $row->corecount;

			if (!$exist->save())
			{
				return response()->json(['message' => trans('messages.create failed')], 500);
			}

			return new JsonResource($exist);
		}

		return new JsonResource($row);
	}

	/**
	 * Read a queue
	 *
	 * @apiMethod GET
	 * @apiUri    /api/queues/loans/{id}
	 * @apiAuthorization  true
	 * @apiParameter {
	 *      "in":            "query",
	 *      "name":          "id",
	 *      "description":   "The ID of the queue type",
	 *      "type":          "integer",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @return  Response
	 */
	public function read($id)
	{
		$row = Loan::findOrFail($id);

		return new JsonResource($row);
	}

	/**
	 * Update a queue
	 *
	 * @apiMethod PUT
	 * @apiUri    /api/queues/loans/{id}
	 * @apiAuthorization  true
	 * @apiParameter {
	 *      "in":            "query",
	 *      "name":          "id",
	 *      "description":   "The ID of the queue type",
	 *      "type":          "integer",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "queueid",
	 *      "description":   "ID of the queue being loaned to",
	 *      "type":          "integer",
	 *      "required":      false,
	 *      "default":       ""
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "lenderqueueid",
	 *      "description":   "ID of the queue being loaned",
	 *      "type":          "integer",
	 *      "required":      false,
	 *      "default":       ""
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "datetimestart",
	 *      "description":   "Date/time (YYYY-MM-DD hh:mm:ss) of when the loan starts",
	 *      "type":          "string",
	 *      "required":      false,
	 *      "default":       ""
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "datetimestop",
	 *      "description":   "Date/time (YYYY-MM-DD hh:mm:ss) of when the loan stops",
	 *      "type":          "string",
	 *      "required":      false,
	 *      "default":       ""
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "nodecount",
	 *      "description":   "Node count",
	 *      "type":          "integer",
	 *      "required":      false,
	 *      "default":       ""
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 *      "name":          "corecount",
	 *      "description":   "Core count",
	 *      "type":          "integer",
	 *      "required":      false,
	 *      "default":       ""
	 * }
	 * @return  Response
	 */
	public function update($id, Request $request)
	{
		$request->validate([
			'queueid' => 'nullable|integer',
			'lenderqueueid' => 'nullable|integer',
			'datetimestart' => 'nullable|datetime',
			'datetimestop' => 'nullable|datetime',
			'nodecount' => 'nullable|integer',
			'corecount' => 'nullable|integer',
		]);

		$row = Loan::findOrFail($id);

		if (!$row->update($request->all()))
		{
			return response()->json(['message' => trans('messages.create failed')], 500);
		}

		return new JsonResource($row);
	}

	/**
	 * Delete a queue
	 *
	 * @apiMethod DELETE
	 * @apiUri    /api/queues/loans/{id}
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "query",
	 *      "name":          "id",
	 *      "description":   "The ID of the queue type",
	 *      "type":          "integer",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @return  Response
	 */
	public function delete($id)
	{
		$row = Loan::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
		}

		return response()->json(null, 204);
	}
}
