<?php

namespace App\Modules\Courses\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Halcyon\Http\StatefulRequest;
use App\Modules\Courses\Models\Account;
use App\Modules\Courses\Events\InstructorLookup;
use App\Modules\Resources\Models\Asset;
use Carbon\Carbon;

class AccountsController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * 
	 * @param  StatefulRequest  $request
	 * @return Response
	 */
	public function index(StatefulRequest $request)
	{
		$filters = array(
			'search' => '',
			'resourceid'  => 0,
			'userid' => null,
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

		if ($filters['userid'])
		{
			$query->where('userid', '=', $filters['userid']);
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
	 * 
	 * @param   Request  $request
	 * @return Response
	 */
	public function create(Request $request)
	{
		$row = new Account();
		$row->userid = $request->input('userid');
		$row->datetimestart = Carbon::now();
		$row->datetimestop = Carbon::now()->modify('+5 months');

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		event($event = new InstructorLookup($row->user));

		$courses = $event->courses;
		$resources = (new Asset)->tree();

		return view('courses::admin.edit', [
			'row'     => $row,
			'classes' => $courses,
			'resources' => $resources,
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
		$row = Account::findOrFail($id);

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		event($event = new InstructorLookup($row->user));

		$courses = $event->courses;
		$resources = (new Asset)->tree();

		return view('courses::admin.edit', [
			'row'     => $row,
			'classes' => $courses,
			'resources' => $resources,
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
		$rules = [
			'fields.crn' => 'nullable|string|max:8',
			'fields.department' => 'nullable|string|max:4',
			'fields.coursenumber' => 'nullable|string|max:8',
			'fields.classname' => 'required|string|max:255',
			'fields.resourceid' => 'required|integer|min:1',
			'fields.groupid' => 'nullable|integer|min:1',
			'fields.userid' => 'nullable|integer|min:1',
			'fields.datetimestart' => 'required|date',
			'fields.datetimestop' => 'nullable|date',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$id = $request->input('id');
		$type = $request->input('type');

		$row = $id ? Account::findOrFail($id) : new Account();
		$row->fill($request->input('fields'));

		if ($type == 'workshop')
		{
			if ($row->classname == '')
			{
				return redirect()->back()->withError(trans('Required field `classname` is empty'));
			}
			if ($row->datetimestart == '')
			{
				return redirect()->back()->withError(trans('Required field `start` is empty'));
			}
			if ($row->datetimestop == '')
			{
				return redirect()->back()->withError(trans('Required field `stop` is empty'));
			}
			$row->datetimestart = Carbon::parse($row->datetimestart)->modify('-86400 seconds')->toDateTimeString();
			$row->datetimestop  = Carbon::parse($row->datetimestop)->modify('+86400 seconds')->toDateTimeString();
			$row->crn = uniqid();
			$row->semester = 'Workshop';
			$row->reference = $row->semester;
			$row->department = '';
			$row->coursenumber = '';
		}
		else
		{
			// Check to see if CRN is already in the system.
			// TODO: are CRNs unique?
			// TODO: fine tune date range. Does requested date range overlap with another?
			$exist = Account::query()
				->where('crn', '=', $row->crn)
				->where('semester', '=', $row->semester)
				->where(function ($where)
				{
					$where->whereNull('datetimeremoved')
						->orWhere('datetimeremoved', '=', '0000-00-00 00:00:00');
				})
				->get()
				->first();

			if ($exist)
			{
				return redirect()->back()->withError(trans('Record with provided `crn` already exists'));
			}

			// Fetch information about class from input.
			event($event = new AccountLookup($row));

			$row = $event->account;

			if (!$row->crn)
			{
				// Invalid CRN/classID provided
				return redirect()->back()->withError(trans('Invalid CRN/classID provided'));
			}

			$row->datetimestart = Carbon::parse($row->datetimestart)->modify('-259200 seconds')->toDateTimeString();
			$row->datetimestop  = Carbon::parse($row->datetimestop)->modify('+604800 seconds')->toDateTimeString();
			$row->notice = 1;
		}

		if (!$row->save())
		{
			$error = $row->getError() ? $row->getError() : trans('global.messages.save failed');

			return redirect()->back()->withError($error);
		}

		return $this->cancel()->withSuccess(trans('global.messages.update success'));
	}

	/**
	 * Remove the specified entry
	 *
	 * @param   Request  $request
	 * @return  Response
	 */
	public function delete(Request $request)
	{
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		foreach ($ids as $id)
		{
			$row = Job::findOrFail($id);

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
	 * Return to default page
	 *
	 * @return  Response
	 */
	public function cancel()
	{
		return redirect(route('admin.courses.index'));
	}
}
