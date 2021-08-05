<?php

namespace App\Modules\Resources\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Modules\Resources\Models\Asset;
use App\Modules\Resources\Models\Subresource;
use App\Modules\Resources\Models\Child;
use App\Modules\Resources\Models\Type;
use App\Halcyon\Http\StatefulRequest;

class SubresourcesController extends Controller
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
			'resource' => 0,
			'state'    => 'published',
			// Paging
			'limit'    => config('list_limit', 20),
			'page'     => 1,
			//'start'    => $request->input('limitstart', 0),
			// Sorting
			'order'     => Subresource::$orderBy,
			'order_dir' => Subresource::$orderDir,
		);

		$reset = false;
		$request = $request->mergeWithBase();
		foreach ($filters as $key => $default)
		{
			if ($key != 'page'
			 && $request->has($key) && session()->has('subresources.filter_' . $key)
			 && $request->input($key) != session()->get('subresources.filter_' . $key))
			{
				$reset = true;
			}
			$filters[$key] = $request->state('subresources.filter_' . $key, $key, $default);
		}
		$filters['page'] = $reset ? 1 : $filters['page'];

		if (!in_array($filters['order'], ['id', 'name', 'state', 'resource']))
		{
			$filters['order'] = Subresource::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Subresource::$orderDir;
		}

		// Build query
		$s = (new Subresource)->getTable();
		$c = (new Child)->getTable();

		$query = Subresource::query()
			->select($s . '.*', $c . '.resourceid')
			->withTrashed();

		if ($filters['state'] == 'trashed')
		{
			$query->whereIsTrashed();
		}
		elseif ($filters['state'] == 'published')
		{
			$query->whereIsActive();
		}

		if ($filters['search'])
		{
			if (is_numeric($filters['search']))
			{
				$query->where($s . '.id', '=', $filters['search']);
			}
			else
			{
				$query->where($s . '.name', 'like', '%' . $filters['search'] . '%');
			}
		}

		$query->leftJoin($c, $c . '.subresourceid', $s . '.id');

		if ($filters['resource'])
		{
			$query->where($c . '.resourceid', '=', $filters['resource']);
		}

		$rows = $query
			->withCount('queues')
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		$resources = (new Asset)->tree();

		return view('resources::admin.subresources.index', [
			'rows'  => $rows,
			'filters' => $filters,
			'resources' => $resources,
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @param  Request $request
	 * @return Response
	 */
	public function create(Request $request)
	{
		$row = new Subresource();
		$row->nodecores = 16;
		$row->nodemem = '64G';
		$row->nodegpus = 0;

		$parents = (new Asset)->tree();

		$resourceid = $request->input('resource', 0);

		return view('resources::admin.subresources.edit', [
			'row'   => $row,
			'parents' => $parents,
			'resourceid' => $resourceid,
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
		$row = Subresource::find($id);

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		$resourceid = $row->association ? $row->association->resourceid : 0;

		$parents  = (new Asset)->tree();

		return view('resources::admin.subresources.edit', [
			'row'   => $row,
			'parents' => $parents,
			'resourceid' => $resourceid,
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
		$rules = [
			'fields.name' => 'required|string|max:32',
			'fields.cluster' => 'nullable|string|max:12',
			'fields.nodecores' => 'nullable|integer|max:999',
			'fields.nodemem' => 'nullable|string|max:5',
			'fields.nodegpus' => 'nullable|integer|max:9999',
			'fields.nodeattributes' => 'nullable|string|max:16',
			'fields.description' => 'nullable|string|max:255',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$id = $request->input('id');

		// Save Subresource
		$row = $id ? Subresource::findOrFail($id) : new Subresource();
		$row->fill($request->input('fields'));
		$row->nodeattributes = (string)$row->nodeattributes;
		$row->description = $row->description ?: '';
		$row->nodecores = $row->nodecores ?: 0;
		$row->nodemem = $row->nodemem ?: '';

		if (!$row->save())
		{
			$error = $row->getError() ? $row->getError() : trans('global.messages.save failed');

			return redirect()->back()->withError($error);
		}

		// Update/Create Resource/Subresource association
		$child = $row->association;

		if (!$child)
		{
			$child = new Child;
		}
		$child->resourceid = $request->input('assoc.resourceid');

		if (!$child->resource)
		{
			return redirect()->back()->withError(trans('resources::assets.invalid resource'));
		}
		$child->subresourceid = $row->id;
		$child->save();

		return redirect(route('admin.resources.subresources'))->withSuccess(trans('global.messages.item ' . ($id ? 'updated' : 'created')));
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

		$success = 0;

		foreach ($ids as $id)
		{
			$row = Subresource::findOrFail($id);

			if (!$row->trashed())
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
	 * Restore a removed item
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
	 * Restore a removed item
	 *
	 * @param  Request $request
	 * @return Response
	 */
	public function stop(Request $request)
	{
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		foreach ($ids as $id)
		{
			$row = Subresource::findOrFail($id);
			$row->stopQueues();

			$success++;
		}

		if ($success)
		{
			$request->session()->flash('success', trans('resources::resources.messages.queues stopped', ['count' => $success]));
		}

		return $this->cancel();
	}

	/**
	 * Restore a removed item
	 *
	 * @param  Request $request
	 * @return Response
	 */
	public function start(Request $request)
	{
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		foreach ($ids as $id)
		{
			$row = Subresource::findOrFail($id);
			$row->startQueues();

			$success++;
		}

		if ($success)
		{
			$request->session()->flash('success', trans('resources::resources.messages.queues started', ['count' => $success]));
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
		return redirect(route('admin.resources.subresources'));
	}
}
