<?php

namespace App\Modules\Knowledge\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Halcyon\Http\StatefulRequest;
use App\Modules\Knowledge\Models\Page;
use App\Modules\Knowledge\Models\Associations;

class PagesController extends Controller
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
			'access'    => null,
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			'order'     => Page::$orderBy,
			'order_dir' => Page::$orderDir,
		);
		//$filters['start'] = ($filters['limit'] * $filters['page']) - $filters['limit'];

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->state('kb.filter_' . $key, $key, $default);
		}

		if (!in_array($filters['order'], array_keys((new Page)->getAttributes())))
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
			->select($p . '.title', $p . '.snippet', $p . '.updated_at', $a . '.*');

		if ($filters['search'])
		{
			$query->where(function($query) use ($filters)
			{
				$query->where($p . '.title', 'like', '%' . $filters['search'] . '%')
					->orWhere($p . '.content', 'like', '%' . $filters['search'] . '%');
			});
		}

		if ($filters['parent'])
		{
			$parent = Associations::find($filters['parent']);

			$query->where($a . '.lft', '>', $parent->lft)
					->where($a . '.rgt', '<', $parent->rgt);
		}

		if ($filters['state'] == 'published')
		{
			$query->where($p . '.state', '=', 1);
		}
		elseif ($filters['state'] == 'unpublished')
		{
			$query->where($p . '.state', '=', 0);
		}
		elseif ($filters['state'] == 'trashed')
		{
			$query->onlyTrashed(); //->whereNotNull($page . '.deleted_at');
		}
		else
		{
			$query->withTrashed();
		}

		if ($filters['access'] > 0)
		{
			$query->where($p . '.access', '=', (int)$filters['access']);
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		/*$list = Page::tree($filters);
		$total = count($list);

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

		$list = Page::query()
			->join($a, $a . '.page_id', $p . '.id')
			->select($p . '.*', $a . '.level', $a . '.lft', $a . '.rgt', $a . '.id AS assoc_id')
			->where('level', '<', 2)
			->orderBy('lft', 'asc')
			->get();

		return view('knowledge::admin.pages.index', [
			'filters' => $filters,
			'rows'    => $rows,
			'tree' => $list,
			//'paginator' => $paginator,
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 * @return Response
	 */
	public function create()
	{
		app('request')->merge(['hidemainmenu' => 1]);

		$row = new Associations();

		$parents = Page::tree();

		return view('knowledge::admin.pages.edit', [
			'row'     => $row,
			'tree' => $parents
		]);
	}

	/**
	 * Show the form for editing the specified resource.
	 * @return Response
	 */
	public function edit($id)
	{
		app('request')->merge(['hidemainmenu' => 1]);

		$row = Associations::findOrFail($id);

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		$parents = Page::tree();

		return view('knowledge::admin.pages.edit', [
			'row'     => $row,
			'tree' => $parents
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
			$row = Associations::findOrFail(intval($id));

			if ($row->state == $state)
			{
				continue;
			}

			$row->state = $state;

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
			$row = Associations::findOrFail($id);

			if (!$row->delete())
			{
				$request->session()->flash('error', $row->getError());
				continue;
			}

			$success++;
		}

		if ($success)
		{
			$request->session()->flash('success', trans('messages.item deleted', ['count' => $success]));
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
