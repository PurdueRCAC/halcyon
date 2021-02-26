<?php

namespace App\Modules\Knowledge\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Modules\Knowledge\Models\Feedback;

/**
 * Pages
 *
 * @apiUri    /api/knowledge/feedback
 */
class FeedbackController extends Controller
{
	/**
	 * Display a listing of feedback
	 *
	 * @return Response
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			'type'      => null,
			'target_id' => 0,
			'user_id'   => 0,
			'ip'        => null,
			'start'     => null,
			'stop'      => null,
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			'order'     => Feedback::$orderBy,
			'order_dir' => Feedback::$orderDir,
			'level'     => 0,
		);

		$refresh = false;
		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->input($key, $default);
		}

		if ($refresh)
		{
			$filters['page'] = 1;
		}

		if (!in_array($filters['order'], ['id', 'target_id', 'ip', 'type', 'user_id', 'created_at']))
		{
			$filters['order'] = Feedback::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Feedback::$orderDir;
		}

		// If the user isn't a manager, force all feedback to just theirs
		if (auth()->user() && !auth()->user()->can('manage knowledge'))
		{
			$filters['user_id'] = auth()->user()->id;
		}

		$query = Feedback::query();

		if ($filters['search'])
		{
			$query->where('comments', 'like', '%' . $filters['search'] . '%');
		}

		if ($filters['target_id'])
		{
			$query->where('target_id', '=', $filters['target_id']);
		}

		if ($filters['ip'])
		{
			$query->where('ip', '=', $filters['ip']);
		}

		if ($filters['type'])
		{
			$query->where('type', '=', $filters['type']);
		}

		if ($filters['user_id'])
		{
			$query->where('user_id', '=', $filters['user_id']);
		}

		if ($filters['start'])
		{
			$query->where('start', '>=', $filters['start']);
		}

		if ($filters['stop'])
		{
			$query->where('stop', '<', $filters['stop']);
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		return new ResourceCollection($rows);
	}

	/**
	 * Create an entry
	 *
	 * @apiMethod POST
	 * @apiUri    /api/knowledge/feedback
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "user_id",
	 * 		"description":   "ID of user making the feedback",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "target_id",
	 * 		"description":   "Targetted page association ID",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "comments",
	 * 		"description":   "User comments",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "type",
	 * 		"description":   "Type of feedback (e.g., positive, nuetral, negative)",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @param  Request $request
	 * @return Response
	 */
	public function create(Request $request)
	{
		$request->validate([
			'target_id' => 'required|integer',
			//'ip'        => 'nullable|string|max:15',
			'type'      => 'required|string|max:10',
			'user_id'   => 'nullable|integer',
			'comments'  => 'nullable|string|max:255',
		]);

		$row = new Feedback;
		$row->target_id = $request->input('target_id');
		$row->ip = $request->ip();
		$row->type = $request->input('type');
		if (auth()->user())
		{
			$row->user_id = auth()->user()->id;
		}
		if ($request->has('comments'))
		{
			$row->comments = $request->input('comments');
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.save failed')], 409);
		}

		return new JsonResource($row);
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /api/knowledge/feedback/{id}
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @param  integer $id
	 * @return Response
	 */
	public function read($id)
	{
		$row = Feedback::findOrFail((int)$id);

		// If the user isn't a manager, force all feedback to just theirs
		if (!auth()->user()->can('manage knowledge') && $row->user_id != auth()->user()->id)
		{
			return response()->json(['message' => trans('global.messages.not found')], 404);
		}

		return new JsonResource($row);
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /api/knowledge/feedback/{id}
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
	 * 		"name":          "comments",
	 * 		"description":   "User comments",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "type",
	 * 		"description":   "Type of feedback (e.g., positive, nuetral, negative)",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @param   Request $request
	 * @param   integer $id
	 * @return  Response
	 */
	public function update(Request $request, $id)
	{
		$request->validate([
			'type'      => 'nullable|string|max:10',
			'comments'  => 'nullable|string|max:255',
		]);

		$row = Feedback::findOrFail($id);
		if ($request->has('comments'))
		{
			$row->comments = $request->input('comments');
		}
		if ($request->has('type'))
		{
			$row->type = $request->input('type');
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.save failed')], 409);
		}

		return new JsonResource($row);
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /api/knowledge/feedback/{id}
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @param  integer $id
	 * @return Response
	 */
	public function delete($id)
	{
		$row = Feedback::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
		}

		return response()->json(null, 204);
	}
}
