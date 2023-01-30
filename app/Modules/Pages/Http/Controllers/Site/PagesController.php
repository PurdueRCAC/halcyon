<?php

namespace App\Modules\Pages\Http\Controllers\Site;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Config\Repository;
use Illuminate\Support\Facades\Validator;
use App\Modules\Pages\Models\Page;
use App\Modules\Pages\Events\PageMetadata;
use App\Modules\Pages\Events\PageTitleAfterDisplay;

class PagesController extends Controller
{
	/**
	 * Display a page
	 *
	 * @param   Request $request
	 * @return  View
	 */
	public function index(Request $request)
	{
		if ($request->wantsJson()) //$request->expectsJson())
		{
			return response()->json(['message' => trans('global.unacceptable header')], 406);
		}

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
					route('page', ['uri' => $page->path])
				);
			}
		}

		event($event = new PageMetadata($page));

		$page->event = new \stdClass();

		event($event = new PageTitleAfterDisplay($page));
		$page->event->afterDisplayTitle = $event->getContent();

		$parents = array();
		if (auth()->user() && auth()->user()->can('edit pages'))
		{
			$parents = Page::query()
				->select('id', 'title', 'path', 'level')
				->where('level', '>', 0)
				->orderBy('path', 'asc')
				->get();
		}

		$layout = $page->params->get('layout');

		return view('pages::site.' . ($layout ? $layout : 'index'), [
			'page'    => $page,
			'parents' => $parents,
		]);
	}

	/**
	 * Show the specified page
	 * 
	 * @param  Request $request
	 * @return View
	 */
	public function show(Request $request)
	{
		return $this->index($request);
	}

	/**
	 * Show the form for creating a new page.
	 * 
	 * @return View
	 */
	public function create()
	{
		$row = new Page;
		$row->access = 1;
		$row->state = 1;

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		$parents = Page::query()
			->select('id', 'title', 'path', 'level')
			->where('level', '>', 0)
			->orderBy('lft', 'asc')
			->get();

		return view('pages::site.edit', [
			'row'     => $row,
			'parents' => $parents
		]);
	}

	/**
	 * Store a newly created resource in storage.
	 * 
	 * @param  Request $request
	 * @return Response
	 */
	public function store(Request $request)
	{
		$rules = [
			'title'   => 'required|string|max:255',
			'content' => 'required|string',
			'access'  => 'nullable|min:1'
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$id = $request->input('id');

		$row = $id ? Page::findOrFail($id) : new Page();
		$row->fill($request->input('fields'));

		if ($params = $request->input('params', []))
		{
			foreach ($params as $key => $val)
			{
				$params[$key] = is_array($val) ? array_filter($val) : $val;
			}

			$row->params = new Repository($params);
		}

		if (!$row->save())
		{
			return redirect()->back()->withError(trans('global.messages.save failed'));
		}

		// Rebuild the set
		$root = Page::rootNode();
		$row->rebuild($root->id);

		return redirect(route('page', ['uri' => $row->path]))->withSuccess(trans('global.messages.item ' . ($id ? 'created' : 'updated')));
	}

	/**
	 * Show the form for editing the specified page
	 *
	 * @param  int  $id
	 * @return View
	 */
	public function edit($id)
	{
		$row = Page::findOrFail($id);

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		// Fail if checked out not by 'me'
		if ($row->checked_out
		 && $row->checked_out <> auth()->user()->id)
		{
			return redirect(route('home'))->with('warning', trans('global.checked out'));
		}

		$parents = Page::query()
			->select('id', 'title', 'path', 'level')
			->where('level', '>', 0)
			->orderBy('path', 'asc')
			->get();

		return view('pages::site.edit', [
			'row'     => $row,
			'parents' => $parents
		]);
	}

	/**
	 * Remove the specified page
	 *
	 * @param  Request $request
	 * @return Response
	 */
	public function delete(Request $request)
	{
		// Incoming
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		foreach ($ids as $id)
		{
			// Delete the entry
			// Note: This is recursive and will also remove all descendents
			$row = Page::findOrFail($id);

			if ($row->trashed())
			{
				if (!$row->forceDelete())
				{
					$request->session()->flash('error', trans('global.messages.delete failed'));
					continue;
				}
			}
			elseif (!$row->delete())
			{
				$request->session()->flash('error', trans('global.messages.delete failed'));
				continue;
			}

			$success++;
		}

		if ($success)
		{
			$request->session()->flash('success', trans('global.messages.item deleted', ['number' => $success]));
		}

		return redirect(route('home'));
	}
}
