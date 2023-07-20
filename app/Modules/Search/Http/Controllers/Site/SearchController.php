<?php

namespace App\Modules\Search\Http\Controllers\Site;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Routing\Controller;
use App\Modules\Search\Events\Searching;

class SearchController extends Controller
{
	/**
	 * Display search results
	 *
	 * @param  Request $request
	 * @return View
	 */
	public function index(Request $request): View
	{
		// Get filters
		$filters = array(
			'search'    => '',
			'limit'     => intval(config('list_limit', 20)),
			'page'      => 1,
			'order'     => 'weight',
			'order_dir' => 'desc',
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
		$filters['start'] = ($filters['limit'] * $filters['page']) - $filters['limit'];

		if (!in_array($filters['order'], ['title', 'weight', 'updated_at', 'created_at']))
		{
			$filters['order'] = 'weight';
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = 'asc';
		}

		event($event = new Searching(
			$filters['search'],
			$filters['page'],
			$filters['limit'],
			$filters['order'],
			$filters['order_dir']
		));

		$rows = $event->rows->sortByDesc($filters['order']);
		$total = count($rows);
		$rows = $rows->slice($filters['start'], $filters['limit']);

		$paginator = new \Illuminate\Pagination\LengthAwarePaginator(
			$rows,
			$total,
			$filters['limit'],
			$filters['page']
		);
		$paginator->withPath(route('site.search.index'))->appends(['search' => $filters['search']]);

		return view('search::site.index', [
			'rows' => $rows,
			'filters' => $filters,
			'paginator' => $paginator,
		]);
	}
}
