<?php

namespace App\Modules\Knowledge\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Halcyon\Http\StatefulRequest;
use App\Modules\Knowledge\Models\Page;
use App\Modules\Knowledge\Models\SnippetAssociation;
use App\Modules\Knowledge\Models\Associations;

class SnippetsController extends Controller
{
	/**
	 * Display a listing of articles
	 *
	 * @param  StatefulRequest $request
	 * @return View
	 */
	public function index(StatefulRequest $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			'parent'    => null,
			'state'     => null,
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			'order'     => 'lft',
			'order_dir' => Page::$orderDir,
		);

		$reset = false;
		$request = $request->mergeWithBase();
		foreach ($filters as $key => $default)
		{
			if ($key != 'page'
			 && $request->has($key) //&& session()->has('kb.filter_' . $key)
			 && $request->input($key) != session()->get('kb.snippets.filter_' . $key))
			{
				$reset = true;
			}
			$filters[$key] = $request->state('kb.snippets.filter_' . $key, $key, $default);
		}
		$filters['page'] = $reset ? 1 : $filters['page'];

		if (!in_array($filters['order'], array('lft', 'id', 'title', 'updated_at')))
		{
			$filters['order'] = Page::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Page::$orderDir;
		}

		$root = SnippetAssociation::rootNode();

		$query = Page::query();

		$p = (new Page)->getTable();
		$a = (new SnippetAssociation)->getTable();

		$query->join($a, $a . '.page_id', $p . '.id')
			->select($p . '.title', $p . '.state', $p . '.access', $p . '.snippet', $p . '.updated_at', $a . '.*')
			->where($p . '.snippet', '=', 1)
			->where($a . '.lft', '>=', $root->lft)
			->where($a . '.rgt', '<=', $root->rgt);

		if ($filters['search'])
		{
			$query->where(function($query) use ($filters, $p)
			{
				$query->where($p . '.title', 'like', '%' . $filters['search'] . '%')
					->orWhere($p . '.content', 'like', '%' . $filters['search'] . '%');
			});
		}

