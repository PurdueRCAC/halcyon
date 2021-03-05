<?php

namespace App\Modules\Queues\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Queues\Models\Walltime;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Queue Walltimes
 *
 * @apiUri    /api/queues/walltimes
 */
class WalltimesController extends Controller
{
	/**
	 * Display a listing of queue walltimes.
	 *
	 * @apiMethod GET
	 * @apiUri    /api/queues/walltimes
	 * @apiAuthorization  true
	 * @apiParameter {
	 *      "name":          "limit",
	 *      "description":   "Number of result to return.",
	 *      "type":          "integer",
	 *      "required":      false,
	 *      "default":       25
	 * }
	 * @apiParameter {
	 *      "name":          "page",
	 *      "description":   "Number of where to start returning results.",
	 *      "type":          "integer",
	 *      "required":      false,
	 *      "default":       0
	 * }
	 * @apiParameter {
	 *      "name":          "search",
	 *      "description":   "A word or phrase to search for.",
	 *      "type":          "string",
	 *      "required":      false,
	 *      "default":       ""
	 * }
	 * @apiParameter {
	 *      "name":          "order",
	 *      "description":   "Field to sort results by.",
	 *      "type":          "string",
	 *      "required":      false,
	 *      "default":       "created",
	 *      "allowedValues": "id, name, datetimecreated, datetimeremoved, parentid"
	 * }
	 * @apiParameter {
	 *      "name":          "order_dir",
	 *      "description":   "Direction to sort results by.",
	 *      "type":          "string",
	 *      "required":      false,
	 *      "default":       "desc",
	 *      "allowedValues": "asc, desc"
	 * }
	 * @param   Request  $request
	 * @return Response
	 */
	public function index(Request $request)
	{
		$filters = array(
			'queueid' => $request->input('queueid'),
			'datetimestart' => $request->input('datetimestart'),
			'datetimestop' => $request->input('datetimestop'),
			// Paging
			'limit'    => $request->input('limit', config('list_limit', 20)),
			'page'     => $request->input('page', 1),
			// Sorting
			'order'     => $request->input('order', 'id'),
			'order_dir' => $request->input('order_dir', 'desc')
		);

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = 'asc';
		}

		$query = Walltime::query();

		if ($filters['queueid'])
		{
			$query->where('queueid', '=', $filters['queueid']);
		}

		if ($filters['datetimestart'])
		{
			$query->where('datetimestart', '>=', $filters['datetimestart']);
		}

		if ($filters['datetimestop'])
		{
			$query->where('datetimestop', '<', $filters['datetimestop']);
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page'])
			->appends(array_filter($filters));

		return new ResourceCollection($rows);
	}

	/**
	 * Create a queue walltime
	 *
	 * @apiMethod POST
	 * @apiUri    /api/queues/walltimes
	 * @apiAuthorization  true
	 * @apiParameter {
	 *      "name":          "queueid",
	 *      "description":   "The ID of owning queue",
	 *      "type":          "integer",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @apiParameter {
	 *      "name":          "datetimestart",
	 *      "description":   "The start time. Defaults to now.",
	 *      "type":          "string",
	 *      "required":      false,
	 *      "default":       ""
	 * }
	 * @apiParameter {
	 *      "name":          "datetimestop",
	 *      "description":   "The stop time",
	 *      "type":          "string",
	 *      "required":      false,
	 *      "default":       ""
	 * }
	 * @apiParameter {
	 *      "name":          "walltime",
	 *      "description":   "walltime in seconds",
	 *      "type":          "integer",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @param   Request  $request
	 * @return Response
	 */
	public function create(Request $request)
	{
		$request->validate([
			'queueid' => 'required|integer|min:1',
			'datetimestart' => 'nullable|string',
			'datetimestop' => 'nullable|string',
			'walltime' => 'required|integer',
		]);

		$row = Walltime::create($request->all());

		return new JsonResource($row);
	}

	/**
	 * Read a queue walltime
	 *
	 * @apiMethod GET
	 * @apiUri    /api/queues/walltimes/{id}
	 * @apiAuthorization  true
	 * @apiParameter {
	 *      "name":          "id",
	 *      "description":   "The ID of the queue walltime",
	 *      "type":          "integer",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @param   integer  $id
	 * @return  Response
	 */
	public function read($id)
	{
		$row = Walltime::findOrFail($id);

		return new JsonResource($row);
	}

	/**
	 * Update a queue walltime
	 *
	 * @apiMethod PUT
	 * @apiUri    /api/queues/walltimes/{id}
	 * @apiAuthorization  true
	 * @apiParameter {
	 *      "name":          "id",
	 *      "description":   "The ID of the queue walltime",
	 *      "type":          "integer",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @apiParameter {
	 *      "name":          "datetimestart",
	 *      "description":   "The start time",
	 *      "type":          "string",
	 *      "required":      false,
	 *      "default":       ""
	 * }
	 * @apiParameter {
	 *      "name":          "datetimestop",
	 *      "description":   "The stop time",
	 *      "type":          "string",
	 *      "required":      false,
	 *      "default":       ""
	 * }
	 * @param   integer  $id
	 * @param   Request  $request
	 * @return  Response
	 */
	public function update($id, Request $request)
	{
		$row = Walltime::findOrFail($id);

		$request->validate([
			'datetimestart' => 'nullable|string',
			'datetimestop' => 'nullable|string',
			'walltime' => 'nullable|integer',
		]);

		$row->update($request->all());

		return new JsonResource($row);
	}

	/**
	 * Delete a queue walltime
	 *
	 * @apiMethod DELETE
	 * @apiUri    /api/queues/walltimes/{id}
	 * @apiAuthorization  true
	 * @apiParameter {
	 *      "name":          "id",
	 *      "description":   "The ID of the queue walltime",
	 *      "type":          "integer",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @param   integer  $id
	 * @return  Response
	 */
	public function delete($id)
	{
		$row = Walltime::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
		}

		return response()->json(null, 204);
	}
}
