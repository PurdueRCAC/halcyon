<?php

namespace App\Modules\Groups\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Halcyon\Http\StatefulRequest;
use App\Halcyon\Models\FieldOfScience;
use App\Modules\Groups\Models\Group;
use App\Modules\Groups\Models\Department;
use App\Modules\Groups\Models\GroupDepartment;
use App\Modules\Groups\Models\GroupFieldOfScience;
use App\Modules\Groups\Events\GroupDisplay;
use App\Modules\Groups\Events\UnixGroupFetch;

class GroupsController extends Controller
{
	/**
	 * Display a listing of tags
	 *
	 * @param  StatefulRequest  $request
	 * @return Response
	 */
	public function index(StatefulRequest $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			'state'     => 'active',
			'department' => 0,
			'fieldofscience' => 0,
			// Paging
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			// Sorting
			'order'     => Group::$orderBy,
			'order_dir' => Group::$orderDir,
		);

		$reset = false;
		$request = $request->mergeWithBase();
		foreach ($filters as $key => $default)
		{
			if ($key != 'page'
			 && $request->has($key) //&& session()->has('groups.filter_' . $key)
			 && $request->input($key) != session()->get('groups.filter_' . $key))
			{
				$reset = true;
			}
			$filters[$key] = $request->state('groups.filter_' . $key, $key, $default);
		}
		$filters['page'] = $reset ? 1 : $filters['page'];

		if (!in_array($filters['order'], array('id', 'name', 'unixgroup', 'members_count')))
		{
			$filters['order'] = Group::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Group::$orderDir;
		}

		$query = Group::query();

		$g = (new Group)->getTable();

		if ($filters['search'])
		{
			if (is_numeric($filters['search']))
			{
				$query->where($g . '.id', '=', $filters['search']);
			}
			else
			{
				$filters['search'] = strtolower((string)$filters['search']);

				$query->where(function ($where) use ($filters, $g)
				{
					$where->where($g . '.name', 'like', '%' . $filters['search'] . '%')
						->orWhere($g . '.unixgroup', 'like', '%' . $filters['search'] . '%');
				});
			}
		}

		if ($filters['state'] == 'trashed')
		{
			$query->onlyTrashed();
		}
		elseif ($filters['state'] == '*')
		{
			$query->withTrashed();
		}

		if ($filters['department'])
		{
			$gd = (new GroupDepartment)->getTable();
			$query->join($gd, $gd . '.groupid', $g . '.id')
				->where($gd . '.collegedeptid', $filters['department']);
		}

		if ($filters['fieldofscience'])
		{
			$gf = (new GroupFieldOfScience)->getTable();
			$query->join($gf, $gf . '.groupid', $g . '.id')
				->where($gf . '.fieldofscienceid', $filters['fieldofscience']);
		}

		$rows = $query
			->withCount('members')
			->with('departmentList')
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		$departments = Department::tree();
		$fields = FieldOfScience::tree();

		return view('groups::admin.groups.index', [
			'rows'    => $rows,
			'filters' => $filters,
			'departments' => $departments,
			'fields' => $fields,
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		$row = new Group;
		$row->cascademanagers = 1;

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		$departments = Department::tree();
		$fields = FieldOfScience::tree();

		return view('groups::admin.groups.edit', [
			'row' => $row,
			'departments' => $departments,
			'fields' => $fields,
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
		//$request->validate([
		$rules = [
			'fields.name' => 'required|string|max:255',
			'fields.unixgroup' => 'nullable|string|max:48',
			'fields.cascademanagers' => 'nullable|integer',
			'fields.prefix_unixgroup' => 'nullable|integer',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$id = $request->input('id');

		$row = $id ? Group::findOrFail($id) : new Group;
		$row->fill($request->input('fields'));
		$row->unixgroup = $row->unixgroup ? $row->unixgroup : '';
		if (!$request->has('fields.cascademanagers') || !$request->input('fields.cascademanagers'))
		{
			$row->cascademanagers = 0;
		}
		else
		{
			$row->cascademanagers = 1;
		}
		if (!$request->has('fields.prefix_unixgroup') || !$request->input('fields.prefix_unixgroup'))
		{
			$row->prefix_unixgroup = 0;
		}
		else
		{
			$row->prefix_unixgroup = 1;
		}

		// Verify UNIX group is sane - this is just a first pass,
		// would still need to make sure this is not a duplicate anywhere, etc
		if ($row->unixgroup)
		{
			//if (!preg_match('/^[a-z][a-z0-9\-]{0,8}[a-z0-9]$/', $row->unixgroup)
			// && !preg_match('/^[a-z][a-z0-9\-]{0,48}$/', $row->unixgroup))
			if (!preg_match('/^[a-z0-9][a-z0-9\-]*[a-z0-9]+$/', $row->unixgroup))
			{
				return redirect()->back()->withError(trans('Field `unixgroup` not in valid format'));
			}

			$exists = Group::findByUnixgroup($row->unixgroup);

			// Check for a duplicate
			if ($exists && $exists->id != $row->id)
			{
				return redirect()->back()->withError(trans('`unixgroup` "' . $row->unixgroup . '" already exists'));
			}

			// Check to make sure this base name doesn't exist elsewhere
			event($event = new UnixGroupFetch($row->unixgroup));

			$rows = $event->results;

			if (count($rows) > 0)
			{
				return response()->json(['message' => trans('groups::groups.error.unixgroup name already exists', ['name' => $row->unixgroup])], 409);
			}
		}

		if (!$row->save())
		{
			$error = $row->getError() ? $row->getError() : trans('global.messages.save failed');

			return redirect()->back()->withError($error);
		}

		return $this->cancel()->with('success', trans('global.messages.item saved'));
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  integer  $id
	 * @return Response
	 */
	public function edit($id)
	{
		$row = Group::findOrFail($id);

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		$departments = Department::tree();
		$fields = FieldOfScience::tree();

		return view('groups::admin.groups.edit', [
			'row' => $row,
			'departments' => $departments,
			'fields' => $fields,
		]);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  integer  $id
	 * @return Response
	 */
	public function show($id)
	{
		$row = Group::findOrFail($id);

		$departments = Department::tree();
		$fields = FieldOfScience::tree();

		event($event = new GroupDisplay($row, 'details'));
		$sections = collect($event->getSections());

		return view('groups::admin.groups.show', [
			'row' => $row,
			'departments' => $departments,
			'fields' => $fields,
			'sections' => $sections,
		]);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param   Request  $request
	 * @return Response
	 */
	public function delete(Request $request)
	{
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		foreach ($ids as $id)
		{
			$row = Group::findOrFail($id);

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

		return $this->cancel();
	}

	/**
	 * Return to the main view
	 *
	 * @return  Response
	 */
	public function cancel()
	{
		return redirect(route('admin.groups.index'));
	}
}
