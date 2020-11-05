<?php

namespace App\Modules\Knowledge\Http\Controllers\Site;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Knowledge\Models\Page;
use App\Modules\Knowledge\Models\Associations;

class PagesController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * @return Response
	 */
	/**
	 * Display a page
	 *
	 * @return  Response
	 */
	public function index(Request $request)
	{
		$path = trim($request->path(), '/');

		// Load the entire path
		$pages = Associations::stackByPath($path);

		if (!$pages || count($pages) == 0)
		{
			abort(404, trans('knowledge::knowledge.article not found'));
		}

		$levels = auth()->user()
			? auth()->user()->getAuthorisedViewLevels()
			: array(1);

		// We want to go through every article in the path, starting with the root
		// node and see if the user has proper permissions to view the article.
		// If a parent isn't accessible, then the child shouldn't be either.
		app('pathway')->append(
			trans('knowledge::knowledge.knowledge base'),
			route('site.knowledge.index')
		);

		$uri = '';
		$prev = null;
		foreach ($pages as $node)
		{
			$page = $node->page;

			// Ensure we have an article
			if (!$page)
			{
				abort(404, trans('knowledge::knowledge.article not found'));
			}

			// Does the user have access to the article?
			if (!in_array($page->access, $levels))
			{
				abort(403, trans('knowledge::knowledge.permission denied'));
			}

			// Can non-managers view this article?
			if (!auth()->user() || !auth()->user()->can('manage knowledge'))
			{
				if (!$page->isPublished())
				{
					abort(404, trans('knowledge::knowledge.article not found'));
				}
			}

			if ($page->main)
			{
				continue;
			}

			if ($prev)
			{
				$page->variables->merge($prev->variables);
			}

			//$uri .= ($uri ? '/' : '') . $page->alias;

			app('pathway')->append(
				$page->headline,
				route('site.knowledge.page', ['uri' => $node->path])//$uri])
			);

			$prev = $page;
		}

		$root = Associations::rootNode();
		$path = explode('/', $path);
		array_shift($path);

		/*$root = Page::rootNode();
		$children = $root->children()->orderBy('ordering', 'asc')->get();
		$this->nestedset($children, 1);

		$root = Associations::rootNode();
		$children = $root->children()->orderBy('lft', 'asc')->get();
		$this->nestedset($children, $root->path);*/

		return view('knowledge::site.index', [
			'node' => $node,
			'pages' => $pages,
			'path' => $path,
			'root' => $root,
		]);
	}

	private function nestedset($nodes, $alias)
	{
		foreach ($nodes as $node)
		{
			$node->path = ($alias ? $alias . '/' : '') . $node->page->alias;

			if (!$node->save())
			{
				print_r($assoc);
				die('here');
			}

			$children = $node->children()->orderBy('lft', 'asc')->get();

			$this->nestedset($children, $node->path);
		}
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
