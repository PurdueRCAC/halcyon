<?php

namespace App\Modules\Knowledge\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Halcyon\Http\StatefulRequest;
use App\Modules\Knowledge\Models\Page;
use App\Modules\Knowledge\Models\Association;

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
		//$filters['start'] = ($filters['limit'] * $filters['page']) - $filters['limit'];

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->state('kb.filter_' . $key, $key, $default);
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
		$a = (new Association)->getTable();

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
	 * Store a newly created entry
	 *
	 * @param   Request  $request
	 * @return  Response
	 */
	public function store(Request $request)
	{
		$request->validate([
			'fields.headline' => 'required',
			'fields.body' => 'required'
		]);

		$row = new Page();
		$row->fill($request->input('fields'));

		if (!$row->save())
		{
			return redirect()->back()->with('error', 'Failed to create item.');
		}

		return $this->cancel()->withSuccess('Item created!');
	}

	/**
	 * Show the form for editing the specified entry
	 *
	 * @param   integer  $id
	 * @return  Response
	 */
	public function edit($id)
	{
		app('request')->merge(['hidemainmenu' => 1]);

		$row = Report::findOrFail($id);

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		$groups = \App\Modules\Groups\Models\Group::where('id', '>', 0)->orderBy('name', 'asc')->get();

		return view('knowledge::admin.reports.edit', [
			'row'   => $row,
			'groups' => $groups
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
