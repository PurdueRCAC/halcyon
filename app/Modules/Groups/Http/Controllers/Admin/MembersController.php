<?php

namespace App\Modules\Groups\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use App\Halcyon\Http\Concerns\UsesFilters;
use App\Modules\Groups\Models\Group;
use App\Modules\Groups\Models\Member;
use App\Modules\Groups\Models\Type;
use App\Modules\Users\Models\UserUsername;
use App\Modules\Users\Models\User;

class MembersController extends Controller
{
	use UsesFilters;

	/**
	 * Display a listing of entries
	 *
	 * @param  int  $group
	 * @param  Request $request
	 * @return View
	 */
	public function index($group, Request $request)
	{
		// Get filters
		$filters = $this->getStatefulFilters($request, 'groups.members', [
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
		]);

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
		$uu = (new UserUsername)->getTable();
		$m = (new Member)->getTable();

		$query = Member::query()
			->join($uu, $uu . '.userid', $m . '.userid')
			->join($u, $u . '.id', $uu . '.userid')
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
				->whereNull($uu . '.dateremoved')
				->whereNull($m . '.dateremoved');
		}
		elseif ($filters['state'] == 'trashed')
		{
			//$query->onlyTrashed();
			$query->withTrashed()
				->where(function($where) use ($uu, $m)
				{
					$where->whereNotNull($uu . '.dateremoved')
						->orWhereNotNull($m . '.dateremoved');
				});
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
	 * @param  int  $group
	 * @return View
	 */
	public function create($group)
	{
		$row = new Member();

		return view('groups::admin.members.edit', [
			'row' => $row
		]);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int $group
	 * @param  int $id
	 * @return View
	 */
	public function edit($group, $id)
	{
		$row = Member::findOrFail($id);

		return view('groups::admin.members.edit', [
			'row' => $row,
		]);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  Request $request
	 * @return RedirectResponse
	 */
	public function store(Request $request)
	{
		$request->validate([
			'fields.membertype' => 'nullable|integer',
			'fields.userid' => 'required|integer',
			'fields.groupid' => 'required|integer'
		]);

		$id = $request->input('id');

		$row = Member::findOrNew($id);
		$row->fill($request->input('fields'));
		if (!$row->membertype)
		{
			$row->membertype = 1;
		}

		if (!$row->save())
		{
			return redirect()->back()->withError(trans('global.messages.save failed'));
		}

		return $this->cancel($row->groupid)->with('success', trans('global.messages.item saved'));
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  Request $request
	 * @param  int $group
	 * @return RedirectResponse
	 */
	public function delete(Request $request, $group)
	{
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		foreach ($ids as $id)
		{
			$row = Member::findOrFail($id);

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

		return $this->cancel($group);
	}

	/**
	 * Return to the main view
	 *
	 * @param   int  $group
	 * @return  RedirectResponse
	 */
	public function cancel($group)
	{
		return redirect(route('admin.groups.members', ['group' => $group]));
	}
}
