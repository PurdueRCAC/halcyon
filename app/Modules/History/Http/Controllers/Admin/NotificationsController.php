<?php

namespace App\Modules\History\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Notifications\DatabaseNotification;
use App\Halcyon\Http\StatefulRequest;

class NotificationsController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @param   StatefulRequest  $request
	 * @return  View
	 */
	public function index(StatefulRequest $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			'notifiable_id'   => 0,
			'notifiable_type' => null,
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

		$reset = false;
		$request = $request->mergeWithBase();
		foreach ($filters as $key => $default)
		{
			if ($key != 'page'
			 && $request->has($key) //&& session()->has('history.notifications.filter_' . $key)
			 && $request->input($key) != session()->get('history.notifications.filter_' . $key))
			{
				$reset = true;
			}
			$filters[$key] = $request->state('history.notifications.filter_' . $key, $key, $default);
		}
		$filters['page'] = $reset ? 1 : $filters['page'];

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

		if ($filters['notifiable_id'])
		{
			$query->where('notifiable_id', '=', $filters['notifiable_id']);
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
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		$types = DatabaseNotification::query()
			->select(DB::raw('DISTINCT(type)'))
			->get();

		return view('history::admin.notifications.index', [
			'filters' => $filters,
			'rows' => $rows,
			'types' => $types,
		]);
	}

	/**
	 * Show the form for editing the specified entry
	 *
	 * @param   int   $id
	 * @return  View
	 */
	public function show($id)
	{
		$row = DatabaseNotification::findOrFail($id);

		return view('history::admin.notifications.show', [
			'row' => $row
		]);
	}

	/**
	 * Remove the specified entry
	 *
	 * @param   Request  $request
	 * @return  RedirectResponse
	 */
	public function delete(Request $request)
	{
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		foreach ($ids as $id)
		{
			$row = DatabaseNotification::findOrFail($id);

			if (!$row->delete())
			{
				$request->session()->flash('error', trans('global.messages.delete failed'));
				continue;
			}

			$success++;
		}

		if ($success)
		{
			$request->session()->flash('success', trans('global.messages.item deleted', ['count' => $success]));
		}

		return $this->cancel();
	}

	/**
	 * Return to the main view
	 *
	 * @return  RedirectResponse
	 */
	public function cancel()
	{
		return redirect(route('admin.history.notifications'));
	}
}
