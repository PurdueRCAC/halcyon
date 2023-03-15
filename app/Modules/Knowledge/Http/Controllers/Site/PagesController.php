<?php

namespace App\Modules\Knowledge\Http\Controllers\Site;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Modules\Knowledge\Models\Page;
use App\Modules\Knowledge\Models\SnippetAssociation;
use App\Modules\Knowledge\Models\Associations;
use App\Modules\Knowledge\Events\PageMetadata;
use App\Modules\Knowledge\Helpers\Diff;
use App\Modules\Knowledge\Helpers\Diff\TableDiffFormatter;
//use App\Modules\Knowledge\Helpers\Diff\DivDiffFormatter;
use App\Modules\History\Models\History;

class PagesController extends Controller
{
	/**
	 * Display a page
	 *
	 * @return  View
	 */
	public function index(Request $request): View
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
		$guide = '';
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
				/*if ($node->isArchived())
				{
					abort(410, trans('knowledge::knowledge.article no longer available'));
				}*/

				if (!$node->isPublished() && !$node->isArchived())
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

				$page->mergeVariables($prev->page->variables);
			}

			app('pathway')->append(
				$page->headline,
				route('site.knowledge.page', ['uri' => $node->path])
			);

			if ($i == 1)
			{
				$guide = $page->headline;
				$parent = $node->id;
			}

			$prev = $node;
		}

		event($event = new PageMetadata($node->page));

		$node->page = $event->page;

		$path = explode('/', $path);
		array_shift($path);
		$node->guide = $guide;

		$all = $request->input('all');

		$view = 'index';
		if (auth()->user()
		 && auth()->user()->can('edit knowledge')
		 && $request->input('action') == 'history')
		{
			$view = 'history';
		}

		return view('knowledge::site.' . $view, [
			'node' => $node,
			'pages' => $pages,
			'path' => $path,
			'root' => $root,
			'parent' => $parent,
			'all' => $all,
		]);
	}

	/**
	 * Restore to a specific version
	 * 
	 * @param  Request $request
	 * @return RedirectResponse
	 */
	public function restore(Request $request)
	{
		$dest = $request->input('revision');

		$node = Association::findOrFail($request->input('node'));
		$page = $node->page;

		$revisions = $page->history()
			->orderBy('created_at', 'desc')
			->get();

		foreach ($revisions as $revision)
		{
			if (isset($revision->new->title))
			{
				$page->title = $revision->new->title;
			}
			if (isset($revision->new->alias))
			{
				$page->alias = $revision->new->alias;
			}
			if (isset($revision->new->content))
			{
				$page->content = $revision->new->content;
			}
			if (isset($revision->new->params))
			{
				$params = json_decode($revision->new->params, true);
				foreach (['show_title', 'show_toc', 'variables', 'tags'] as $p)
				{
					if (isset($params[$p]))
					{
						$page->params->{$p} = $params[$p];
					}
				}
			}

			if ($revision->id == $dest)
			{
				$page->save();

				$row->path = '';
				if ($row->parent)
				{
					$row->path = trim($row->parent->path . '/' . $page->alias, '/');
				}

				if (!$row->save())
				{
					return redirect()->back()->withError(trans('global.messages.save failed'));
				}

				// Rebuild the paths of the entry's children
				if (!$row->rebuild($row->id, $row->lft, $row->level, $row->path))
				{
					return redirect()->back()->withError(trans('knowledge::knowledge.rebuild failed'));
				}

				break;
			}
		}

		return redirect(route('site.knowledge.page', ['uri' => $node->path]))->withSuccess(trans('knowledge::knowledge.page restored'));
	}

	/**
	 * Display a listing of the resource.
	 * 
	 * @param  array  $nodes
	 * @param  string $alias
	 * @return void
	 */
	private function nestedset($nodes, $alias): void
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
	 * Display search results.
	 *
	 * @param  Request $request
	 * @return View
	 */
	public function search(Request $request): View
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

		//$query = Page::query();

		$p = (new Page)->getTable();
		$a = (new Associations)->getTable();

		//$query->join($a, $a . '.page_id', $p . '.id')
		$query = Associations::query()
			->join($p, $a . '.page_id', $p . '.id')
			->select($p . '.title', $p . '.content', $p . '.params', $p . '.snippet', $p . '.updated_at', $a . '.*');

		if ($filters['search'])
		{
			$filters['order'] = 'weight';

			$query->select($p . '.title', $p . '.content', $p . '.params', $p . '.snippet', $p . '.updated_at', $a . '.*',
				DB::raw("IF(" . $a . ".path LIKE '%" . $filters['search'] . "', 10, 0) +
					IF(" . $p . ".title LIKE '" . $filters['search'] . "%', 20, 
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

		if ($filters['parent'] > 1)
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
		else
		{
			// Filter out retired trees
			$retired = Associations::query()
				->where('parent_id', '=', 1)
				->where('state', '=', 2)
				->get();

			foreach ($retired as $re)
			{
				$query->where(function($query) use ($a, $re)
				{
					$query->where($a . '.lft', '<', $re->lft)
						->orWhere($a . '.lft', '>', $re->rgt);
				});
			}
		}

		$query->where($a . '.state', '=', 1);
		$query->where($a . '.access', '=', $levels);

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page'])
			->appends([
				'search' => $filters['search'],
				'parent' => $filters['parent']
			]);
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

	/**
	 * Show the form for creating a new resource.
	 *
	 * @param  Request $request
	 * @return View
	 */
	public function create(Request $request): View
	{
		$root = Associations::rootNode();

		$parent_id = $request->input('parent');
		$node = Associations::find($parent_id);

		$row = new Associations();
		$row->state = 1;
		$row->parent_id = $parent_id;

		$page = new Page;
		$page->state = 1;

		$parents = Page::tree();

		return view('knowledge::site.edit', [
			'root' => $root,
			'node' => $node,
			'row'  => $row,
			'tree' => $parents,
			'page' => $page
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 * 
	 * @param  Request $request
	 * @return View
	 */
	public function select(Request $request): View
	{
		$root = Associations::rootNode();

		$parent_id = $request->input('parent');
		$node = Associations::find($parent_id);

		$parents = Page::tree();

		$p = (new Page)->getTable();
		$a = (new SnippetAssociation)->getTable();

		$snippets = Page::query()
			->join($a, $a . '.page_id', $p . '.id')
			->select($p . '.title', $a . '.level', $a . '.lft', $a . '.rgt', $a . '.id', $a . '.path', $a . '.parent_id', $a . '.page_id')
			->where($p . '.snippet', '=', 1)
			->orderBy('lft', 'asc')
			->get();

		return view('knowledge::site.select', [
			'root' => $root,
			'node' => $node,
			//'parent_id' => $parent_id,
			'parents'   => $parents,
			'snippets'  => $snippets,
		]);
	}

	/**
	 * Comment the specified entry
	 *
	 * @param   Request $request
	 * @return  RedirectResponse
	 */
	public function attach(Request $request)
	{
		$request->validate([
			'parent_id' => 'required|integer',
			'snippets' => 'required|array'
		]);

		$parent_id = $request->input('parent_id');
		$snippets = $request->input('snippets');
		$parents = array();

		$page = Associations::findOrFail($parent_id);

		foreach ($snippets as $parent => $snips)
		{
			foreach ($snips as $id => $snippet)
			{
				if (!isset($snippet['page_id']))
				{
					continue;
				}

				$row = new Associations;
				$row->access    = $snippet['access'];
				$row->state     = $snippet['state'];
				$row->page_id   = $snippet['page_id'];
				$row->parent_id = $parent_id;
				if (isset($parents[$parent]))
				{
					$row->parent_id = $parents[$parent];
				}

				if (!$row->save())
				{
					return redirect()->back()->withError(trans('knowledge::knowledge.error.failed to attach snippets'));
				}

				$parents[$id] = $row->id;
			}
		}

		return redirect(route('site.knowledge.page', ['uri' => $page->path]));//->withSuccess(trans('knowledge::knowledge.snippets attached'));
	}

	/**
	 * Remove the specified entry
	 *
	 * @param   Request $request
	 * @param   int  $id
	 * @return  RedirectResponse
	 */
	public function delete(Request $request, $id = null)
	{
		// Incoming
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);
		if ($id)
		{
			$ids[] = $id;
		}

		$success = 0;

		foreach ($ids as $id)
		{
			// Delete the entry
			// Note: This is recursive and will also remove all descendents
			$row = Associations::findOrFail($id);

			if (!$row->delete())
			{
				$request->session()->flash('error', trans('global.messages.delete failed'));
				continue;
			}

			$success++;
		}

		if ($success)
		{
			$request->session()->flash('success', trans('global.messages.item deleted', ['count' => $success]));
		}

		return redirect(route('site.knowledge.index'));
	}
}
