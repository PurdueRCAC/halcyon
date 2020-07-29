<?php

namespace App\Modules\Courses\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Halcyon\Http\StatefulRequest;
use App\Modules\Courses\Models\Account;
use Carbon\Carbon;

class AccountsController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * @return Response
	 */
	public function index(StatefulRequest $request)
	{
		$filters = array(
			'search' => '',
			'resourceid'  => 0,
			'semester' => '',
			'state' => 'active',
			// Paging
			'limit' => config('list_limit', 20),
			'page' => 1,
			// Sorting
			'order' => 'datetimestart',
			'order_dir' => 'asc'
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->state('courses.filter_' . $key, $key, $default);
		}

		$query = Account::query();

		if ($filters['search'])
		{
			$query->where('classname', 'like', '%' . $filters['search'] . '%');
		}

		if ($filters['resourceid'])
		{
			$query->where('resourceid', '=', $filters['resourceid']);
		}

		if ($filters['semester'])
		{
			$query->where('semester', '=', $filters['semester']);
		}

		if ($filters['state'] == 'active')
		{
			$query->where(function ($where)
				{
					$where->whereNull('datetimestop')
						->orWhere('datetimestop', '=', '0000-00-00 00:00:00')
						->orWhere('datetimestop', '>', Carbon::now()->toDateTimeString());
				});
		}
		elseif ($filters['state'] == 'inactive')
		{
			$query->where(function ($where)
				{
					$where->whereNotNull('datetimestop')
						->where('datetimestop', '!=', '0000-00-00 00:00:00');
				});
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

		$semesters = Account::query()
			->select(DB::raw('DISTINCT(semester)'))
			->orderBy('semester', 'desc')
			->get();

		return view('courses::admin.index', [
			'rows'    => $rows,
			'filters' => $filters,
			'semesters' => $semesters,
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 * @return Response
	 */
	public function create()
	{
		$row = new Account();

		return view('courses::admin.edit', ['row' => $row]);
	}

	/**
	 * Store a newly created resource in storage.
	 * @param  Request $request
	 * @return Response
	 */
	public function store(Request $request)
	{
		$request->validate([
			'name' => 'required'
		]);

		$row = new Account;
		$row->fill([
			'name'         => $request->get('name'),
			'parentid'     => $request->get('parentid'),
			'rolename'     => $request->get('rolename'),
			'listname'     => $request->get('listname'),
			'resourcetype' => $request->get('resourcetype'),
			'producttype'  => $request->get('producttype')
		]);

		$order->save();

		event('onAfterSaveOrder', $order);

		return redirect(route('admin.courses.index'))->with('success', 'Resource saved!');
	}

	/**
	 * Show the form for editing the specified resource.
	 * @return Response
	 */
	public function edit($id)
	{
		$row = Account::findOrFail($id);

		return view('courses::admin.edit', [
			'row'      => $row,
		]);
	}

	/**
	 * Update the specified resource in storage.
	 * @param  Request $request
	 * @return Response
	 */
	public function update(Request $request, $id)
	{
		$request->validate([
			'name' => 'required'
		]);

		$order = Job::find($id);
		$order->set([
			'name'         => $request->get('name'),
			'parentid'     => $request->get('parentid'),
			'rolename'     => $request->get('rolename'),
			'listname'     => $request->get('listname'),
			'resourcetype' => $request->get('resourcetype'),
			'producttype'  => $request->get('producttype')
		]);

		$order->save();

		//event(new ResourceUpdated($order));
		event('onAfterSaveOrder', $order);

		return redirect(route('admin.courses.index'))->with('success', 'courses Job updated!');
	}

	/**
	 * Remove the specified resource from storage.
	 * @return Response
	 */
	public function delete($id)
	{
		$order = Job::find($id);
		$order->delete();

		return redirect(route('admin.courses.index'))->with('success', 'courses Job deleted!');
	}

	/**
	 * Return to default page
	 *
	 * @return  Response
	 */
	public function cancel()
	{
		return redirect(route('admin.courses.index'));
	}
}
