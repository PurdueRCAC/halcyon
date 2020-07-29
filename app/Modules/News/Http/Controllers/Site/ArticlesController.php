<?php

namespace App\Modules\News\Http\Controllers\Site;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\News\Models\Article;
use App\Modules\News\Models\Type;
use App\Modules\Resources\Entities\Asset;
use Carbon\Carbon;

class ArticlesController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * @return Response
	 */
	public function index()
	{
		$types = Type::query()->orderBy('name', 'asc')->get();

		app('pathway')->append(
			config('news.name'),
			route('site.news.index')
		);

		return view('news::site.index', [
			'types' => $types
		]);
	}

	/**
	 * Display a listing of the resource.
	 * @return Response
	 */
	public function search()
	{
		$types = Type::all();

		app('pathway')
			->append(
				config('news.name'),
				route('site.news.index')
			)
			->append(
				trans('news::news.search'),
				route('site.news.search')
			);

		return view('news::site.search', [
			'types' => $types
		]);
	}

	/**
	 * Show the form for editing the specified resource.
	 * @return Response
	 */
	public function rss()
	{
		$types = Type::query()->orderBy('name', 'asc')->get();

		app('pathway')
			->append(
				config('news.name'),
				route('site.news.index')
			)
			->append(
				trans('news::news.feeds'),
				route('site.news.rss')
			);

		return view('news::site.rss', [
			'types' => $types
		]);
	}

	/**
	 * Show the form for editing the specified resource.
	 * @return Response
	 */
	public function feed(Request $request, $name = null)
	{
		$parts = explode(',', $name);

		$types = array();
		$resources = array();

		if (count($parts))
		{
			foreach ($parts as $id)
			{
				if (is_numeric($id))
				{
					$row = Type::find($id);

					if (!$row)
					{
						continue;
					}

					$types[] = $row;
				}
				else
				{
					$row = Type::findByName($id);

					if ($row)
					{
						$types[] = $row;
						continue;
					}

					// Check search terms against resources.
					$row = Asset::findByName($id);

					$resources[] = $row;
				}
			}
		}

		// If there is no matching newstype, just display all of them.
		if (!count($types))
		{
			$types = Type::all();
		}

		$types = collect($types);
		$typeids = $types->pluck('id')->toArray();

		$resources = collect($resources);
		$resourceids = $resources->pluck('id')->toArray();

		$query = Article::query();

		if (count($resources))
		{
			$query->whereResourceIn($resourceids);
		}

		$items = $query
			->wherePublished()
			->whereIn('newstypeid', $typeids)
			->orderBy('datetimenews', 'desc')
			->limit(20)
			->paginate();

		$meta = array(
			'title'         => config('app.name') . ' - ' . implode(', ', $types->pluck('name')->toArray()),
			'url'           => $request->url(),
			'description'   => trans('news::news.feed description', [':category' => implode(', ', $types->pluck('name')->toArray())]),
			'language'      => app('translator')->locale(),
			'lastBuildDate' => Carbon::now()->format('D, d M Y H:i:s T'),
		);

		$contents = view('news::site.feed', [
			'meta'  => $meta,
			'items' => $items,
		]);

		return new Response($contents, 200, [
			'Content-Type' => 'application/xml;charset=UTF-8',
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 * @return Response
	 */
	public function manage()
	{
		$types = Type::all();

		$templates = Article::where('template', '=', 1)->get();

		app('pathway')
			->append(
				config('news.name'),
				route('site.news.index')
			)
			->append(
				trans('news::news.manage news'),
				route('site.news.manage')
			);

		return view('news::site.manage', [
			'types' => $types,
			'templates' => $templates
		]);
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

		if (!$row)
		{
			abort(404);
		}

		$types = Type::all();

		app('pathway')
			->append(
				config('news.name'),
				route('site.news.index')
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
	 * @param   string  $name
	 * @return  Response
	 */
	public function coffee()
	{
		$row = Type::findByName('coffee');

		if (!$row)
		{
			abort(404);
		}

		$types = Type::all();

		app('pathway')
			->append(
				config('news.name'),
				route('site.news.index')
			)
			->append(
				$row->name,
				route('site.news.type', ['name' => 'coffee'])
			);

		return view('news::site.coffee', [
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
		$row = Article::findOrFail($id);

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
	 * Update the specified resource in storage.
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
