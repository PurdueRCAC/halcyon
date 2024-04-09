<?php

namespace App\Modules\Storage\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Modules\Storage\Models\StorageResource;
use App\Modules\Storage\Models\Purchase;
use App\Halcyon\Http\Concerns\UsesFilters;
use App\Modules\Resources\Models\Asset;
use App\Modules\Messages\Models\Type as MessageType;

class StorageController extends Controller
{
	use UsesFilters;

	/**
	 * Display a listing of the resource.
	 * 
	 * @param  Request $request
	 * @return View
	 */
	public function index(Request $request): View
	{
		$filters = $this->getStatefulFilters($request, 'storage', [
			'search'   => '',
			'state'    => 'active',
			'resource' => null,
			// Paging
			'limit'    => config('list_limit', 20),
			'page'     => 1,
			// Sorting
			'order'     => 'name',
			'order_dir' => 'asc'
		]);

		// Get records
		$query = StorageResource::query()->withTrashed()->with('resource');

		if ($filters['state'] != '*')
		{
			if ($filters['state'] == 'active')
			{
				$query->whereNull('datetimeremoved');
			}
			elseif ($filters['state'] == 'inactive')
			{
				$query->whereNotNull('datetimeremoved');
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
	 * @param  Request $request
	 * @return View
	 */
	public function create(Request $request): View
	{
		$row = new StorageResource;

		$resources = (new Asset)->tree();

		$messagetypes = MessageType::query()
			->orderBy('name', 'asc')
			->get();

		if ($fields = $request->old('fields'))
		{
			$row->fill($fields);
		}

		return view('storage::admin.storage.edit', [
			'row'   => $row,
			'resources' => $resources,
			'messagetypes' => $messagetypes,
		]);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  Request $request
	 * @param  int  $id
	 * @return View
	 */
	public function edit(Request $request, $id): View
	{
		$row = StorageResource::find($id);

		$resources = (new Asset)->tree();

		$messagetypes = MessageType::query()
			->orderBy('name', 'asc')
			->get();

		if ($fields = $request->old('fields'))
		{
			$row->fill($fields);
		}

		return view('storage::admin.storage.edit', [
			'row'   => $row,
			'resources' => $resources,
			'messagetypes' => $messagetypes,
		]);
	}

	/**
	 * Store a newly created resource in storage.
	 * 
	 * @param  Request $request
	 * @return RedirectResponse
	 */
	public function store(Request $request): RedirectResponse
	{
		$rules = [
			'fields.name' => 'required|string|max:32',
			'fields.parentresourceid' => 'nullable|integer',
			'fields.path' => 'nullable|string|max:255',
			//'fields.import' => 'nullable|in:0,1',
			'fields.autousedir' => 'nullable|in:0,1',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$id = $request->input('id');

		$row = StorageResource::findOrNew($id);

		$row->fill($request->input('fields'));
		$row->importhostname = $row->importhostname ?: '';

		if (!$row->save())
		{
			return redirect()->back()->withError(trans('global.messages.save failed'));
		}

		$bytes = $request->input('bytes');

		if ($bytes)
		{
			$hardware = Purchase::query()
				->where('resourceid', '=', $row->getOriginal('parentresourceid'))
				->where('groupid', '=', '-1')
				->where('sellergroupid', '=', 0)
				->first();

			if (!$hardware)
			{
				$hardware = new Purchase;
				$hardware->comment = 'New hardware';
				$hardware->datetimestart = $row->datetimecreated;
				$hardware->sellergroupid = 0;
				$hardware->groupid = -1;
			}

			$hardware->resourceid = $row->parentresourceid;
			$hardware->bytes = $bytes;
			$hardware->save();
		}

		return $this->cancel()->with('success', trans('global.messages.item saved'));
	}

	/**
	 * Remove the specified items
	 *
	 * @param  Request $request
	 * @return RedirectResponse
	 */
	public function delete(Request $request): RedirectResponse
	{
		// Incoming
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		foreach ($ids as $id)
		{
			// Delete the entry
			// Note: This is recursive and will also remove all descendents
			$row = StorageResource::find($id);

			if (!$row)
			{
				continue;
			}

			if ($row->directories()->count())
			{
				$request->session()->flash('error', trans('storage::storage.error.not empty'));
				continue;
			}

			if ($row->trashed())
			{
				if (!$row->forceDelete())
				{
					$request->session()->flash('error', trans('storage::storage.error.failed to delete'));
					continue;
				}
			}
			elseif (!$row->delete())
			{
				$request->session()->flash('error', trans('storage::storage.error.failed to delete'));
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
	 * @return  RedirectResponse
	 */
	public function cancel(): RedirectResponse
	{
		return redirect(route('admin.storage.index'));
	}
}
