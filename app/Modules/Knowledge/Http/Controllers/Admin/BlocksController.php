<?php

namespace App\Modules\Knowledge\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Halcyon\Http\StatefulRequest;
use App\Modules\Knowledge\Models\Page;
use App\Modules\Knowledge\Models\Associations;

class BlocksController extends Controller
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
			'order'     => Page::$orderBy,
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

		if (!in_array($filters['order'], array_keys((new Page)->getAttributes())))
		{
			$filters['order'] = Page::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Page::$orderDir;
		}

		$query = Page::query()->where('snippet', '=', 1);

		$p = (new Page)->getTable();
		$a = (new Associations)->getTable();

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
			$query->join($a, $a . '.child_id', $p . '.id')
				->where($a . '.parent_id', '=', $filters['parent']);
		}

		$rows = $query
			->orderBy($p . '.' . $filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		$list = Page::tree($filters);
		/*$total = count($list);

		if ($filters['state'])
		{
			$list = array_filter($list, function($k) use ($filters)
			{
				return ($k->state == $filters['state']);
			});
		}

		$rows = array_slice($list, $filters['start'], $filters['limit']);

		$paginator = new \Illuminate\Pagination\LengthAwarePaginator($rows, $total, $filters['limit'], $filters['current']);
		$paginator->withPath(route('admin.knowledge.index'));*/

		return view('knowledge::admin.blocks.index', [
			'filters' => $filters,
			'rows'    => $rows,
		]);
	}

	/**
	 * Show the form for creating a new article
	 *
	 * @return  Response
	 */
	public function create()
	{
		app('request')->merge(['hidemainmenu' => 1]);

		$row = new Page();

		return view('knowledge::admin.pages.edit', [
			'row'   => $row
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

		$row = $id ? Associations::findOrFail($id) : new Associations;
		$row->access = $request->input('fields.access');
		$row->state  = $request->input('fields.state');
		$row->page_id = $request->input('fields.page_id');
		$row->parent_id = $parent_id;

		$page = $request->page;
		if (!$row->page_id)
		{
			$page = new Page;
		}

		$page->title = $request->input('page.title');
		$page->alias = $request->input('page.alias');
		$page->alias = $page->alias ?: $page->title;
		$page->content = $request->input('page.content');
		$page->snippet = $request->input('page.snippet', 0);
		$page->params = json_encode($request->input('params', []));

		if (!$page->save())
		{
			$error = $row->getError() ? $row->getError() : trans('messages.save failed');

			return redirect()->back()->withError($error);
		}

		if (!$row->save())
		{
			$error = $row->getError() ? $row->getError() : trans('messages.save failed');

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
		if (!$row->rebuildPath())
		{
			return redirect()->back()->withError($row->getError());
		}

		// Rebuild the paths of the entry's children
		if (!$row->rebuild($row->id, $row->lft, $row->level, $row->path))
		{
			return redirect()->back()->withError($row->getError());
		}

		return redirect(route('admin.pages.index'))->withSuccess(trans('messages.update success'));
	}

	/**
	 * Show the form for editing the specified entry
	 *
	 * @param   integer  $id
	 * @return  Response
	 */
	public function edit(Request $request, $id)
	{
		app('request')->merge(['hidemainmenu' => 1]);

		$page = Page::findOrFail($id);

		if ($fields = $request->old('fields'))
		{
			$page->fill($fields);
		}

		$row = new Associations;
		$parents = Page::tree();

		return view('knowledge::admin.pages.edit', [
			'row'  => $row,
			'tree' => $parents,
			'page' => $page,
		]);
	}

	/**
	 * Comment the specified entry
	 *
	 * @param   Request $request
	 * @return  Response
	 */
	public function update(Request $request, $id)
	{
		$request->validate([
			'fields.headline' => 'required'
		]);

		$fields = $request->input('fields');
		$fields['location'] = (string)$fields['location'];

		$row = Report::findOrFail($id);
		$row->fill($fields);

		if (!$row->save())
		{
			return redirect()->back()->withError(trans('messages.update failed'));
		}

		return $this->cancel()->withSuccess(trans('messages.update success'));
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
		return redirect(route('admin.knowledge.index'));
	}
}
