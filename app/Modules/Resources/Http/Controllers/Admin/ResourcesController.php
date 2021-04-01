<?php

namespace App\Modules\Resources\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Resources\Models\Asset;
use App\Modules\Resources\Models\Type;
use App\Modules\Resources\Models\Batchsystem;
use App\Modules\Resources\Events\AssetDisplaying;
use App\Halcyon\Http\StatefulRequest;

class ResourcesController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @param  StatefulRequest $request
	 * @return Response
	 */
	public function index(StatefulRequest $request)
	{
		// Get filters
		$filters = array(
			'search'   => null,
			'state'    => 'active',
			'type'     => 0,
			'parent'   => 0,
			'batchsystem' => 0,
			// Paging
			'limit'    => config('list_limit', 20),
			'page'     => 1,
			// Sorting
			'order'     => 'name',
			'order_dir' => 'asc',
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->state('resources.filter_' . $key, $key, $default);
		}
		$filters['start'] = ($filters['limit'] * $filters['page']) - $filters['limit'];

		if (!in_array($filters['order'], ['id', 'name', 'state', 'type', 'parent', 'display']))
		{
			$filters['order'] = 'name';
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = 'asc';
		}

		// Build query
		$query = Asset::query()->withTrashed();

		if ($filters['state'] == 'active')
		{
			$query->whereIsActive();
		}
		elseif ($filters['state'] == 'trashed')
		{
			$query->whereIsTrashed();
		}

		if ($filters['type'] > 0)
		{
			$query->where('resourcetype', '=', (int)$filters['type']);
		}

		if ($filters['parent'])
		{
			$query->where('parentid', '=', (int)$filters['parent']);
		}

		if ($filters['batchsystem'])
		{
			$query->where('batchsystem', '=', (int)$filters['batchsystem']);
		}

		if ($filters['search'])
		{
			if (is_numeric($filters['search']))
			{
				$query->where('id', '=', $filters['search']);
			}
			else
			{
				$query->where(function ($where) use ($filters)
				{
					$where->where('name', 'like', '%' . strtolower($filters['search']) . '%')
						->orWhere('rolename', 'like', '%' . strtolower($filters['search']) . '%')
						->orWhere('listname', 'like', '%' . strtolower($filters['search']) . '%');
				});
			}
		}

		if ($filters['order'] == 'display')
		{
			$query->orderBy('parentid', 'asc');
		}

		/*$rows = $query
			->withCount('children')
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit']);*/

		if ($filters['search'] || $filters['state'] == 'trashed')
		{
			$rows = $query
				->withCount('children')
				->orderBy($filters['order'], $filters['order_dir'])
				->paginate($filters['limit'], ['*'], 'page', $filters['page']);

			$paginator = $rows;
		}
		else
		{
			$rows = $query
				->withCount('children')
				->orderBy($filters['order'], $filters['order_dir'])
				->get();
				//->paginate($filters['limit']);

			$total      = count($rows);
			$levellimit = ($filters['limit'] == 0) ? 500 : $filters['limit'];
			$list       = array();
			$children   = array();

			if ($rows)
			{
				// First pass - collect children
				foreach ($rows as $k)
				{
					$pt = $k->parentid;
					$list = @$children[$pt] ? $children[$pt] : array();
					array_push($list, $k);
					$children[$pt] = $list;
				}

				// Second pass - get an indent list of the items
				$list = $this->treeRecurse(0, '', array(), $children, max(0, $levellimit-1));
			}

			if ($filters['batchsystem'])
			{
				$list = array_filter($list, function($k) use ($filters)
				{
					return ($k->batchsystem == $filters['batchsystem']);
				});
				$total = count($list);
			}

			$rows = array_slice($list, $filters['start'], $filters['limit']);

			$paginator = new \Illuminate\Pagination\LengthAwarePaginator($rows, $total, $filters['limit'], $filters['page']);
			$paginator->withPath(route('admin.resources.index'));
		}

		$types = Type::orderBy('name', 'asc')->get();

		$batchsystems = Batchsystem::all();

		return view('resources::admin.resources.index', [
			'rows'  => $rows,
			'types' => $types,
			'paginator' => $paginator,
			'filters' => $filters,
			'batchsystems' => $batchsystems
		]);
	}

	/**
	 * Recursive function to build tree
	 *
	 * @param   integer  $id        Parent ID
	 * @param   string   $indent    Indent text
	 * @param   array    $list      List of records
	 * @param   array    $children  Container for parent/children mapping
	 * @param   integer  $maxlevel  Maximum levels to descend
	 * @param   integer  $level     Indention level
	 * @param   integer  $type      Indention type
	 * @return  array
	 */
	protected function treeRecurse($id, $indent, $list, $children, $maxlevel=9999, $level=0, $type=1)
	{
		if (@$children[$id] && $level <= $maxlevel)
		{
			foreach ($children[$id] as $v)
			{
				$id = $v->id;

				$spacer = '&nbsp;&nbsp;';

				$pt = $v->parentid;

				$list[$id] = $v;
				$list[$id]->treename = str_repeat('<span class="gi">|&mdash;</span>', $level);
				$list[$id]->children = isset($children[$id]) ? count($children[$id]) : 0;

				$list = $this->treeRecurse($id, $indent . $spacer, $list, $children, $maxlevel, $level+1, $type);
			}
		}
		return $list;
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		$row = new Asset();

		$types = Type::orderBy('name', 'asc')->get();
		$batchsystems = Batchsystem::all();
		$parents  = $row->tree();
		$products = array();

		return view('resources::admin.resources.edit', [
			'row'   => $row,
			'types' => $types,
			'parents' => $parents,
			'products' => $products,
			'batchsystems' => $batchsystems
		]);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  integer $id
	 * @return Response
	 */
	public function edit($id)
	{
		$row = Asset::query()->withTrashed()->where('id', '=', $id)->first();

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		$types = Type::orderBy('name', 'asc')->get();
		$batchsystems = Batchsystem::all();
		$parents  = $row->tree();
		$products = array();

		event($event = new AssetDisplaying($row, 'details'));
		$sections = collect($event->getSections());

		return view('resources::admin.resources.edit', [
			'row'   => $row,
			'types' => $types,
			'parents' => $parents,
			'products' => $products,
			'batchsystems' => $batchsystems,
			'sections' => $sections,
		]);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  Request $request
	 * @return Response
	 */
	public function store(Request $request)
	{
		$request->validate([
			'fields.name'         => 'required|max:32',
			'fields.parentid'     => 'nullable|integer',
			'fields.rolename'     => 'nullable|string|max:32',
			'fields.listname'     => 'nullable|string|max:32',
			'fields.batchsystem'  => 'nullable|integer',
			'fields.resourcetype' => 'nullable|integer',
			'fields.producttype'  => 'nullable|integer',
		]);

		$id = $request->input('id');

		$row = $id ? Asset::findOrFail($id) : new Asset();

		$row->fill($request->input('fields'));

		if ($params = $request->input('params', []))
		{
			foreach ($params as $key => $val)
			{
				$row->params->set($key, $val);
			}
		}

		if (!$row->save())
		{
			$error = $row->getError() ? $row->getError() : trans('global.messages.save failed');

			return redirect()->back()->withError($error);
		}

		return redirect(route('admin.resources.index'))->withSuccess(trans('global.messages.update success'));
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  Request $request
	 * @return Response
	 */
	public function delete(Request $request)
	{
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		//$success = Asset::destroy($ids);
		$success = 0;

		foreach ($ids as $id)
		{
			$row = Asset::findOrFail($id);

			if (!$row->isTrashed())
			{
				if (!$row->delete())
				{
					$request->session()->flash('error', $row->getError());
					continue;
				}
			}
			else
			{
				if (!$row->forceDelete())
				{
					$request->session()->flash('error', $row->getError());
					continue;
				}
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
	 * Remove the specified resource from storage.
	 *
	 * @param  Request $request
	 * @return Response
	 */
	public function restore(Request $request)
	{
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		foreach ($ids as $id)
		{
			$row = Asset::findOrFail($id);

			if ($row->trashed())
			{
				if (!$row->restore())
				{
					$request->session()->flash('error', $row->getError());
					continue;
				}
			}

			$success++;
		}

		if ($success)
		{
			$request->session()->flash('success', trans('global.messages.item restored', ['count' => $success]));
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
		return redirect(route('admin.resources.index'));
	}
}
