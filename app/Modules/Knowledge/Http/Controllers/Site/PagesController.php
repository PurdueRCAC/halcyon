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
			if (!in_array($node->access, $levels))
			{
				abort(403, trans('knowledge::knowledge.permission denied'));
			}

			// Can non-managers view this article?
			if (!auth()->user() || !auth()->user()->can('manage knowledge'))
			{
				if (!$node->isPublished())
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

			app('pathway')->append(
				$page->headline,
				route('site.knowledge.page', ['uri' => $node->path])
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

	/**
	 * Display a listing of the resource.
	 * 
	 * @param  array  $nodes
	 * @param  string $alias
	 * @return void
	 */
	private function nestedset($nodes, $alias)
	{
		foreach ($nodes as $node)
		{
			$node->path = ($alias ? $alias . '/' : '') . $node->page->alias;

			if (!$node->save())
			{
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
	public function search(Request $request)
	{
		$filters = array(
			'search'    => null,
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			'order'     => Page::$orderBy,
			'order_dir' => Page::$orderDir,
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->input($key, $default);
		}

		if (!in_array($filters['order'], ['id', 'title', 'lft', 'rgt', 'path']))
		{
			$filters['order'] = 'lft';
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = 'asc';
		}

		$query = Page::query();

		$p = (new Page)->getTable();
		$a = (new Associations)->getTable();

		$query->join($a, $a . '.page_id', $p . '.id')
			->select($p . '.title', $p . '.snippet', $p . '.updated_at', $a . '.*'); //$p . '.state', $p . '.access', 
			//->select($p . '.title', $p . '.snippet', $p . '.updated_at', $a . '.*');

		if ($filters['search'])
		{
			$query->where(function($query) use ($filters, $p)
			{
				$query->where($p . '.title', 'like', '%' . $filters['search'] . '%')
					->orWhere($p . '.content', 'like', '%' . $filters['search'] . '%');
			});
		}

		$query->where($p . '.state', '=', 1);
		$query->where($p . '.access', '=', auth()->user() ? auth()->user()->getAuthorisedViewLevels() : [1]);

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		$root = Associations::rootNode();

		app('pathway')
			->append(
				trans('knowledge::knowledge.knowledge base'),
				route('site.knowledge.index')
			)
			->append(
				trans('knowledge::knowledge.search'),
				route('site.knowledge.search')
			);

		return view('knowledge::site.search', [
			'filters' => $filters,
			'root' => $root,
			'rows' => $rows
		]);
	}
}
