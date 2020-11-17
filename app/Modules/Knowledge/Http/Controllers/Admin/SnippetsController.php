<?php

namespace App\Modules\Knowledge\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Halcyon\Http\StatefulRequest;
use App\Modules\Knowledge\Models\Page;
use App\Modules\Knowledge\Models\SnippetAssociation;

class SnippetsController extends Controller
{
	/**
	 * Display a listing of articles
	 *
	 * @return Response
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

		$refresh = false;
		foreach ($filters as $key => $default)
		{
			if (!$refresh && $key != 'page')
			{
				$refresh = (session()->get($key, $default) != $request->input('search', $default));
			}
			$filters[$key] = $request->state('kb.snippets.filter_' . $key, $key, $default);
		}

		if ($refresh)
		{
			$filters['page'] = 1;
		}

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
		/*$tree = Page::query()
			->join($a, $a . '.page_id', $p . '.id')
			->select($p . '.title', $a . '.level', $a . '.lft', $a . '.rgt', $a . '.id', $a . '.path')
			->orderBy('lft', 'asc')
			->get();


		$rowids = Page::query()
			->join($a, $a . '.page_id', $p . '.id')
			->select($p . '.id')
			->where($p . '.snippet', '=', 1)
			->where($a . '.lft', '>=', $root->lft)
			->where($a . '.rgt', '<=', $root->rgt)
			->get()
			->pluck('id')
			->toArray();

		$pages = Page::query()
			->select($p . '.*')
			->where($p . '.snippet', '=', 1)
			->whereNotIn($p . '.id', $rowids)
			->orderBy($p . '.alias', 'asc')
			->orderBy($p . '.title', 'asc')
			->get();*/
		// Preprocess the list of items to find ordering divisions.
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
	 * @return Response
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
	 * Show the form for editing the specified resource.
	 * @return Response
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
	 * @return  Response
	 */
	public function store(Request $request)
	{
		$request->validate([
			'page.title'   => 'required',
			'page.content' => 'required',
			'fields.access' => 'nullable|min:1',
			'fields.state'  => 'nullable|min:1',
		]);

		$id = $request->input('id');
		$parent_id = $request->input('fields.parent_id');

		$row = $id ? SnippetAssociation::findOrFail($id) : new SnippetAssociation;
		//$row->access = $request->input('fields.access');
		//$row->state  = $request->input('fields.state');
		$row->page_id = $request->input('fields.page_id');
		$row->parent_id = $parent_id;

		$page = Page::find($row->page_id);
		if (!$row->page_id)
		{
			$page = new Page;
		}
		$page->snippet = 1;
		$page->access = $request->input('fields.access');
		$page->state  = $request->input('fields.state');
		$page->title = $request->input('page.title');
		$page->alias = $request->input('page.alias');
		$page->alias = $page->alias ?: $page->title;
		$page->content = $request->input('page.content');
		if ($params = $request->input('params', []))
		{
			foreach ($params as $key => $val)
			{
				//$params[$key] = is_array($val) ? array_filter($val) : $val;
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

			//$page->params = new Repository($params);
		}
		//$page->snippet = $request->input('page.snippet', 0);
		//$page->params = json_encode($request->input('params', []));

		if (!$page->save())
		{
			$error = $row->getError() ? $row->getError() : trans('global.messages.save failed');

			return redirect()->back()->withError($error);
		}

		$row->page_id = $page->id;
		$row->path = trim($row->parent->path . '/' . $page->alias, '/');

		if (!$row->save())
		{
			$error = $row->getError() ? $row->getError() : trans('global.messages.save failed');

			return redirect()->back()->withError($error);
		}

		if ($id && $parent_id != $row->parent_id)
		{
			if (!$row->moveByReference($row->parent_id, 'last-child', $row->id))
			{
				return redirect()->back()->withError($row->getError());
			}
		}

		// Rebuild the paths of the entry's path
		/*if (!$row->rebuildPath())
		{
			return redirect()->back()->withError($row->getError());
		}*/

		// Rebuild the paths of the entry's children
		if (!$row->rebuild($row->id, $row->lft, $row->level, $row->path))
		{
			return redirect()->back()->withError($row->getError());
		}

		return redirect(route('admin.knowledge.snippets'))->withSuccess(trans('global.messages.update success'));
	}

	/**
	 * Comment the specified entry
	 *
	 * @param   Request $request
	 * @return  Response
	 */
	public function attach(Request $request)
	{
		$request->validate([
			'parent_id' => 'required',
			'page_id' => 'required'
		]);

		$row = new SnippetAssociation;
		$row->access = 1;
		$row->state  = 1;
		$row->page_id = $request->input('page_id');
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
	 * @return  void
	 */
	public function state(Request $request, $id)
	{
		$action = $request->segment(count($request->segments()) - 1);
		$state  = $action == 'publish' ? 1 : 0;

		// Incoming
		$ids = $request->input('id', array($id));
		$ids = (!is_array($ids) ? array($ids) : $ids);

		// Check for an ID
		if (count($ids) < 1)
		{
			$request->session()->flash('warning', trans($state ? 'knowledge::knowledge.select to publish' : 'knowledge::knowledge.select to unpublish'));
			return $this->cancel();
		}

		$success = 0;

		// Comment record(s)
		foreach ($ids as $id)
		{
			$row = Report::findOrFail(intval($id));

			if ($row->published == $state)
			{
				continue;
			}

			// Don't update last modified timestamp for state changes
			$row->timestamps = false;

			$row->published = $state;

			if (!$row->save())
			{
				$request->session()->flash('error', $row->getError());
				continue;
			}

			$success++;
		}

		// Set message
		if ($success)
		{
			$msg = $state
				? 'knowledge::knowledge.items published'
				: 'knowledge::knowledge.items unpublished';

			$request->session()->flash('success', trans($msg, ['count' => $success]));
		}

		return $this->cancel();
	}

	/**
	 * Reorder entries
	 * 
	 * @return  void
	 */
	public function reorder($id, Request $request)
	{
		// Get the element being moved
		$row = SnippetAssociation::findOrFail($id);
		$move = ($request->segment(4) == 'orderup') ? -1 : +1;

		if (!$row->move($move))
		{
			$request->session()->flash('error', $row->getError());
		}

		// Redirect
		return $this->cancel();
	}

	/**
	 * Remove the specified entry
	 *
	 * @return  Response
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
				$request->session()->flash('error', $row->getError());
				continue;
			}

			$success++;
		}

		if ($success)
		{
			$request->session()->flash('success', trans('messages.item deleted', $success));
		}

		return $this->cancel();
	}

	/**
	 * Return to default page
	 *
	 * @return  Response
	 */
	public function cancel()
	{
		return redirect(route('admin.knowledge.snippets'));
	}
}
