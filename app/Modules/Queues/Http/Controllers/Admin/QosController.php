<?php

namespace App\Modules\Queues\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Modules\Queues\Models\Qos;
use App\Modules\Queues\Models\QueueQos;
use App\Modules\Queues\Models\Scheduler;
use App\Halcyon\Http\StatefulRequest;

class QosController extends Controller
{
	/**
	 * Display a listing of the queue.
	 * 
	 * @param  StatefulRequest $request
	 * @return View
	 */
	public function index(StatefulRequest $request)
	{
		// Get filters
		$filters = array(
			'search'   => null,
			'priority' => null,
			'scheduler' => null,
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

		if ($filters['scheduler'])
		{
			$query->where('scheduler_id', '=', $filters['scheduler']);
		}

		$rows = $query
			->withCount('queues')
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		$schedulers = Scheduler::orderBy('hostname', 'asc')->get();

		return view('queues::admin.qos.index', [
			'rows'    => $rows,
			'filters' => $filters,
			'schedulers' => $schedulers,
		]);
	}

	/**
	 * Show the form for creating a new queue.
	 * 
	 * @return View
	 */
	public function create()
	{
		$row = new Qos();

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		$qoses = Qos::query()
			->orderBy('scheduler_id', 'asc')
			->orderBy('name', 'asc')
			->get();

		$schedulers = Scheduler::orderBy('hostname', 'asc')->get();

		return view('queues::admin.qos.edit', [
			'row' => $row,
			'qoses' => $qoses,
			'schedulers' => $schedulers,
		]);
	}

	/**
	 * Show the form for editing the specified queue.
	 *
	 * @param  int $id
	 * @return View
	 */
	public function edit($id)
	{
		$row = Qos::withTrashed()->findOrFail($id);

		if (!$row)
		{
			abort(404);
		}

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		$qoses = Qos::query()
			->where('id', '!=', $row->id)
			->orderBy('scheduler_id', 'asc')
			->orderBy('name', 'asc')
			->get();

		$schedulers = Scheduler::orderBy('hostname', 'asc')->get();

		return view('queues::admin.qos.edit', [
			'row' => $row,
			'qoses' => $qoses,
			'schedulers' => $schedulers,
		]);
	}

	/**
	 * Update the specified queue in storage.
	 * 
	 * @param  Request $request
	 * @return RedirectResponse
	 */
	public function store(Request $request)
	{
		$rules = [
			'scheduler_id' => 'required|integer',
			'name' => 'required|string|max:255',
			'description' => 'nullable|string',
			'flags' => 'nullable|array',
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

		$row = $id ? Qos::withTrashed()->findOrFail($id) : new Qos();

		foreach ($rules as $key => $rule)
		{
			if ($request->has($key))
			{
				if ($key == 'flags')
				{
					$row->{$key} = implode(',', $request->input($key, []));
				}
				else
				{
					$row->{$key} = $request->input($key);
				}
			}
			else
			{
				$row->{$key} = null;
			}

			if (!$row->{$key})
			{
				if ($key == 'preempt_mode' || $key == 'priority')
				{
					$row->{$key} = 0;
				}
				if ($key == 'usage_factor')
				{
					$row->{$key} = 1.0000;
				}
			}
		}

		if (!$row->save())
		{
			return redirect()->back()->withError(trans('global.messages.save failed'));
		}

		return $this->cancel()->withSuccess(trans('global.messages.item ' . ($id ? 'updated' : 'created')));
	}

	/**
	 * Remove the specified queue from storage.
	 *
	 * @param  Request $request
	 * @return RedirectResponse
	 */
	public function delete(Request $request)
	{
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		foreach ($ids as $id)
		{
			$row = Qos::findOrFail($id);

			if ($row->trashed())
			{
				$row->forceDelete();
			}
			elseif (!$row->delete())
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
	 * Restore one or more trashed entries
	 * 
	 * @param   Request $request
	 * @return  RedirectResponse
	 */
	public function restore(Request $request)
	{
		// Incoming
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		// Check for an ID
		if (count($ids) < 1)
		{
			$request->session()->flash('warning', trans('menus::menus.select to restore'));
			return $this->cancel();
		}

		$success = 0;

		// Update record(s)
		foreach ($ids as $id)
		{
			$row = Qos::withTrashed()->findOrFail(intval($id));

			if (!$row->restore())
			{
				$request->session()->flash('error', trans('global.messages.restore failed'));
				continue;
			}

			$success++;
		}

		// Set message
		if ($success)
		{
			$request->session()->flash('success', trans('menus::menus.items restored', ['count' => $success]));
		}

		return $this->cancel();
	}

	/**
	 * Return to default page
	 *
	 * @return  RedirectResponse
	 */
	public function cancel()
	{
		return redirect(route('admin.queues.qos'));
	}
}
