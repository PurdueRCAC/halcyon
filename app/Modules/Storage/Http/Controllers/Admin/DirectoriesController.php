<?php

namespace App\Modules\Storage\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Storage\Models\Directory;
use App\Modules\Storage\Models\StorageResource;
use App\Halcyon\Http\StatefulRequest;
use App\Modules\Users\Models\User;

class DirectoriesController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * @return Response
	 */
	public function index(StatefulRequest $request)
	{
		$filters = array(
			'search'   => null,
			'state'    => 'active',
			'parent'   => null,
			// Paging
			'limit'    => config('list_limit', 20),
			'page'     => 1,
			// Sorting
			'order'     => 'path',
			'order_dir' => 'asc'
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->state('storage.dirs.filter_' . $key, $key, $default);
		}

		// Get records
		$query = Directory::query();

		$d = (new Directory)->getTable();

		$query->select($d . '.*')
			->where($d . '.parentstoragedirid', '=', 0)
			->withTrashed();

		$storage = null;
		if ($filters['parent'])
		{
			$storage = StorageResource::find($filters['parent']);

			$query->where($d . '.storageresourceid', '=', $filters['parent']);
		}

		if ($filters['state'] != '*')
		{
			if ($filters['state'] == 'active')
			{
				$query->where($d . '.datetimeremoved', '=', '0000-00-00 00:00:00');
			}
			elseif ($filters['state'] == 'inactive')
			{
				$query->where($d . '.datetimeremoved', '!=', '0000-00-00 00:00:00');
			}
		}

		if ($filters['search'])
		{
			if (is_numeric($filters['search']))
			{
				$query->where($d . '.id', '=', $filters['search']);
			}
			else
			{
				$u = (new User)->getTable();

				$query->leftJoin($u, $u . '.id', $d . '.owneruserid');

				$query->where(function($where) use ($filters, $d, $u)
				{
					$where->where($d . '.name', 'like', '%' . $filters['search'] . '%')
						->orWhere($u . '.name', 'like', '%' . $filters['search'] . '%');
				});
			}
		}

		$rows = $query
			->orderBy($d . '.' . $filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		$query = StorageResource::query();

		if ($filters['state'] != '*')
		{
			if ($filters['state'] == 'active')
			{
				$query->where('datetimeremoved', '=', '0000-00-00 00:00:00');
			}
			elseif ($filters['state'] == 'inactive')
			{
				$query->where('datetimeremoved', '!=', '0000-00-00 00:00:00');
			}
		}

		$storages = $query
			->orderBy('name', 'asc')
			->get();

		return view('storage::admin.directories.index', [
			'rows'    => $rows,
			'filters' => $filters,
			'storage' => $storage,
			'storages' => $storages,
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 * @return Response
	 */
	public function create(Request $request)
	{
		$row = new Directory;

		if ($parent = $request->input('parent'))
		{
			$row->storageresourceid = $parent;
		}

		$storages = StorageResource::query()
			->orderBy('name', 'asc')
			->get();

		return view('storage::admin.directories.edit', [
			'row' => $row,
			'storageresources' => $storages,
		]);
	}

	/**
	 * Show the form for editing the specified resource.
	 * @return Response
	 */
	public function edit($id)
	{
		$row = Directory::findOrFail($id);

		$storages = StorageResource::query()
			->orderBy('name', 'asc')
			->get();

		return view('storage::admin.directories.edit', [
			'row' => $row,
			'storageresources' => $storages,
		]);
	}

	/**
	 * Store a newly created resource in storage.
	 * @param  Request $request
	 * @return Response
	 */
	public function store(Request $request)
	{
		$request->validate([
			'fields.name' => 'required|string|max:32',
			'fields.storageresourceid' => 'nullable|integer',
			'fields.groupid' => 'nullable|integer',
			'fields.unixgroupid' => 'nullable|integer',
			'fields.owneruserid' => 'nullable|integer',
			'fields.autouserunixgroupid' => 'nullable|integer',
			'fields.autouser' => 'nullable|in:0,1,2,3',
		]);

		$id = $request->input('id');

		$row = $id ? Directory::findOrFail($id) : new Directory;

		$row->fill($request->input('fields'));
		$row->resourceid = $row->storageResource->resource->id;

		if (!$row->path)
		{
			$row->path = $row->name;
		}

		if ($row->parentstoragedirid)
		{
			$row->path = $row->parent->path . '/' . $row->name;
		}

		if (!$row->save())
		{
			$error = $row->getError() ? $row->getError() : trans('messages.save failed');

			return redirect()->back()->withError($error);
		}

		return $this->cancel()->with('success', trans('messages.item saved'));
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
			$row = Directory::findOrFail($id);

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
		return redirect(route('admin.storage.directories'));
	}
}
