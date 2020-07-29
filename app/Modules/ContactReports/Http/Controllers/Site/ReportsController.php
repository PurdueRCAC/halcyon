<?php

namespace App\Modules\ContactReports\Http\Controllers\Site;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\ContactReports\Models\Report;

class ReportsController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * @return Response
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			'group'    => null,
			'start'    => null,
			'stop'     => null,
			'notice'   => '*',
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			'order'     => Report::$orderBy,
			'order_dir' => Report::$orderDir,
		);

		foreach ($filters as $key => $default)
		{
			//$filters[$key] = $request->state('crm.reports.filter_' . $key, $key, $default);
			$filters[$key] = $request->input($key, $default);
		}

		if (!in_array($filters['order'], array_keys((new Report)->getAttributes())))
		{
			$filters['order'] = Report::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Report::$orderDir;
		}

		$query = Report::query();

		if ($filters['search'])
		{
			$query->where(function($query) use ($filters)
			{
				$query->where('headline', 'like', '%' . $filters['search'] . '%')
					->orWhere('body', 'like', '%' . $filters['search'] . '%');
			});
		}

		if ($filters['notice'] != '*')
		{
			$query->where('notice', '=', $filters['notice']);
		}

		if ($filters['group'])
		{
			$query->where('groupid', '=', $filters['group']);
		}

		$rows = $query
			->withCount('comments')
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		app('pathway')->append(
			trans('contactreports::contactreports.contact reports'),
			route('site.contactreports.index')
		);

		return view('contactreports::site.index', [
			'filters' => $filters,
			'rows' => $rows
		]);
	}

	/**
	 * Display a listing of the resource.
	 * @return Response
	 */
	public function search()
	{
		$types = Type::all();

		app('pathway')->append(
			config('news.name'),
			route('site.news.index')
		);

		return view('news::site.search', [
			'types' => $types
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 * @return Response
	 */
	public function create()
	{
		app('pathway')
			->append(
				config('news.name'),
				route('site.news.index')
			)
			->append(
				__('resources::assets.create'),
				url('/resources/new')
			);

		return view('news::site.create');
	}

	/**
	 * Store a newly created resource in storage.
	 * @param  Request $request
	 * @return Response
	 */
	public function store(Request $request)
	{
	}

	/**
	 * Show the specified entry
	 *
	 * @param   string  $name
	 * @return  Response
	 */
	public function type($name)
	{
		$row = Type::findByName($name);

		$types = Type::all();

		app('pathway')
			->append(
				config('resources.name'),
				url('/resources')
			)
			->append(
				$row->name,
				route('site.news.type', ['name' => $name])
			);

		return view('news::site.type', [
			'type' => $row,
			'types' => $types
		]);
	}

	/**
	 * Show the specified entry
	 *
	 * @param   integer  $id
	 * @return  Response
	 */
	public function show($id)
	{
		$row = Report::findOrFail($id);

		$types = Type::all();

		app('pathway')
			->append(
				config('resources.name'),
				url('/resources')
			)
			->append(
				$row->headline,
				route('site.news.show', ['id' => $id])
			);

		return view('news::site.article', [
			'article' => $row,
			'types' => $types
		]);
	}

	/**
	 * Show the form for editing the specified resource.
	 * @return Response
	 */
	public function edit()
	{
		$id = 1;

		app('pathway')
			->append(
				config('resources.name'),
				url('/resources')
			)
			->append(
				__('resources::assets.edit'),
				url('/resources/edit/:id', $id)
			);

		return view('news::site.edit');
	}

	/**
	 * Comment the specified resource in storage.
	 * @param  Request $request
	 * @return Response
	 */
	public function update(Request $request)
	{
	}

	/**
	 * Remove the specified resource from storage.
	 * @return Response
	 */
	public function destroy()
	{
	}
}
