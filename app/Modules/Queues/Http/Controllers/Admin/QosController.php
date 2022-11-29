<?php

namespace App\Modules\Queues\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Modules\Queues\Models\Qos;
use App\Modules\Queues\Models\QueueQos;
use App\Halcyon\Http\StatefulRequest;

class QosController extends Controller
{
	/**
	 * Display a listing of the queue.
	 * 
	 * @param  StatefulRequest $request
	 * @return Response
	 */
	public function index(StatefulRequest $request)
	{
		// Get filters
		$filters = array(
			'search'   => null,
			'priority' => null,
			'state' => 'enabled',
			// Paging
			'limit'    => config('list_limit', 20),
			'page'     => 1,
			// Sorting
			'order'     => Qos::$orderBy,
			'order_dir' => Qos::$orderDir,
		);

		$reset = false;
		$request = $request->mergeWithBase();
		foreach ($filters as $key => $default)
		{
			if ($key != 'page'
			 && $request->has($key) //&& session()->has('queues.qos.filter_' . $key)
			 && $request->input($key) != session()->get('queues.qos.filter_' . $key))
			{
				$reset = true;
			}
			$filters[$key] = $request->state('queues.qos.filter_' . $key, $key, $default);
		}
		$filters['page'] = $reset ? 1 : $filters['page'];

		if (!in_array($filters['order'], ['id', 'name', 'description', 'limit_factor', 'priority']))
		{
			$filters['order'] = Qos::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Qos::$orderDir;
		}

		// Build query
		$query = Qos::query();

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

		if ($filters['state'] == 'trashed')
		{
			$query->onlyTrashed();
		}
		elseif ($filters['state'] == '*')
		{
			$query->withTrashed();
		}

		if ($filters['priority'])
		{
			$query->where('priority', '=', $filters['priority']);
		}

		$rows = $query
			->withCount('queues')
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		return view('queues::admin.qos.index', [
			'rows'    => $rows,
			'filters' => $filters,
		]);
	}

	/**
	 * Show the form for creating a new queue.
	 * 
	 * @return Response
	 */
	public function create()
	{
		$row = new Qos();

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		$qoses = Qos::query()
			->orderBy('name', 'asc')
			->get();

		return view('queues::admin.qos.edit', [
			'row' => $row,
			'qoses' => $qoses,
		]);
	}

	/**
	 * Show the form for editing the specified queue.
	 * 
	 * @return Response
	 */
	public function edit($id)
	{
		$row = Qos::findOrFail($id);

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		$qoses = Qos::query()
			->where('id', '!=', $row->id)
			->orderBy('name', 'asc')
			->get();

		return view('queues::admin.qos.edit', [
			'row' => $row,
			'qoses' => $qoses,
		]);
	}

	/**
	 * Update the specified queue in storage.
	 * 
	 * @param  Request $request
	 * @return Response
	 */
	public function store(Request $request)
	{
		$rules = [
			'name' => 'required|string|max:255',
			'description' => 'nullable|string',
			'max_jobs_pa' => 'nullable|integer',
			'max_jobs_per_user' => 'nullable|integer',
			'max_jobs_accrue_pa' => 'nullable|integer',
			'max_jobs_accrue_pu' => 'nullable|integer',
			'min_prio_thresh' => 'nullable|integer',
			'max_submit_jobs_pa' => 'nullable|integer',
			'max_submit_jobs_per_user' => 'nullable|integer',
			'max_tres_pa' => 'nullable|string',
			'max_tres_pj' => 'nullable|string',
			'max_tres_pn' => 'nullable|string',
			'max_tres_pu' => 'nullable|string',
			'max_tres_mins_pj' => 'nullable|integer',
			'max_tres_run_mins_pa' => 'nullable|integer',
			'max_tres_run_mins_pu' => 'nullable|integer',
			'min_tres_pj' => 'nullable|string',
			'max_wall_duration_per_job' => 'nullable|integer',
			'grp_jobs' => 'nullable|integer',
			'grp_jobs_accrue' => 'nullable|integer',
			'grp_submit_jobs' => 'nullable|integer',
			'grp_tres' => 'nullable|string',
			'grp_tres_mins' => 'nullable|integer',
			'grp_tres_run_mins' => 'nullable|integer',
			'grp_wall' => 'nullable|integer',
			'preempt' => 'nullable|string',
			'preempt_mode' => 'nullable|integer',
			'preempt_exempt_time' => 'nullable|integer',
			'priority' => 'nullable|integer',
			'usage_factor' => 'nullable|string',
			'usage_thres' => 'nullable|string',
			'limit_factor' => 'nullable|string',
			'grace_time' => 'nullable|integer',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$id = $request->input('id');

		$row = $id ? Qos::findOrFail($id) : new Qos();

		foreach ($rules as $key => $rule)
		{
			if ($request->has($key))
			{
				$row->{$key} = $request->input($key);
			}
		}

		if (!$row->save())
		{
			$error = $row->getError() ? $row->getError() : trans('global.messages.save failed');

			return redirect()->back()->withError($error);
		}

		return $this->cancel()->withSuccess(trans('global.messages.item ' . ($id ? 'updated' : 'created')));
	}

	/**
	 * Remove the specified queue from storage.
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
			$row = Qos::findOrFail($id);

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
		return redirect(route('admin.queues.qos'));
	}
}
