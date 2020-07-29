<?php

namespace App\Modules\Queues\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Queues\Models\Size;
use Carbon\Carbon;

class SizesController extends Controller
{
	/**
	 * Display a listing of queue sizes
	 *
	 * @apiMethod GET
	 * @apiUri    /queues/sizes
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
	 * @param   Request  $request
	 * @return  Response
	 */
	public function index(Request $request)
	{
		$filters = array(
			'state'    => $request->input('state'),
			'queueid'   => $request->input('queueid', 0),
			'sellerqueueid' => $request->input('sellerqueueid', 0),
			// Paging
			'limit'    => $request->input('limit', config('list_limit', 20)),
			// Sorting
			'order'     => $request->input('order', 'datetimestart'),
			'order_dir' => $request->input('order_dir', 'desc')
		);

		if (!in_array($filters['order'], ['id', 'queueid', 'sellerqueueid', 'datetimestart', 'datetimestop']))
		{
			$filters['order'] = 'datetimestart';
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = 'desc';
		}

		// Build query
		$query = Size::query();

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

		if ($filters['sellerqueueid'])
		{
			$query->where('sellerqueueid', '=', (int)$filters['sellerqueueid']);
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'])
			->appends(array_filter($filters));

		return new ResourceCollection($rows);
	}

	/**
	 * Create a queue size
	 *
	 * @apiMethod POST
	 * @apiUri    /queues/sizes
	 * @apiParameter {
	 *      "name":          "name",
	 *      "description":   "The name of the queue type",
	 *      "type":          "string",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @param  Request  $request
	 * @return Response
	 */
	public function create(Request $request)
	{
		$request->validate([
			'queueid' => 'required|integer',
			'sellerqueueid' => 'required|integer',
			'datetimestart' => 'nullable|datetime',
			'datetimestop' => 'nullable|datetime',
			'nodecount' => 'nullable|integer',
			'corecount' => 'nullable|integer',
		]);

		$row = new Size;
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
			return response()->json(['message' => trans('queues::queues.invalid seller queue id')], 415);
		}

		// Does the queue have any cores yet?
		$count = Size::query()
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
			->where('sellerqueueid', '=', $row->sellerqueueid)
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

		if (!$row->save())
		{
			return response()->json(['message' => trans('messages.create failed')], 500);
		}

		return new JsonResource($row);
	}

	/**
	 * Read a queue size
	 *
	 * @apiMethod POST
	 * @apiUri    /queues/sizes/{id}
	 * @apiParameter {
	 *      "name":          "id",
	 *      "description":   "The ID of the queue type",
	 *      "type":          "integer",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @param   integer  $id
	 * @return  Response
	 */
	public function read($id)
	{
		$row = Size::findOrFail($id);

		return new JsonResource($row);
	}

	/**
	 * Update a queue size
	 *
	 * @apiMethod PUT
	 * @apiUri    /queues/sizes/{id}
	 * @apiParameter {
	 *      "name":          "id",
	 *      "description":   "The ID of the queue type",
	 *      "type":          "integer",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @apiParameter {
	 *      "name":          "name",
	 *      "description":   "The name of the queue type",
	 *      "type":          "string",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @param   integer  $id
	 * @param   Request  $request
	 * @return  Response
	 */
	public function update($id, Request $request)
	{
		$request->validate([
			'queueid' => 'nullable|integer',
			'sellerqueueid' => 'nullable|integer',
			'datetimestart' => 'nullable|datetime',
			'datetimestop' => 'nullable|datetime',
			'nodecount' => 'nullable|integer',
			'corecount' => 'nullable|integer',
		]);

		$row = Size::findOrFail($id);

		if (!$row->update($request->all()))
		{
			return response()->json(['message' => trans('messages.create failed')], 500);
		}

		return new JsonResource($row);
	}

	/**
	 * Delete a queue size
	 *
	 * @apiMethod DELETE
	 * @apiUri    /queues/sizes/{id}
	 * @apiParameter {
	 *      "name":          "id",
	 *      "description":   "The ID of the queue type",
	 *      "type":          "integer",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @param   integer  $id
	 * @return  Response
	 */
	public function delete($id)
	{
		$row = Size::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
		}

		return response()->json(null, 204);
	}
}
