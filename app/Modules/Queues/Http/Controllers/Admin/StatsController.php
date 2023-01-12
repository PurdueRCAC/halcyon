<?php

namespace App\Modules\Queues\Http\Controllers\Admin;

use Illuminate\Http\Response;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use App\Halcyon\Http\StatefulRequest;
use App\Modules\Queues\Models\Queue;
use App\Modules\Queues\Models\Type;
use App\Modules\Resources\Models\Asset;

class StatsController extends Controller
{
	/**
	 * Display a listing of the queue.
	 * 
	 * @param  StatefulRequest $request
	 * @return View
	 */
	public function index(StatefulRequest $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			'state'     => 'enabled',
			'type'      => 0,
			'scheduler' => 0,
			'resource'  => 0,
			'class'     => 'owner',
			// Paging
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			// Sorting
			'order'     => Queue::$orderBy,
			'order_dir' => Queue::$orderDir,
		);

		$reset = false;
		$request = $request->mergeWithBase();
		foreach ($filters as $key => $default)
		{
			if ($key != 'page'
			 && $request->has($key) //&& session()->has('queues.stats.filter_' . $key)
			 && $request->input($key) != session()->get('queues.stats.filter_' . $key))
			{
				$reset = true;
			}
			$filters[$key] = $request->state('queues.stats.filter_' . $key, $key, $default);
		}
		$filters['page'] = $reset ? 1 : $filters['page'];

		if (!in_array($filters['order'], ['id', 'name', 'enabled', 'type', 'parent', 'queuetype', 'groupid']))
		{
			$filters['order'] = Queue::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Queue::$orderDir;
		}

		$resources = Asset::query()
			->where('batchsystem', '>', 0)
			->orderBy('name', 'asc')
			->get();

		$resource = null;
		if ($filters['resource'])
		{
			foreach ($resources as $re)
			{
				if ($re->id == $filters['resource'])
				{
					$resource = $re;
					break;
				}
			}
		}

		$types = Type::orderBy('name', 'asc')->get();

		return view('queues::admin.stats.index', [
			'types' => $types,
			'resources' => $resources,
			'resource' => $resource,
			'filters' => $filters,
		]);
	}
}