		if ($filters['parent'])
		{
			$parent = SnippetAssociation::find($filters['parent']);

			$query->where($a . '.lft', '>=', $parent->lft)
					->where($a . '.rgt', '<=', $parent->rgt);
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		$list = Page::query()
			->join($a, $a . '.page_id', $p . '.id')
			->select($p . '.*', $a . '.level', $a . '.lft', $a . '.rgt', $a . '.id AS assoc_id')
			->where('level', '<', 2)
			->orderBy('lft', 'asc')
			->get();

		// Preprocess the list of items to find ordering divisions.
		$ordering = array();
		foreach ($rows as $item)
		{
			$ordering[$item->parent_id][] = $item->id;
		}

		return view('knowledge::admin.snippets.index', [
			'filters' => $filters,
			'rows' => $rows,
			'tree' => $list,
			'ordering' => $ordering,
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 * 
	 * @return View
	 */
	public function create()
	{
		$row = new SnippetAssociation();
		$row->state = 1;

		$page = new Page;
		$page->state = 1;

		$p = (new Page)->getTable();
		$a = (new SnippetAssociation)->getTable();
		$parents = Page::query()
			->join($a, $a . '.page_id', $p . '.id')
			->select($p . '.title', $a . '.level', $a . '.lft', $a . '.rgt', $a . '.id', $a . '.path')
			->where($p . '.snippet', '=', 1)
			->orderBy('lft', 'asc')
			->get();

		return view('knowledge::admin.snippets.edit', [
			'row'  => $row,
			'tree' => $parents,
			'page' => $page
		]);
	}

	/**
	 * Copy the specified entry to the edit form to make a new entry.
	 * 
	 * @param  int $id
	 * @return View
	 */
	public function copy($id)
	{
		$row = SnippetAssociation::findOrFail($id);

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		$row->id = null;
		$row->path = $row->path . '_copy';
		$row->page->id = null;
		$row->page->title = $row->page->title . ' (copy)';
		$row->page->alias = $row->page->alias . '_copy';

		$p = (new Page)->getTable();
		$a = (new SnippetAssociation)->getTable();
		$parents = Page::query()
			->join($a, $a . '.page_id', $p . '.id')
			->select($p . '.title', $a . '.level', $a . '.lft', $a . '.rgt', $a . '.id', $a . '.path')
			->where($p . '.snippet', '=', 1)
			->orderBy('lft', 'asc')
			->get();

		return view('knowledge::admin.snippets.edit', [
			'row'  => $row,
			'tree' => $parents,
			'page' => $row->page,
		]);
	}

	/**
	 * Show the form for editing the specified resource.
	 * 
	 * @param  int  $id
	 * @return View
	 */
	public function edit($id)
	{
		$row = SnippetAssociation::findOrFail($id);

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		$p = (new Page)->getTable();
		$a = (new SnippetAssociation)->getTable();
		$parents = Page::query()
			->join($a, $a . '.page_id', $p . '.id')
			->select($p . '.title', $a . '.level', $a . '.lft', $a . '.rgt', $a . '.id', $a . '.path')
			->where($p . '.snippet', '=', 1)
			->orderBy('lft', 'asc')
			->get();

		return view('knowledge::admin.snippets.edit', [
			'row'  => $row,
			'tree' => $parents,
			'page' => $row->page,
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
			'page.content'  => 'required|string',
			'fields.access' => 'nullable|integer|min:1',
			'fields.state'  => 'nullable|integer|min:0',
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

		$row = $id ? SnippetAssociation::findOrFail($id) : new SnippetAssociation;
		$row->page_id = $request->input('fields.page_id');
		$orig_parent_id = $row->parent_id;
		$row->parent_id = $parent_id;

		$page = Page::find($row->page_id);
		if (!$row->page_id)
		{
			$page = new Page;
		}
		$page->snippet = 1;
		$page->access  = $request->input('fields.access');
		$page->state   = $request->input('fields.state');
		$page->title   = $request->input('page.title');
		$page->alias   = $request->input('page.alias');
		$page->alias   = $page->alias ?: $page->title;
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
				$page->params->set($key, $val);
			}
		}

		if (!$page->save())
		{
			return redirect()->back()->withError(trans('global.messages.save failed'));
		}

		$row->page_id = $page->id;
		$row->path = trim($row->parent->path . '/' . $page->alias, '/');

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

		return redirect(route('admin.knowledge.snippets'))->withSuccess(trans('global.messages.update success'));
	}

	/**
	 * Attach an entry to a parent entry
	 *
	 * @param   Request $request
	 * @return  Response
	 */
	public function attach(Request $request)
	{
		$request->validate([
			'parent_id' => 'required|integer',
			'page_id'   => 'required|integer'
		]);

		$row = new SnippetAssociation;
		$row->access    = 1;
		$row->state     = 1;
		$row->page_id   = $request->input('page_id');
		$row->parent_id = $request->input('parent_id');

		if (!$row->save())
		{
			return redirect()->back()->withError(trans('global.messages.update failed'));
		}

		return $this->cancel()->withSuccess(trans('global.messages.update success'));
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
			$row = SnippetAssociation::findOrFail(intval($id));

			$grows = Associations::query()
				->where('page_id', '=', $row->page_id)
				->get();

			foreach ($grows as $grow)
			{
				if ($grow->state == $state)
				{
					continue;
				}

				$grow->state = $state;

				if (!$grow->save())
				{
					$request->session()->flash('error', trans('global.messages.save failed'));
					continue;
				}
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
	 * Reorder entries
	 * 
	 * @param   int $id
	 * @param   Request $request
	 * @return  RedirectResponse
	 */
	public function reorder($id, Request $request)
	{
		// Get the element being moved
		$row = SnippetAssociation::findOrFail($id);
		$move = ($request->segment(4) == 'orderup') ? -1 : +1;

		if (!$row->move($move))
		{
			$request->session()->flash('error', trans('global.messages.move failed'));
		}

		// Redirect
		return $this->cancel();
	}

	/**
	 * Remove the specified entry
	 *
	 * @return  RedirectResponse
	 */
	public function destroy()
	{
		// Incoming
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		foreach ($ids as $id)
		{
			// Delete the entry
			// Note: This is recursive and will also remove all descendents
			$row = Report::findOrFail($id);

			if (!$row->delete())
			{
				$request->session()->flash('error', trans('global.messages.delete failed'));
				continue;
			}

			$success++;
		}

		if ($success)
		{
			$request->session()->flash('success', trans('global.messages.item deleted', $success));
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
		return redirect(route('admin.knowledge.snippets'));
	}
}
