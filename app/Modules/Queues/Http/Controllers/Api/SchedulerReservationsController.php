<?php

namespace App\Modules\Queues\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Queues\Models\SchedulerPolicy;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Scheduler Reservations
 *
 * @apiUri    /api/queues/schedulerreservations
 */
class SchedulerReservationsController extends Controller
{
	/**
	 * Display a listing of queue schedulers
	 *
	 * @apiMethod GET
	 * @apiUri    /api/queues/schedulerreservations
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
			'search' => $request->input('search'),
			// Paging
			'limit'    => $request->input('limit', config('list_limit', 20)),
			// Sorting
			'order'     => $request->input('order', 'name'),
			'order_dir' => $request->input('order_dir', 'desc')
		);

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = 'asc';
		}

		$query = SchedulerPolicy::query();

		if ($filters['search'])
		{
			$query->where('name', 'like', '%' . $filters['search'] . '%');
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'])
			->appends(array_filter($filters));

		return new ResourceCollection($rows);
	}

	/**
	 * Create a queue scheduler policy
	 *
	 * @apiMethod POST
	 * @apiUri    /api/queues/schedulerpolicies
	 * @apiParameter {
	 *      "name":          "name",
	 *      "description":   "The policy name",
	 *      "type":          "string",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @param   Request  $request
	 * @return Response
	 */
	public function create(Request $request)
	{
		$request->validate([
			'name' => 'required|string|max:64',
		]);

		$row = SchedulerPolicy::create($request->all());

		return new JsonResource($row);
	}

	/**
	 * Read a queue scheduler policy
	 *
	 * @apiMethod GET
	 * @apiUri    /api/queues/schedulerpolicies/{id}
	 * @apiParameter {
	 *      "name":          "id",
	 *      "description":   "The ID of the scheduler policy",
	 *      "type":          "integer",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @param   integer  $id
	 * @return  Response
	 */
	public function read($id)
	{
		$row = SchedulerPolicy::findOrFail($id);

		return new JsonResource($row);
	}

	/**
	 * Update a queue scheduler policy
	 *
	 * @apiMethod PUT
	 * @apiUri    /api/queues/schedulerpolicies/{id}
	 * @apiParameter {
	 *      "name":          "id",
	 *      "description":   "The ID of the scheduler policy",
	 *      "type":          "integer",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @apiParameter {
	 *      "name":          "name",
	 *      "description":   "The policy name",
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
		$row = SchedulerPolicy::findOrFail($id);

		$request->validate([
			'name' => 'nullable|string|max:64',
		]);

		$row->update($request->all());

		return new JsonResource($row);
	}

	/**
	 * Delete a queue scheduler policy
	 *
	 * @apiMethod DELETE
	 * @apiUri    /api/queues/schedulerpolicies/{id}
	 * @apiParameter {
	 *      "name":          "id",
	 *      "description":   "The ID of the scheduler policy",
	 *      "type":          "integer",
	 *      "required":      true,
	 *      "default":       ""
	 * }
	 * @param   integer  $id
	 * @return  Response
	 */
	public function delete($id)
	{
		$row = SchedulerPolicy::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
		}

		return response()->json(null, 204);
	}
}
