<?php

namespace App\Modules\Pages\Http\Controllers\Site;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\Controller;
use App\Modules\Pages\Models\Page;
//use App\Modules\Pages\Events\PageContentIsRendering;
//use App\Modules\Pages\Events\PageContentBeforeDisplay;
//use App\Modules\Pages\Events\PageContentAfterDisplay;
use App\Modules\Pages\Events\PageTitleAfterDisplay;

class PagesController extends Controller
{
	/**
	 * Display a page
	 *
	 * @return  Response
	 */
	public function index(Request $request)
	{
		$path = trim($request->path(), '/');

		// Load the entire path
		$pages = Page::stackByPath($path);

		if (!count($pages))
		{
			abort(404, trans('pages::pages.article not found'));
		}

		$levels = auth()->user()
			? auth()->user()->getAuthorisedViewLevels()
			: array(1);

		// We want to go through every article in the path, starting with the root
		// node and see if the user has proper permissions to view the article.
		// If a parent isn't accessible, then the child shouldn't be either.
		foreach ($pages as $page)
		{
			// Ensure we have an article
			if (!$page->id)
			{
				abort(404, trans('pages::pages.article not found'));
			}

			// Does the user have access to the article?
			if (!in_array($page->access, $levels))
			{
				abort(403, trans('pages::pages.permission denied'));
			}

			// Can non-managers view this article?
			if (!auth()->user() || !auth()->user()->can('manage pages'))
			{
				if (!$page->isPublished())
				{
					abort(404, trans('pages::pages.article not found'));
				}
			}

			if ($page->parent_id)
			{
				app('pathway')->append(
					$page->title,
					url($page->path)
				);
			}
		}

		/*$page->styles = array();
		if ($page->isRoot())
		{
			$page->styles = array(asset('themes/Rcac/css/homepage.css'));
		}
		$page->scripts = array();*/

		//event($event = new PageContentIsRendering($page->content));
		//$page->content = $event->getBody();

		//$results = event('onContentPrepare', array('pages.article', &$page));

		$page->event = new \stdClass();

		//$results = event('onContentAfterTitle', array('pages.article', &$page));
		//$page->event->afterDisplayTitle = trim(implode("\n", $results));
		event($event = new PageTitleAfterDisplay($page));
		$page->event->afterDisplayTitle = $event->getContent();

		//$results = event('onContentBeforeDisplay', array('pages.article', &$page));
		//$page->event->beforeDisplayContent = trim(implode("\n", $results));

		//event($event = new PageContentBeforeDisplay($page->content));
		//$page->content = $event->getBody();

		//$results = event('onContentAfterDisplay', array('pages.article', &$page));
		//$page->event->afterDisplayContent = trim(implode("\n", $results));

		//event($event = new PageContentAfterDisplay($page->content));
		//$page->content = $event->getBody();

		$parents = array();
		if (auth()->user() && auth()->user()->can('edit pages'))
		{
			$parents = Page::query()
				->select('id', 'title', 'path', 'level')
				->where('level', '>', 0)
				->orderBy('lft', 'asc')
				->get();
		}

		return view('pages::site.index', [
			'page' => $page,
			'parents' => $parents,
		]);
	}

	/**
	 * Show the specified resource.
	 * @return Response
	 */
	public function show(Request $request)
	{
		return $this->index($request);
	}

	/**
	 * Show the form for creating a new page.
	 * @return Response
	 */
	public function create()
	{
		app('pathway')
			->append(
				config('pages.name'),
				route('site.pages.home')
			)
			->append(
				trans('pages::pages.create'),
				route('site.pages.create')
			);

		return view('pages::site.create');
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
				trans('resources::assets.edit'),
				url('/resources/edit/:id', $id)
			);

		return view('resources::site.edit');
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
