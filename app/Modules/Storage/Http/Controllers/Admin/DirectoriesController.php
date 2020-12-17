<?php

namespace App\Modules\Storage\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Storage\Models\Directory;
use App\Modules\Storage\Models\StorageResource;
use App\Modules\Storage\Models\Notification;
use App\Halcyon\Http\StatefulRequest;
use App\Halcyon\Utility\Number;
use App\Modules\Users\Models\User;
use Carbon\Carbon;

class DirectoriesController extends Controller
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
			'search'   => null,
			'state'    => 'active',
			'parent'   => 0,
			'resource' => null,
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
			->where($d . '.parentstoragedirid', '=', $filters['parent'])
			->withTrashed();

		$parent = null;
		if ($filters['parent'])
		{
			$parent = Directory::find($filters['parent']);
		}

		$storage = null;
		if ($filters['resource'])
		{
			$storage = StorageResource::find($filters['resource']);

			$query->where($d . '.storageresourceid', '=', $filters['resource']);
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
						->orWhere($d . '.name', 'like', $filters['search'] . '%')
						->orWhere($d . '.name', 'like', '%' . $filters['search'])
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
			'parent' => $parent,
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
		$row = new Directory;

		if ($resource = $request->input('resource'))
		{
			$row->storageresourceid = $resource;
		}

		if ($parent = $request->input('parent'))
		{
			$row->parentstoragedirid = $parent;
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
	 * 
	 * @param  integer  $id
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
	 * 
	 * @param  Request  $request
	 * @param  array    $data
	 * @param  integer  $offset
	 * @return Response
	 */
	public function store(Request $request, $data = array(), $offset = 0)
	{
		$id = null;

		if (empty($data))
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

			$data = $request->input('fields');
			$id = $request->input('id');
		}

		$bytesource = null;
		if (isset($data['bytesource']))
		{
			$bytesource = $data['bytesource'];
			unset($data['bytesource']);
		}

		$row = $id ? Directory::findOrFail($id) : new Directory;

		if (!$id)
		{
			// Set up permissions
			$row->ownerread   = 1;
			$row->ownerwrite  = 1;
			$row->groupread   = 1;
			$row->groupwrite  = 1;
			$row->publicread  = 0;
			$row->publicwrite = 0;
		}

		//$row->fill($request->input('fields'));
		$row->fill($data);
		$row->resourceid = $row->storageResource->resource->id;
		$row->owneruserid = $row->owneruserid ?: auth()->user()->id;

		if ($row->parent)
		{
			// Disable parent groupwrite
			if (!$row->parent->autouser)
			{
				$return = $row->parent->update(['groupwrite' => 0]);

				if (!$return)
				{
					return redirect()->back()->withError(trans('Failed to update `storagedir` for :id', ['id' => $row->parentstoragedirid]));
				}
			}

			$row->publicread = 1;
		}

		// Make sure name is sane
		if (!preg_match("/^([a-zA-Z0-9]+\.?[\-_ ]*)*[a-zA-Z0-9]$/", $row->name))
		{
			return redirect()->back()->withError(trans('Field `name` has invalid format'));
		}

		if (!$row->autouserunixgroupid)
		{
			$row->autouserunixgroupid = $row->unixgroupid;
		}

		// Get parent so we can assemble a path
		$row->path = $row->parent ? $row->parent->path . '/' . $row->name : $row->name;

		if (strlen($row->path) > 255)
		{
			return redirect()->back()->withError(trans('Field `path` cannot be longer than 255 characters'));
		}

		// Find appropriate bucket
		$bucket = null;
		foreach ($row->group->storageBuckets as $b)
		{
			if ($b['resourceid'] == $row->resourceid)
			{
				$bucket = $b;
				break;
			}
		}

		$bytes = $request->input('bytes');
		if ($bytes == 'ALL')
		{
			if ($bucket == null)
			{
				$row->bytes = 0;
			}
			else
			{
				$row->bytes = $bucket['unallocatedbytes'];
			}
		}
		else //if (preg_match_all("/^(\-?\d*\.?\d+)\s*(\w+)$/", $bytes, $matches))
		{
			if ($bucket == null)
			{
				return redirect()->back()->withError(trans('Empty bucket'));
			}

			if ($bytesource && $bytesource == 'p' && $row->parent)
			{
				// Deducting from parent
				// Check to see if parent has sufficient bytes
				$parent = $row->parent;

				// Find the byte source, next ancestor with a quota
				while ($parent->quota == 0 && $parent->parentstoragedirid != 0)
				{
					$parent = $parent->parent;

					if (!$parent)
					{
						return redirect()->back()->withError(trans('Failed to retrieve `storagedir` for :bytesource', ['bytesource' => $bytesource]));
					}
				}

				if ($parent->quota <= $row->bytes)
				{
					return redirect()->back()->withError(trans('Parent quota is less than value submitted'));
				}

				// Reduce bytesource appropriately
				$parent->bytes = ($parent->quota - $row->bytes) . ' B';
				$parent->save();
			}
			elseif ($row->bytes > $bucket['unallocatedbytes'])
			{
				// Check to see if tried to allocate all remaining space but we missed a fwe bits because of rounding
				if (Number::formatBytes($row->bytes, true) == Number::formatBytes($bucket['unallocatedbytes'], true)
				 && $row->bytes != $bucket['unallocatedbytes'])
				{
					$row->bytes = $bucket['unallocatedbytes'];
				}

				if ($row->bytes > $bucket['unallocatedbytes'])
				{
					return redirect()->back()->withError(trans('Submitted bytes is greater than unallocatedbytes'));
				}
			}
			else
			{
				// Check to see if tried to allocate all remaining space but we missed a fwe bits because of rounding
				if (Number::formatBytes($row->bytes, true) == Number::formatBytes($bucket['unallocatedbytes'], true)
				 && $row->bytes != $bucket['unallocatedbytes'])
				{
					$row->bytes = $bucket['unallocatedbytes'];
				}
			}
		}
		/*elseif ($bytes == '-')
		{
			if (!$row->parent)
			{
				return redirect()->back()->withError(trans('Missing or invalid parent value'));
			}

			$row->bytes = 0;
		}*/

		// Look for this entry, duplicate name, etc.
		$exist = Directory::query()
			->where('resourceid', '=', $row->resourceid)
			->where('groupid', '=', $row->groupid)
			->where('parentstoragedirid', '=', $row->parentstoragedirid)
			->where('name', '=', $row->name)
			->where('datetimecreated', '<=', Carbon::now()->toDateTimeString())
			->where(function ($where)
			{
				$where->whereNull('datetimeremoved')
					->where('datetimeremoved', '=', '0000-00-00 00:00:00');
			})
			->get()
			->first();

		if ($exist)
		{
			return redirect()->back()->withError(trans('Duplicate entry found for :name', ['name' => $row->name]));
		}

		// Make sure both resourceid and storageresourceid are set
		if ($row->resourceid && !$row->storageresourceid)
		{
			$sr = StorageResource::query()
				->where('resourceid', '=', $row->resourceid)
				->get()
				->first();

			$row->storageresourceid = $sr->resourceid;
		}
		elseif (!$row->resourceid && $row->storageresourceid)
		{
			$row->resourceid = $row->storageResource->resourceid;
		}

		if (!$row->save())
		{
			$error = $row->getError() ? $row->getError() : trans('global.messages.save failed');

			return redirect()->back()->withError($error);
		}

		// If we have are requesting an autopopulate dir, then let's populate with the current list of users
		if (!$id && $row->autouser > 0)
		{
			$members = $row->autounixgroup->members;

			foreach ($members as $member)
			{
				// Set up object to pass back to ourselfs
				$data = [
					'bytes'       => '-',
					//'bytesource'  => '',
					'groupid'     => $row->groupid,
					'name'        => $member->user->username,
					'parent'      => $row->id,
					'resourceid'  => $row->resourceid,
					'unixgroupid' => $row->unixgroupid,
					'userid'      => $row->userid,
				];

				if ($row->autouser == 1)
				{
					// Group readable
					$data['groupread']  = 1;
					$data['groupwrite'] = 0;
					$data['otherread']  = 0;
				}
				elseif ($row->autouser == 2)
				{
					// Private
					$data['groupread']  = 0;
					$data['groupwrite'] = 0;
					$data['otherread']  = 0;
				}
				elseif ($row->autouser == 3)
				{
					// Group readable writable
					$data['groupread']  = 1;
					$data['groupwrite'] = 1;
					$data['otherread']  = 0;
				}

				$this->store($request, $data, 10);
			}
		}

		return $this->cancel($row->storageresourceid)->with('success', trans('global.messages.item saved'));
	}

	/**
	 * Remove the specified entry
	 *
	 * @param   Request $request
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
			$request->session()->flash('success', trans('global.messages.item deleted', ['count' => $success]));
		}

		return $this->cancel($request->input('parent'));
	}

	/**
	 * Return to default page
	 *
	 * @param   integer  $parent
	 * @return  Response
	 */
	public function cancel($parent = 0)
	{
		return redirect(route('admin.storage.directories', ['parent' => $parent]));
	}
}
