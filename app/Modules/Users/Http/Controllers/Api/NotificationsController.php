<?php

namespace App\Modules\Users\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Notifications\DatabaseNotification;
use App\Modules\Users\Models\User;

/**
 * User Notifications
 * 
 * @apiUri    /users/notifications
 */
class NotificationsController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /users/notifications
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "user_id",
	 * 		"description":   "User the notifications are for.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "read",
	 * 		"description":   "Find notifications amrked as read",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "unread",
	 * 		"description":   "Find notifications amrked as unread",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "read",
	 * 		"description":   "Find notifications amrked as read",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
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
	 * 		"description":   "Number of result per page.",
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
	 * 			"default":   "datetimecreated",
	 * 			"enum": [
	 * 				"id",
	 * 				"datetimecreated",
	 * 				"datetimeremoved"
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
	 * 			"default":   "desc",
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
		// Get filters
		$filters = array(
			'search'    => null,
			'user_id'   => 0,
			'type'      => null,
			'read'      => null,
			'unread'    => null,
			// Paging
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			// Sorting
			'order'     => 'created_at',
			'order_dir' => 'desc',
		);

		foreach ($filters as $key => $default)
		{
			$val = $request->input($key);
			$val = !is_null($val) ? $val : $default;

			$filters[$key] = $val;
		}

		if (!in_array($filters['order'], ['id', 'created_at', 'user_id', 'read_at', 'notifiable_id', 'notifiable_type']))
		{
			$filters['order'] = 'created_at';
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = 'desc';
		}

		$query = DatabaseNotification::query();

		if ($filters['type'])
		{
			$query->where('type', '=', $filters['type']);
		}

		if ($filters['user_id'])
		{
			$query->where('notifiable_type', '=', User::class);
			$query->where('notifiable_id', '=', $filters['user_id']);
		}

		if ($filters['read'])
		{
			$query->whereNotNull('read_at');
		}

		if ($filters['unread'])
		{
			$query->whereNull('read_at');
		}

		if ($filters['search'])
		{
			if (is_numeric($filters['search']))
			{
				$query->where('id', '=', (int)$filters['search']);
			}
			else
			{
				$query->where('data', 'like', '%' . $filters['search'] . '%');
			}
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page'])
			->appends(array_filter($filters));

		$rows->each(function ($item, $key)
		{
			$item->api = route('api.users.notifications.read', ['id' => $item->id]);
		});

		return new ResourceCollection($rows);
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /users/notifications/{id}
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
	 * @param  int  $id
	 * @return JsonResource
	 */
	public function read($id)
	{
		$row = DatabaseNotification::findOrFail((int)$id);

		$data = $row->toArray();
		$data['api'] = route('api.users.notifications.read', ['id' => $row->id]);

		return new JsonResource($data);
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /users/notifications/{id}
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
	 * 		"name":          "subject",
	 * 		"description":   "Subject of the note",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": "100"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "body",
	 * 		"description":   "Note content",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @param   Request $request
	 * @param   int $id
	 * @return  JsonResource|JsonResponse
	 */
	public function update(Request $request, $id)
	{
		$request->validate([
			'mark' => 'nullable|string'
		]);

		$row = DatabaseNotification::findOrFail($id);
		if ($mark == 'read')
		{
			$row->markAsRead();
		}
		elseif ($mark == 'unread')
		{
			$row->markAsUnread();
		}

		$data = $row->toArray();
		$data['api'] = route('api.users.notifications.read', ['id' => $row->id]);

		return new JsonResource($data);
	}

	/**
	 * Update all entries as read
	 *
	 * @apiMethod PUT
	 * @apiUri    /users/notifications/mark-all-as-unread
	 * @apiAuthorization  true
	 * @apiResponse {
	 * 		"201": {
	 * 			"description": "Successful deletion"
	 * 		}
	 * }
	 * @return  ResourceCollection
	 */
	public function markAllRead()
	{
		$rows = DatabaseNotification::query()
			->where('notifiable_id', '=', auth()->user()->id)
			->whereNull('read_at')
			->get();

		$rows->each(function ($item, $key)
		{
			$item->markAsRead();
			$item->api = route('api.users.notifications.read', ['id' => $item->id]);
		});

		return new ResourceCollection($rows);
	}

	/**
	 * Update all entries as unread
	 *
	 * @apiMethod PUT
	 * @apiUri    /users/notifications/mark-all-as-unread
	 * @apiAuthorization  true
	 * @apiResponse {
	 * 		"201": {
	 * 			"description": "Successful deletion"
	 * 		}
	 * }
	 * @return  ResourceCollection
	 */
	public function markAllUnread()
	{
		$rows = DatabaseNotification::query()
			->where('notifiable_id', '=', auth()->user()->id)
			->whereNotNull('read_at')
			->get();

		$rows->each(function ($item, $key)
		{
			$item->markAsUnread();
			$item->api = route('api.users.notifications.read', ['id' => $item->id]);
		});

		return new ResourceCollection($rows);
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /users/notifications/{id}
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
	 * 			"description": "Successful deletion"
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		}
	 * }
	 * @param   int  $id
	 * @return  JsonResponse
	 */
	public function delete($id)
	{
		$row = DatabaseNotification::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $row->id])]);
		}

		return response()->json(null, 204);
	}
}
