<?php

namespace App\Modules\Queues\Http\Controllers\Admin;

use Illuminate\Http\Response;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use App\Halcyon\Http\Concerns\UsesFilters;
use App\Modules\Queues\Models\Queue;
use App\Modules\Queues\Models\Type;
use App\Modules\Resources\Models\Asset;

class StatsController extends Controller
{
	use UsesFilters;

	/**
	 * Display a listing of the queue.
	 * 
	 * @param  Request $request
	 * @return View
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = $this->getStatefulFilters($request, 'queues.stats', [
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
		]);

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
