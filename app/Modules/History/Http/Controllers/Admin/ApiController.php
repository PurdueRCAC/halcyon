<?php

namespace App\Modules\History\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Halcyon\Http\Concerns\UsesFilters;
use App\Modules\History\Models\Log;
use Carbon\Carbon;

class ApiController extends Controller
{
	use UsesFilters;

	/**
	 * Display a listing of the resource.
	 *
	 * @param   Request  $request
	 * @return  View
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = $this->getStatefulFilters($request, 'history.api', [
			'search'    => null,
			'app'       => null,
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			'order'     => Log::$orderBy,
			'order_dir' => Log::$orderDir,
			'action'    => '',
			'transport' => '',
			'status'    => '',
		]);

		if (!in_array($filters['order'], ['id', 'name']))
		{
			$filters['order'] = Log::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Log::$orderDir;
		}

		$query = Log::query()
			->where('app', '=', 'api');

		if ($filters['search'])
		{
			$query->where(function($query) use ($filters)
			{
				$query->where('classname', 'like', '%' . $filters['search'] . '%')
					->orWhere('classmethod', 'like', '%' . $filters['search'] . '%')
					->orWhere('uri', 'like', '%' . $filters['search'] . '%');
			});
		}

		if ($filters['transport'])
		{
			$query->where('transportmethod', '=', $filters['transport']);
		}

		if ($filters['status'])
		{
			$query->where('status', '=', $filters['status']);
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		$types = Log::query()
			->select('classname')
			->where('app', '=', 'api')
			->distinct()
			->orderBy('classname', 'asc')
			->get();

		return view('history::admin.api.index', [
			'filters' => $filters,
			'rows'    => $rows,
			'types'   => $types,
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
		$row = Log::findOrFail($id);

		return view('history::admin.api.show', [
			'row' => $row
		]);
	}

	/**
	 * Show the form for editing the specified resource.
	 * 
	 * @return View
	 */
	public function stats(Request $request)
	{
		$start = Carbon::now()->modify('-1 month');
		$today = Carbon::now()->modify('+1 day');

		// Get filters
		$filters = array(
			'start' => $start->format('Y-m-d'),
			'end' => $today->format('Y-m-d'),
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->input($key, $default);
		}

		if ($filters['end'] < $filters['start'])
		{
			$request->session()->flash('error', trans('history::history.errors.end cannot be before start'));
			$filters['end'] = Carbon::parse($filters['start'])->modify('+1 week')->format('Y-m-d');
		}

		$methods = array(
			'GET',
			'POST',
			'PUT',
			'DELETE',
			'HEAD',
		);

		$stats = array();

		$stats['made'] = Log::query()
			->where('app', '=', 'api')
			->where('datetime', '>=', $filters['start'])
			->where('datetime', '<', $filters['end'])
			->count();

		$stats['errors'] = Log::query()
			->where('app', '=', 'api')
			->where('datetime', '>=', $filters['start'])
			->where('datetime', '<', $filters['end'])
			->where('status', '>=', 400)
			->count();

		$stats['ips'] = Log::query()
			->select('ip', DB::raw('COUNT(*) AS requests'))
			->where('app', '=', 'api')
			->where('datetime', '>=', $filters['start'])
			->where('datetime', '<', $filters['end'])
			->groupBy('ip')
			->orderBy('requests', 'desc')
			->limit(20)
			->get();

		$stats['uris'] = Log::query()
			->select('uri', DB::raw('COUNT(*) AS requests'))
			->where('app', '=', 'api')
			->where('datetime', '>=', $filters['start'])
			->where('datetime', '<', $filters['end'])
			->groupBy('uri')
			->orderBy('requests', 'desc')
			->limit(20)
			->get();

		foreach ($methods as $method)
		{
			$stats['methods'][$method] = Log::query()
				->where('app', '=', 'api')
				->where('transportmethod', '=', $method)
				->where('datetime', '>=', $filters['start'])
				->where('datetime', '<', $filters['end'])
				->count();
		}

		$stats['daily'] = array();

		$start = Carbon::parse($filters['start']);
		$stop  = Carbon::parse($filters['end']);
		$timeframe = round(($stop->timestamp - $start->timestamp) / (60 * 60 * 24));

		$now = Carbon::now();
		$placed = array();
		for ($d = $timeframe; $d >= 0; $d--)
		{
			$yesterday = Carbon::now()->modify('- ' . $d . ' days');
			$tomorrow  = Carbon::now()->modify(($d ? '- ' . ($d - 1) : '+ 1') . ' days');

			$query = Log::query()
				->where('app', '=', 'api');

			$placed[$yesterday->format('Y-m-d')] = $query
				->where('datetime', '>', $yesterday->format('Y-m-d') . ' 00:00:00')
				->where('datetime', '<', $tomorrow->format('Y-m-d') . ' 00:00:00')
				->count();
		}

		$stats['daily'] = $placed;

		return view('history::admin.api.stats', [
			'methods' => $methods,
			'filters' => $filters,
			'stats' => $stats,
		]);
	}
}
