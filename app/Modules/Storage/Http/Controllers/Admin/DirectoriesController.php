<?php

namespace App\Modules\Storage\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Modules\Storage\Models\Directory;
use App\Modules\Storage\Models\StorageResource;
use App\Modules\Storage\Models\Notification;
use App\Halcyon\Http\StatefulRequest;
use App\Halcyon\Utility\Number;
use App\Modules\Users\Models\User;
use App\Modules\Groups\Models\Group;
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

		$reset = false;
		$request = $request->mergeWithBase();
		foreach ($filters as $key => $default)
		{
			if ($key != 'page'
			 && $request->has($key) //&& session()->has('storage.dirs.filter_' . $key)
			 && $request->input($key) != session()->get('storage.dirs.filter_' . $key))
			{
				$reset = true;
			}
			$filters[$key] = $request->state('storage.dirs.filter_' . $key, $key, $default);
		}
		$filters['page'] = $reset ? 1 : $filters['page'];

		// Get records
		$query = Directory::query()
			->with('group');

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
				$query->whereNull($d . '.datetimeremoved');
			}
			elseif ($filters['state'] == 'inactive')
			{
				$query->whereNotNull($d . '.datetimeremoved');
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
				$g = (new Group)->getTable();

				$query->leftJoin($u, $u . '.id', $d . '.owneruserid');
				$query->leftJoin($g, $g . '.id', $d . '.groupid');

				$query->where(function($where) use ($filters, $d, $u, $g)
				{
					$where->where($d . '.name', 'like', '%' . $filters['search'] . '%')
						->orWhere($d . '.name', 'like', $filters['search'] . '%')
						->orWhere($d . '.name', 'like', '%' . $filters['search'])
						->orWhere($u . '.name', 'like', '%' . $filters['search'] . '%')
						->orWhere($g . '.name', 'like', $filters['search'] . '%')
						->orWhere($g . '.name', 'like', '%' . $filters['search'] . '%');
				});
			}
		}

		$rows = $query
			->orderBy($d . '.' . $filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		$storages = StorageResource::query()
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
			if ($row->parent)
			{
				$row->groupid = $row->parent->groupid;
			}
		}

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
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
		$row = Directory::query()
			->withTrashed()
			->where('id', '=', $id)
			->first();

		if (!$row)
		{
			abort(404);
		}

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		$storages = StorageResource::query()
			->withTrashed()
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
			$rules = [
				'fields.name' => 'required|string|max:32',
				'fields.storageresourceid' => 'nullable|integer',
				'fields.groupid' => 'nullable|integer',
				'fields.unixgroupid' => 'nullable|integer',
				'fields.owneruserid' => 'nullable|integer',
				'fields.autouserunixgroupid' => 'nullable|integer',
				'fields.autouser' => 'nullable|in:0,1,2,3',
			];

			$validator = Validator::make($request->all(), $rules);

			if ($validator->fails())
			{
				return redirect()->back()
					->withInput($request->input())
					->withErrors($validator->messages());
			}

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

		// Reset everything in case someone unchecked a box on the form
		// Checked boxes will get set to 1 with the data fill below
		$row->ownerread   = 0;
		$row->ownerwrite  = 0;
		$row->groupread   = 0;
		$row->groupwrite  = 0;
		$row->publicread  = 0;
		$row->publicwrite = 0;
		$row->owneruserid = 0;
		$row->unixgroupid = 0;
		$row->groupid     = 0;
		$row->autouserunixgroupid = 0;
		$row->autouser    = 0;

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

		foreach ($data as $key => $val)
		{
			if (is_null($val) && in_array($key, ['groupid', 'unixgroupid', 'owneruserid', 'autouser', 'autouserunixgroupid']))
			{
				$data[$key] = 0;
			}
		}
		$row->fill($data);
		$row->resourceid = $row->storageResource->resource->id;

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

			//$row->publicread = 1;
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

		$unallocatedbytes = 0;

		if ($request->has('bytes'))
		{
			// Find appropriate bucket
			$bucket = null;
			foreach ($row->group->storagebuckets as $b)
			{
				if ($b['resourceid'] == $row->resourceid)
				{
					$bucket = $b;
					break;
				}
			}

			$bytes = $request->input('bytes');

			if (preg_match_all("/^(\-?\d*\.?\d+)\s*(\w+)$/", $bytes, $matches))
			{
				if ($bucket == null)
				{
					return redirect()->back()->withError(trans('Empty bucket'));
				}

				$row->bytes = $bytes;
				$bytes = $row->bytes;

				// Top level dirs are required to have a quota
				if ($row->bytes == 0 && !$row->parent)
				{
					return redirect()->back()->withError(trans('Top level dirs are required to have a quota'));
				}

				// Can't switch between no quota and quota
				if (($row->getOriginal('bytes') == 0 && $row->bytes != 0)
				 || ($row->getOriginal('bytes') != 0 && $row->bytes == 0))
				{
					return redirect()->back()->withError(trans('Cannot switch between no quota and quota'));
				}

				if ($row->bytes < 0)
				{
					return redirect()->back()->withError(trans('Cannot have a negative quota'));
				}

				if ($row->bytes == 0)
				{
					return redirect()->back()->withError(trans('Cannot have zero bytes'));
				}

				// Check to see if tried to allocate all remaining space but we missed a fwe bits because of rounding
				if (Number::formatBytes($row->bytes) == Number::formatBytes($bucket['unallocatedbytes'] + $row->getOriginal('bytes'))
				 && $row->bytes != $bucket['unallocatedbytes'] + $row->getOriginal('bytes'))
				{
					$row->bytes = $bucket['unallocatedbytes'] + $row->getOriginal('bytes');
				}
			}
			elseif ($bytes == 'ALL')
			{
				if ($bucket == null)
				{
					$bytes = $row->getOriginal('bytes');
				}
				else
				{
					$bytes = $bucket['unallocatedbytes'] + $row->getOriginal('bytes');
				}
				$row->bytes = $bytes;
			}
			else
			{
				return redirect()->back()->withError(trans('Missing or invalid bytes value'));
			}

			if ($bucket == null)
			{
				$unallocatedbytes = Number::formatBytes(0);
			}
			else
			{
				$unallocatedbytes = Number::formatBytes($bucket['unallocatedbytes'] + ($row->getOriginal('bytes') - $bytes));
			}

			if ($unallocatedbytes < 0)
			{
				$row->unallocatedbytes = Number::formatBytes(-($bucket['unallocatedbytes'] + ($row->getOriginal('bytes') - $bytes)));
				$row->overallocated    = 1;

				return redirect()->back()->withError('Over allocated bytes');
			}
		}

		// Look for this entry, duplicate name, etc.
		$q = Directory::query()
			->where('resourceid', '=', $row->resourceid)
			->where('groupid', '=', $row->groupid)
			->where('parentstoragedirid', '=', $row->parentstoragedirid)
			->where('name', '=', $row->name)
			//->where('datetimecreated', '<=', Carbon::now()->toDateTimeString())
			->whereNull('datetimeremoved');
		if ($id)
		{
			$q->where('id', '!=', $id);
		}
		$exist = $q
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
//echo '<pre>';
//print_r($row->toArray());echo '</pre>';die();
		if (!$row->save())
		{
			return redirect()->back()->withError(trans('global.messages.save failed'));
		}

		// If we have are requesting an autopopulate dir, then let's populate with the current list of users
		if ($row->autouser > 0)
		{
			$members = $row->autounixgroup->members;

			foreach ($members as $member)
			{
				// Set up object to pass back to ourselfs
				$data = [
					'bytes'       => 0,
					//'bytesource'  => '',
					'groupid'     => $row->groupid,
					'name'        => $member->user->username,
					'parentstoragedirid' => $row->id,
					'resourceid'  => $row->resourceid,
					'storageresourceid'  => $row->storageresourceid,
					'unixgroupid' => $row->unixgroupid,
					'owneruserid' => $member->userid,
					'ownerread'   => 1,
					'ownerwrite'  => 1,
				];

				if ($row->autouser == 1)
				{
					// Group readable
					$data['groupread']  = 1;
					$data['groupwrite'] = 0;
					$data['publicread'] = 0;
				}
				elseif ($row->autouser == 2)
				{
					// Private
					$data['groupread']  = 0;
					$data['groupwrite'] = 0;
					$data['publicread'] = 0;
				}
				elseif ($row->autouser == 3)
				{
					// Group readable writable
					$data['groupread']  = 1;
					$data['groupwrite'] = 1;
					$data['publicread'] = 0;
				}

				$this->store($request, $data, 10);
			}
		}

		return $this->cancel()->with('success', trans('global.messages.item saved'));
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
				$request->session()->flash('error', trans('global.messages.delete failed'));
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
