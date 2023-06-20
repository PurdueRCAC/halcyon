<?php

namespace App\Modules\Resources\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Modules\Resources\Models\Asset;
use App\Modules\Resources\Models\Type;
use App\Modules\Resources\Models\Batchsystem;
use App\Modules\Resources\Models\Facet;
use App\Modules\Resources\Events\AssetDisplaying;
use App\Halcyon\Http\StatefulRequest;

class ResourcesController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @param  StatefulRequest $request
	 * @return View
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

		$reset = false;
		$request = $request->mergeWithBase();
		foreach ($filters as $key => $default)
		{
			if ($key != 'page'
			 && $request->has($key) //&& session()->has('resources.filter_' . $key)
			 && $request->input($key) != session()->get('resources.filter_' . $key))
			{
				$reset = true;
			}
			$filters[$key] = $request->state('resources.filter_' . $key, $key, $default);
		}
		$filters['page'] = $reset ? 1 : $filters['page'];
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
		$query = Asset::query();

		if ($filters['state'] == 'active')
		{
			// Default behavior
		}
		elseif ($filters['state'] == 'trashed')
		{
			$query->onlyTrashed();
		}
		else
		{
			$query->withTrashed();
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
	 * @param   int  $id        Parent ID
	 * @param   string   $indent    Indent text
	 * @param   array    $list      List of records
	 * @param   array    $children  Container for parent/children mapping
	 * @param   int  $maxlevel  Maximum levels to descend
	 * @param   int  $level     Indention level
	 * @param   int  $type      Indention type
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
	 * @return View
	 */
	public function create()
	{
		$row = new Asset();
		$row->access = config('module.resources.default_access', 0);

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
	 * @param  int $id
	 * @return View
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
	 * @return RedirectResponse
	 */
	public function store(Request $request)
	{
		$rules = [
			'fields.name'         => 'required|max:32',
			'fields.parentid'     => 'nullable|integer',
			'fields.rolename'     => 'nullable|string|max:32',
			'fields.listname'     => 'nullable|string|max:32',
			'fields.batchsystem'  => 'nullable|integer',
			'fields.resourcetype' => 'nullable|integer',
			'fields.producttype'  => 'nullable|integer',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$id = $request->input('id');

		$row = $id ? Asset::findOrFail($id) : new Asset();

		$row->fill($request->input('fields'));

		if (!$row->save())
		{
			return redirect()->back()->withError(trans('global.messages.save failed'));
		}

		if ($facets = $request->input('facets', []))
		{
			if (isset($facets[$row->resourcetype]))
			{
				foreach ($facets[$row->resourcetype] as $key => $value)
				{
					$ft = $row->type->facetTypes->where('name', '=', $key)->first();

					if (!$ft)
					{
						continue;
					}

					$facet = $row->facets->where('facet_type_id', '=', $ft->id)->first();

					if (!$value)
					{
						if ($facet)
						{
							$facet->delete();
						}
						continue;
					}

					$facet = $facet ?: new Facet;
					$facet->asset_id = $row->id;
					$facet->facet_type_id = $ft->id;
					$facet->value = $value;
					$facet->save();
				}
			}
		}

		return redirect(route('admin.resources.index'))->withSuccess(trans('global.messages.item ' . ($id ? 'updated' : 'created')));
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  Request $request
	 * @return RedirectResponse
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

			if (!$row->trashed())
			{
				if (!$row->delete())
				{
					$request->session()->flash('error', trans('global.messages.delete failed'));
					continue;
				}
			}
			else
			{
				if (!$row->forceDelete())
				{
					$request->session()->flash('error', trans('global.messages.delete failed'));
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
	 * @return RedirectResponse
	 */
	public function restore(Request $request)
	{
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		foreach ($ids as $id)
		{
			$row = Asset::query()->withTrashed()->where('id', '=', $id)->first();

			if ($row && $row->trashed())
			{
				if (!$row->restore())
				{
					$request->session()->flash('error', trans('global.messages.restore failed'));
					continue;
				}
				else
				{
					$success++;
				}
			}
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
	 * @return  RedirectResponse
	 */
	public function cancel()
	{
		return redirect(route('admin.resources.index'));
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @param  StatefulRequest $request
	 * @param  int  $id
	 * @return Response
	 */
	public function members(Request $request, $id)
	{
		$rows = array();

		if ($request->has('export'))
		{
			$rows = json_decode($request->input('export', '[]'));

			return $this->export($rows, $id);
		}

		$asset = Asset::query()->withTrashed()->where('id', '=', $id)->first();

		return view('resources::admin.resources.members', [
			'asset' => $asset,
			'rows' => $rows,
		]);
	}

	/**
	 * Download a list of records
	 * 
	 * @param  array $rows
	 * @param  int $id
	 * @return StreamedResponse
	 */
	public function export($rows, $id)
	{
		$data = array();
		$data[] = array(
			trans('resources::assets.id'),
			trans('users::users.name'),
			trans('users::users.username'),
			trans('users::users.email'),
			trans('resources::assets.queues'),
		);

		$users = array();
		foreach ($rows as $row)
		{
			if (in_array($row->id, $users))
			{
				continue;
			}

			$users[] = $row->id;

			$queues = array();
			if (isset($row->queues))
			{
				foreach ($row->queues as $q)
				{
					$queues[] = $q->name;
				}
			}

			$data[] = array(
				$row->id,
				$row->name,
				$row->username,
				$row->email,
				implode('; ', $queues)
			);
		}

		$filename = 'resource_' . $id . '_active_users.csv';

		$headers = array(
			'Content-type' => 'text/csv',
			'Content-Disposition' => 'attachment; filename=' . $filename,
			'Pragma' => 'no-cache',
			'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
			'Expires' => '0',
			'Last-Modified' => gmdate('D, d M Y H:i:s') . ' GMT'
		);

		$callback = function() use ($data)
		{
			$file = fopen('php://output', 'w');

			foreach ($data as $datum)
			{
				fputcsv($file, $datum);
			}
			fclose($file);
		};

		return response()->streamDownload($callback, $filename, $headers);
	}
}
