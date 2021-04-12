<?php

namespace App\Modules\Groups\Http\Controllers\Site;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Halcyon\Http\StatefulRequest;
use App\Modules\Groups\Models\Group;
use App\Modules\Groups\Models\Member;
use App\Modules\Groups\Models\Type;
use App\Modules\Users\Models\User;

class MembersController extends Controller
{
	/**
	 * Display a listing of tags
	 *
	 * @param  integer  $group
	 * @param  StatefulRequest  $request
	 * @return Response
	 */
	public function index($group, StatefulRequest $request)
	{
		// Get filters
		$filters = array(
			'group'     => $group,
			'search'    => null,
			'state'     => 'active',
			'type'      => 0,
			// Paging
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			// Sorting
			'order'     => Member::$orderBy,
			'order_dir' => Member::$orderDir,
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->state('groups.members.' . $key, $key, $default);
		}

		if (!in_array($filters['order'], ['id', 'name', 'created_at', 'updated_at']))
		{
			$filters['order'] = Member::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Member::$orderDir;
		}

		$group = Group::findOrFail($filters['group']);

		$u = (new User)->getTable();
		$m = (new Member)->getTable();

		$query = Member::query()
			->join($u, $u . '.id', $m . '.userid')
			->select($m . '.*', $u . '.name')
			->with('type')
			->where($m . '.groupid', '=', $group->id);

		if ($filters['search'])
		{
			$filters['search'] = strtolower((string)$filters['search']);

			$query->where($u . '.name', 'like', '%' . $filters['search'] . '%');
		}

		if ($filters['type'])
		{
			$query->where($m . '.membertype', '=', $filters['type']);
		}

		if ($filters['state'] == 'active')
		{
			$query->withTrashed()
				->whereNull($u . '.deleted_at') //, '=', '0000-00-00 00:00:00')
				->where($m . '.dateremoved', '=', '0000-00-00 00:00:00');
		}
		elseif ($filters['state'] == 'trashed')
		{
			$query->onlyTrashed();
		}
		else
		{
			$query->withTrashed();
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		$types = Type::query()->whereIn('id', [1, 2, 3])->orderBy('name', 'asc')->get();

		return view('groups::admin.members.index', [
			'rows'    => $rows,
			'filters' => $filters,
			'group'   => $group,
			'types'   => $types,
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		app('request')->merge(['hidemainmenu' => 1]);

		$row = new Group();

		return view('groups::admin.edit', [
			'row' => $row
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
			'fields.name' => 'required'
		]);

		$id = $request->input('id');

		$row = $id ? Group::findOrFail($id) : new Group();
		$row->fill($request->input('fields'));
		$row->slug = $row->normalize($row->name);

		if (!$row->created_by)
		{
			$row->created_by = auth()->user()->id;
		}

		if (!$row->updated_by)
		{
			$row->updated_by = auth()->user()->id;
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
	 * @param  integer $id
	 * @return Response
	 */
	public function edit($id)
	{
		app('request')->merge(['hidemainmenu' => 1]);

		$row = Member::findOrFail($id);

		return view('groups::admin.members.edit', [
			'row' => $row,
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
		return redirect(route('admin.groups.members'));
	}
}
