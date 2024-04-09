<?php

namespace App\Modules\Knowledge\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Halcyon\Http\Concerns\UsesFilters;
use App\Modules\Knowledge\Models\Page;
use App\Modules\Knowledge\Models\Association;
use App\Modules\Knowledge\Models\Associations;
use App\Modules\Knowledge\Models\SnippetAssociation;

class PagesController extends Controller
{
	use UsesFilters;

	/**
	 * Display a listing of articles
	 *
	 * @param  Request $request
	 * @return View
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = $this->getStatefulFilters($request, 'kb', [
			'search'    => null,
			'parent'    => null,
			'state'     => 'published',
			'access'    => null,
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			'order'     => Associations::$orderBy,
			'order_dir' => Associations::$orderDir,
			'level'     => 0,
		]);

		if (!in_array($filters['order'], ['id', 'lft', 'rgt', 'title', 'state', 'access', 'updated_at', 'created_at']))
		{
			$filters['order'] = 'lft';
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = 'asc';
		}

		$query = Page::query()->with('viewlevel');

		$p = (new Page)->getTable();
		$a = (new Associations)->getTable();

		$query->join($a, $a . '.page_id', $p . '.id')
			->select($p . '.title', $p . '.alias', $p . '.snippet', $p . '.updated_at', $a . '.*');

		$lists = Page::query()
			->join($a, $a . '.page_id', $p . '.id')
			->select($p . '.*', $a . '.level', $a . '.lft', $a . '.rgt', $a . '.id AS assoc_id')
			->where($a . '.level', '<', 2);

		if ($filters['search'])
		{
			$query->select(
				$p . '.title', $p . '.alias', $p . '.snippet', $p . '.updated_at', $a . '.*',
				DB::raw('IF(' . $p . '.title LIKE "' . $filters['search'] . '%", 20,
						IF(' . $p . '.title LIKE "%' . $filters['search'] . '%", 10, 0)
					)
					+ IF(' . $p . '.content LIKE "%' . $filters['search'] . '%", 5, 0)
					+ IF(' . $a . '.path    LIKE "%' . $filters['search'] . '%", 1, 0)
					AS `weight`')
				)
				->orderBy('weight', 'desc');
			$query->where(function($query) use ($filters, $p, $a)
			{
				$query->where($p . '.title', 'like', $filters['search'] . '%')
					->orWhere($p . '.title', 'like', '%' . $filters['search'] . '%')
					->orWhere($p . '.content', 'like', '%' . $filters['search'] . '%')
					->orWhere($a . '.path', 'like', '%' . $filters['search'] . '%');
			});
		}

		if ($filters['level'] > 0)
		{
			$query->where($a . '.level', '<=', $filters['level']);
		}

		if ($filters['parent'])
		{
			$parent = Associations::find($filters['parent']);

			$query->where($a . '.lft', '>=', $parent->lft)
					->where($a . '.rgt', '<=', $parent->rgt);
		}

		if ($filters['state'] == 'published')
		{
			$query->where($a . '.state', '=', 1);
			$lists->where($a . '.state', '=', 1);
		}
		elseif ($filters['state'] == 'archived')
		{
			$query->where($a . '.state', '=', 2);
			$lists->where($a . '.state', '=', 2);
		}
		elseif ($filters['state'] == 'unpublished')
		{
			$query->where($a . '.state', '=', 0);
			$lists->where($a . '.state', '=', 0);
		}
		elseif ($filters['state'] == 'trashed')
		{
			$query->onlyTrashed();
			$lists->onlyTrashed();
		}
		else
		{
			$query->withTrashed();
			$lists->withTrashed();
		}

		if ($filters['access'] > 0)
		{
			$query->where($a . '.access', '=', (int)$filters['access']);
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		$list = $lists
			->orderBy('lft', 'asc')
			->get();

		// Preprocess the list of items to find ordering divisions.
		$ordering = array();

		$prev = null;
		if ($filters['page'] > 1)
		{
			$prev = $query
				->orderBy($filters['order'], $filters['order_dir'])
				->limit(1)
				->offset($filters['limit'] * ($filters['page'] - 1) - 1)
				->first();
		}
		$next = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->limit(1)
			->offset($filters['limit'] * $filters['page'])
			->first();

		if ($prev)
		{
			$ordering[$prev->parent_id][] = $prev->id;
		}
		foreach ($rows as $item)
		{
			$ordering[$item->parent_id][] = $item->id;
		}
		if ($next)
		{
			$ordering[$next->parent_id][] = $next->id;
		}

		return view('knowledge::admin.pages.index', [
			'filters'  => $filters,
			'rows'     => $rows,
			'tree'     => $list,
			'ordering' => $ordering,
			//'paginator' => $paginator,
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 * 
	 * @param  Request $request
	 * @return View
	 */
	public function create(Request $request)
	{
		$row = new Associations();
		$row->state = 1;

		$page = new Page;
		$page->state = 1;

		$parents = Page::tree();

		$type = $request->input('type');
		if ($type == 'separator')
		{
			$page->alias = '-separator-';
			$page->title = trans('knowledge::knowledge.type separator');
		}

		return view('knowledge::admin.pages.edit', [
			'row'  => $row,
			'tree' => $parents,
			'page' => $page,
			//'type' => $request->input('type'),
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 * 
	 * @param  Request $request
	 * @return View
	 */
	public function select(Request $request)
	{
		$parent_id = $request->input('parent');

		$parents = Page::tree();

		$p = (new Page)->getTable();
		$a = (new SnippetAssociation)->getTable();

		$snippets = Page::query()
			->join($a, $a . '.page_id', $p . '.id')
			->select($p . '.title', $a . '.level', $a . '.lft', $a . '.rgt', $a . '.id', $a . '.path', $a . '.parent_id', $a . '.page_id')
			->where($p . '.snippet', '=', 1)
			->orderBy('lft', 'asc')
			->get();

		return view('knowledge::admin.pages.select', [
			'parent_id' => $parent_id,
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

		return $this->cancel()->withSuccess(trans('knowledge::knowledge.snippets attached'));
	}

	/**
	 * Copy the specified entry to the edit form to make a new entry.
	 *
	 * @param  Request $request
	 * @param  int $id
	 * @return View
	 */
	public function copy(Request $request, $id)
	{
		$row = Associations::findOrFail($id);

		if ($fields = $request->old('fields'))
		{
			$row->fill($fields);
		}

		$row->id = null;
		$row->path = $row->path . '_copy';
		$row->page->id = null;
		$row->page->title = $row->page->title . ' (copy)';
		$row->page->alias = $row->page->alias . '_copy';

		$parents = Page::tree();

		return view('knowledge::admin.pages.edit', [
			'row'  => $row,
			'tree' => $parents,
			'page' => $row->page,
		]);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  Request $request
	 * @param  int $id
	 * @return View
	 */
	public function edit(Request $request, $id)
	{
		$row = Associations::findOrFail($id);

		if ($fields = $request->old('fields'))
		{
			$row->fill($fields);
		}

		$parents = Page::tree();

		return view('knowledge::admin.pages.edit', [
			'row'  => $row,
			'tree' => $parents,
			'page' => $row->page,
			//'type' => $row->page->isSeparator() ? 'separator' : 'article',
		]);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param   Request $request
	 * @return  RedirectResponse
	 */
	public function store(Request $request)
	{
		$rules = [
			'page.title'    => 'required|string|max:255',
			'page.content'  => 'nullable|string',
			'fields.access' => 'nullable|integer|min:1',
			'fields.state'  => 'nullable|integer',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$id = $request->input('id');
		$parent_id = $request->input('fields.parent_id');

		$row = Associations::findOrNew($id);
		if ($request->has('fields.access'))
		{
			$row->access = $request->input('fields.access');
		}
		if ($request->has('fields.state'))
		{
			$row->state  = $request->input('fields.state');
		}
		$row->page_id = $request->input('fields.page_id');
		$orig_parent_id = $row->parent_id;
		$row->parent_id = $parent_id;

		$page = Page::findOrNew($row->page_id);

		$original = $page->alias;

		$page->title = $request->input('page.title');
		$page->alias = $request->input('page.alias');
		$page->alias = $page->alias ?: $page->title;
		$page->content = $request->input('page.content');
		if ($params = $request->input('params', []))
		{
			foreach ($params as $key => $val)
			{
				if ($key == 'variables')
				{
					$vars = array();
					foreach ($val as $opts)
					{
						if (!$opts['key'])
						{
							continue;
						}
						$vars[$opts['key']] = $opts['value'];
					}
					$val = $vars;
				}
				if ($key == 'tags')
				{
					$val = array_filter($val);
				}
				$page->params->set($key, $val);
			}
		}
		$page->metakey = $request->input('page.metakey');
		$page->metadesc = $request->input('page.metadesc');

		if (!$page->save())
		{
			return redirect()->back()->withError(trans('global.messages.save failed'));
		}

		$tags = explode(',', $page->metakey);
		$tags = array_map('trim', $tags);
		$page->setTags($tags);

		$row->page_id = $page->id;
		$row->path = '';
		if ($row->parent)
		{
			$row->path = trim($row->parent->path . '/' . $page->alias, '/');
		}

		if (!$row->save())
		{
			return redirect()->back()->withError(trans('global.messages.save failed'));
		}

		if ($id && $parent_id != $orig_parent_id)
		{
			if (!$row->moveByReference($row->parent_id, 'last-child', $row->id))
			{
				return redirect()->back()->withError(trans('global.messages.move failed'));
			}
		}

		// Rebuild the paths of the entry's children
		if (!$row->rebuild($row->id, $row->lft, $row->level, $row->path))
		{
			return redirect()->back()->withError(trans('knowledge::knowledge.errors.rebuild failed'));
		}

		// Update all instances of this snippet
		if ($page->alias != $original)
		{
			$instances = Associations::query()
				->where('page_id', '=', $row->page_id)
				->where('id', '!=', $row->id)
				->get();

			foreach ($instances as $inst)
			{
				$inst->path = trim($inst->parent->path . '/' . $page->alias, '/');
				$inst->save();

				$inst->rebuild($inst->id, $inst->lft, $inst->level, $inst->path);
			}
		}

		return redirect(route('admin.knowledge.index'))->withSuccess(trans('global.messages.item ' . ($id ? 'updated' : 'created')));
	}

	/**
	 * Rebuild the tree
	 * 
	 * @return  RedirectResponse
	 */
	public function rebuild()
	{
		$row = Associations::rootNode();

		// Rebuild the paths of the entry's children
		if (!$row->rebuild($row->id, $row->lft, $row->level, $row->path))
		{
			return redirect()->back()->withError(trans('knowledge::knowledge.errors.rebuild failed'));
		}

		return redirect(route('admin.knowledge.index'))->withSuccess(trans('knowledge::knowledge.tree rebuilt'));
	}

	/**
	 * Reorder entries
	 * 
	 * @param   int $id
	 * @param   Request $request
	 * @return  RedirectResponse
	 */
	public function reorder($id, Request $request)
	{
		// Get the element being moved
		$row = Associations::findOrFail($id);
		$move = ($request->segment(3) == 'orderup') ? -1 : +1;

		if (!$row->move($move))
		{
			$request->session()->flash('error', trans('global.messages.move failed'));
		}

		// Redirect
		return $this->cancel();
	}

	/**
	 * Sets the state of one or more entries
	 * 
	 * @param   Request $request
	 * @param   int $id
	 * @return  RedirectResponse
	 */
	public function state(Request $request, $id = null)
	{
		$action = $request->segment(3);
		$state  = $action == 'publish' ? 1 : 0;

		// Incoming
		$ids = $request->input('id', array($id));
		$ids = (!is_array($ids) ? array($ids) : $ids);

		// Check for an ID
		if (count($ids) < 1)
		{
			$request->session()->flash('warning', trans('knowledge::knowledge.error.select to ' . ($state ? 'publish' : 'unpublish')));
			return $this->cancel();
		}

		$success = 0;

		// Comment record(s)
		foreach ($ids as $id)
		{
			$row = Associations::findOrFail(intval($id));

			if ($row->state == $state)
			{
				continue;
			}

			$row->state = $state;

			if (!$row->save())
			{
				$request->session()->flash('error', trans('global.messages.save failed'));
				continue;
			}

			$success++;
		}

		// Set message
		if ($success)
		{
			$msg = $state
				? 'global.messages.items published'
				: 'global.messages.items unpublished';

			$request->session()->flash('success', trans($msg, ['count' => $success]));
		}

		return $this->cancel();
	}

	/**
	 * Remove the specified entry
	 *
	 * @param   Request $request
	 * @return  RedirectResponse
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
			$row = Associations::find($id);

			if (!$row)
			{
				continue;
			}

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

		return $this->cancel();
	}

	/**
	 * Return to default page
	 *
	 * @return  RedirectResponse
	 */
	public function cancel()
	{
		return redirect(route('admin.knowledge.index'));
	}
}
