<?php

namespace App\Modules\Storage\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Storage\Models\StorageResource;
use App\Halcyon\Http\StatefulRequest;
use App\Modules\Resources\Models\Asset;
use App\Modules\Messages\Models\Type as MessageType;

class StorageController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * 
	 * @param  StatefulRequest $request
	 * @return Response
	 */
	public function index(StatefulRequest $request)
	{
		$filters = array(
			'search'   => '',
			'state'    => 'active',
			'resource' => null,
			// Paging
			'limit'    => config('list_limit', 20),
			'page'     => 1,
			// Sorting
			'order'     => 'name',
			'order_dir' => 'asc'
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->state('storage.filter_' . $key, $key, $default);
		}

		// Get records
		$query = StorageResource::query()->withTrashed();

		if ($filters['state'] != '*')
		{
			if ($filters['state'] == 'active')
			{
				$query->where(function($where)
				{
					$where->whereNull('datetimeremoved')
						->orWhere('datetimeremoved', '=', '0000-00-00 00:00:00');
				});
			}
			elseif ($filters['state'] == 'inactive')
			{
				$query->whereNotNull('datetimeremoved')
					->where('datetimeremoved', '!=', '0000-00-00 00:00:00');
			}
		}

		if ($filters['search'])
		{
			if (is_numeric($filters['search']))
			{
				$query->where('id', '=', $filters['search']);
			}
			else
			{
				$query->where('name', 'like', '%' . $filters['search'] . '%');
			}
		}

		if ($filters['resource'])
		{
			$query->where('parentresourceid', '=', $filters['resource']);
		}

		$rows = $query
			->withCount('directories')
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page'])
			->appends(array_filter($filters));

		$resources = (new Asset)->tree();

		return view('storage::admin.storage.index', [
			'rows'    => $rows,
			'filters' => $filters,
			'resources' => $resources,
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 * 
	 * @return Response
	 */
	public function create()
	{
		$asset = new StorageResource;

		$resources = (new Asset)->tree();

		$messagetypes = MessageType::query()->orderBy('name', 'asc')->get();

		return view('storage::admin.storage.edit', [
			'row'   => $asset,
			'resources' => $resources,
			'messagetypes' => $messagetypes,
		]);
	}

	/**
	 * Show the form for editing the specified resource.
	 * 
	 * @param  integer  $id
	 * @return Response
	 */
	public function edit($id)
	{
		$asset = StorageResource::find($id);

		$resources = (new Asset)->tree();

		$messagetypes = MessageType::query()->orderBy('name', 'asc')->get();

		return view('storage::admin.storage.edit', [
			'row'   => $asset,
			'resources' => $resources,
			'messagetypes' => $messagetypes,
		]);
	}

	/**
	 * Store a newly created resource in storage.
	 * 
	 * @param  Request $request
	 * @return Response
	 */
	public function store(Request $request)
	{
		$request->validate([
			'fields.name' => 'required|string|max:32',
			'fields.parentresourceid' => 'nullable|integer',
			'fields.path' => 'nullable|string|max:255',
			'fields.import' => 'nullable|in:0,1',
			'fields.autousedir' => 'nullable|in:0,1',
		]);

		$id = $request->input('id');

		$row = $id ? StorageResource::findOrFail($id) : new StorageResource;

		$row->fill($request->input('fields'));

		if (!$row->save())
		{
			$error = $row->getError() ? $row->getError() : trans('global.messages.save failed');

			return redirect()->back()->withError($error);
		}

		return $this->cancel()->with('success', trans('global.messages.item saved'));
	}

	/**
	 * Remove the specified items
	 *
	 * @param  Request $request
	 * @return Response
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
			$row = StorageResource::findOrFail($id);

			if ($row->directories()->count())
			{
				$request->session()->flash('error', trans('storage::storage.error.not empty'));
				continue;
			}

			if ($row->isTrashed())
			{
				if (!$row->forceDelete())
				{
					$request->session()->flash('error', $row->getError());
					continue;
				}
			}
			elseif (!$row->delete())
			{
				$request->session()->flash('error', $row->getError());
				continue;
			}

			$success++;
		}

		if ($success)
		{
			$request->session()->flash('success', trans('global.messages.item deleted', ['count' => $success]));
		}

		return redirect(route('admin.storage.index'));
	}

	/**
	 * Return to the main view
	 *
	 * @return  Response
	 */
	public function cancel()
	{
		return redirect(route('admin.storage.index'));
	}
}
