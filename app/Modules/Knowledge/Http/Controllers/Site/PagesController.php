<?php

namespace App\Modules\Knowledge\Http\Controllers\Site;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
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

		if (substr($path, -4) == '/all')
		{
			$path = substr($path, 0, -4);
			$request->merge(['all' => 1]);
		}

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

		$root = Associations::rootNode();

		$uri = '';
		$prev = null;
		$parent = $root->id;
		foreach ($pages as $i => $node)
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

			if ($prev && $prev->page)
			{
				if ($prev->isArchived() && !$node->isArchived())
				{
					$node->state = 2;
				}
				$page->variables->merge($prev->page->variables);
			}

			app('pathway')->append(
				$page->headline,
				route('site.knowledge.page', ['uri' => $node->path])
			);

			if ($i == 1)
			{
				$parent = $node->id;
			}

			$prev = $node;
		}

		$path = explode('/', $path);
		array_shift($path);

		return view('knowledge::site.index', [
			'node' => $node,
			'pages' => $pages,
			'path' => $path,
			'root' => $root,
			'parent' => $parent,
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

			$node->save();

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
			'parent'    => 0,
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

		$path = array();
		$levels = auth()->user()
			? auth()->user()->getAuthorisedViewLevels()
			: array(1);

		$query = Page::query();

		$p = (new Page)->getTable();
		$a = (new Associations)->getTable();

		$query->join($a, $a . '.page_id', $p . '.id')
			->select($p . '.title', $p . '.content', $p . '.params', $p . '.snippet', $p . '.updated_at', $a . '.*');

		if ($filters['search'])
		{
			$filters['order'] = 'weight';

			$query->select($p . '.title', $p . '.content', $p . '.params', $p . '.snippet', $p . '.updated_at', $a . '.*',
				DB::raw("IF(" . $p . ".title LIKE '" . $filters['search'] . "%', 20, 
						IF(" . $p . ".title LIKE '%" . $filters['search'] . "%', 10, 0)
					)
					+ IF(" . $p . ".content LIKE '%" . $filters['search'] . "%', 5, 0)
				AS `weight`"));

			$query->where(function($query) use ($filters, $p)
			{
				$query->where($p . '.title', 'like', $filters['search'] . '%')
					->orWhere($p . '.title', 'like', '%' . $filters['search'] . '%')
					->orWhere($p . '.content', 'like', '%' . $filters['search'] . '%');
			});
		}

		if ($filters['parent'])
		{
			$parent = Associations::find($filters['parent']);

			// Ensure we have an article
			if (!$parent)
			{
				abort(404, trans('knowledge::knowledge.article not found'));
			}

			// Does the user have access to the article?
			if (!in_array($parent->access, $levels))
			{
				abort(403, trans('knowledge::knowledge.permission denied'));
			}

			// Can non-managers view this article?
			if (!auth()->user() || !auth()->user()->can('manage knowledge'))
			{
				if (!$parent->isPublished())
				{
					abort(404, trans('knowledge::knowledge.article not found'));
				}
			}

			$path = explode('/', $parent->path);

			$query->where($a . '.lft', '>=', $parent->lft)
					->where($a . '.rgt', '<=', $parent->rgt);
		}

		$query->where($p . '.state', '=', 1);
		$query->where($p . '.access', '=', $levels);

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);
			/*->map(function ($row) use ($filters)
			{
				$row->title = preg_replace('/(' . $filters['search'] . ')/i', "<strong class=\"highlight\">$1</strong>", $row->title);
				$row->content = preg_replace('/(' . $filters['search'] . ')/i', "<strong class=\"highlight\">$1</strong>", $row->title);
				return $row;
			});*/

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
			'rows' => $rows,
			'path' => $path,
		]);
	}
}
