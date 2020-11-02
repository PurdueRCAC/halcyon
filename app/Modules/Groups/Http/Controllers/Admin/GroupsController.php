<?php

namespace App\Modules\Groups\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Halcyon\Http\StatefulRequest;
use App\Halcyon\Models\FieldOfScience;
use App\Modules\Groups\Models\Group;
use App\Modules\Groups\Models\Department;
use App\Modules\Groups\Models\GroupDepartment;
use App\Modules\Groups\Models\GroupFieldOfScience;
use App\Modules\Groups\Events\GroupDisplay;

class GroupsController extends Controller
{
	/**
	 * Display a listing of tags
	 *
	 * @return Response
	 */
	public function index(StatefulRequest $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			'state'     => null,
			'department' => 0,
			'fieldofscience' => 0,
			// Paging
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			// Sorting
			'order'     => Group::$orderBy,
			'order_dir' => Group::$orderDir,
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->state('groups.filter_' . $key, $key, $default);
		}

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
		$row = new Group();

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
		$request->validate([
			'fields.name' => 'required|max:255',
			'fields.unixgroup' => 'nullable|max:10',
		]);

		$id = $request->input('id');

		$row = $id ? Group::findOrFail($id) : new Group();
		$row->fill($request->input('fields'));

		// Verify UNIX group is sane - this is just a first pass,
		// would still need to make sure this is not a duplicate anywhere, etc
		if ($row->unixgroup)
		{
			if (!preg_match('/^[a-z][a-z0-9\-]{0,8}[a-z0-9]$/', $row->unixgroup))
			{
				return redirect()->back()->withError(trans('Field `unixgroup` not in valid format'));
			}

			$exists = Group::findByUnixgroup($row->unixgroup);

			// Check for a duplicate
			if ($exists)
			{
				return redirect()->back()->withError(trans('`unixgroup` ' . $row->unixgroup . ' already exists'));
			}
		}

		if (!$row->save())
		{
			$error = $row->getError() ? $row->getError() : trans('messages.save failed');

			return redirect()->back()->withError($error);
		}

		return $this->cancel()->with('success', trans('messages.item saved'));
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @return Response
	 */
	public function edit($id)
	{
		$row = Group::findOrFail($id);

		$departments = Department::tree();
		$fields = FieldOfScience::tree();

		event($event = new GroupDisplay($row, 'details'));
		$sections = collect($event->getSections());

		return view('groups::admin.groups.edit', [
			'row' => $row,
			'departments' => $departments,
			'fields' => $fields,
			'sections' => $sections,
		]);
	}

	/**
	 * Remove the specified resource from storage.
	 *
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
			$request->session()->flash('success', trans('messages.item deleted', ['count' => $success]));
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
