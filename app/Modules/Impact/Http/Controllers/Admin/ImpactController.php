<?php

namespace App\Modules\Impact\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Halcyon\Http\StatefulRequest;
use App\Modules\Impact\Models\Table;
use App\Modules\Impact\Models\Impact;
use App\Modules\Impact\Models\AwardReport;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ImpactController extends Controller
{
	/**
	 * Display a listing of tags
	 *
	 * @param  StatefulRequest $request
	 * @return Response
	 */
	public function index(StatefulRequest $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			// Paging
			'limit'     => config('list_limit', 20),
			'page'      => 1,
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->state('impact.filter_' . $key, $key, $default);
		}
		$filters['start'] = ($filters['limit'] * $filters['page']) - $filters['limit'];

		$it = (new Table)->getTable();
		$i  = (new Impact)->getTable();

		$all_rows = app('db')
			->table($it)
			->join($i, $i . '.impacttableid', $it . '.id')
			->select(
				$it . '.name AS name',
				$i . '.name AS rowname',
				$it . '.columnname as columnname',
				$i . '.value as value',
				$it . '.sequence AS itsequence',
				$i . '.sequence AS isequence',
				DB::raw('MAX(' . $i . '.updatedatetime) AS updated')
			)
			->where($i . '.name', '!=', '')
			->where($i . '.value', '!=', '')
			->groupBy($it . '.sequence')
			->groupBy($i . '.sequence')
			->groupBy($it . '.name')
			->groupBy($i . '.name')
			->groupBy($it . '.columnname')
			->groupBy($i . '.value')
			->orderBy($it . '.sequence', 'asc')
			->orderBy($i . '.sequence', 'asc')
			->get();

		$data = AwardReport::query()
			->where('awardeecount', '!=', 0)
			->orderBy('fiscalyear', 'desc')
			->get();

		$updatedatetime = Impact::query()
			->select(DB::raw('MAX(updatedatetime) AS updated'))
			->first();

		$updatedatetime = Carbon::parse($updatedatetime->updated);

		return view('impact::admin.index', [
			'all_rows' => $all_rows->toArray(),
			'data' => $data,
			'updatedatetime' => $updatedatetime,
			'filters' => $filters
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		$parents = Department::tree();

		$row = new Department();

		return view('groups::admin.departments.edit', [
			'row' => $row,
			'parents' => $parents
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
		$parents = Department::tree();

		$row = Department::findOrFail($id);

		return view('groups::admin.departments.edit', [
			'row' => $row,
			'parents' => $parents,
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

		$row = $id ? Department::findOrFail($id) : new Department();
		$row->fill($request->input('fields'));

		if (!$row->save())
		{
			return redirect()->back()->withError(trans('global.messages.save failed'));
		}

		return $this->cancel()->with('success', trans('global.messages.item saved'));
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
			$row = Department::findOrFail($id);

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

		return $this->cancel();
	}

	/**
	 * Return to the main view
	 *
	 * @return  Response
	 */
	public function cancel()
	{
		return redirect(route('admin.groups.departments'));
	}
}
