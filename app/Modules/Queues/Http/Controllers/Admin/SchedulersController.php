<?php

namespace App\Modules\Queues\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Modules\Queues\Models\Scheduler;
use App\Modules\Queues\Models\SchedulerPolicy;
use App\Modules\Resources\Models\Batchsystem;
use App\Modules\Resources\Models\Asset;
use App\Halcyon\Http\Concerns\UsesFilters;

class SchedulersController extends Controller
{
	use UsesFilters;

	/**
	 * Display a listing of the entries
	 * 
	 * @param  Request $request
	 * @return View
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = $this->getStatefulFilters($request, 'queues.schedulers', [
			'search'    => null,
			'batchsystem' => null,
			'policy'    => null,
			'state'     => 'enabled',
			// Paging
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			// Sorting
			'order'     => Scheduler::$orderBy,
			'order_dir' => Scheduler::$orderDir,
		]);

		if (!in_array($filters['order'], ['id', 'hostname', 'defaultmaxwalltime', 'schedulerpolicyid', 'batchsystem']))
		{
			$filters['order'] = Scheduler::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Scheduler::$orderDir;
		}

		// Build query
		$query = Scheduler::query();

		if ($filters['search'])
		{
			if (is_numeric($filters['search']))
			{
				$query->where('id', '=', $filters['search']);
			}
			else
			{
				$query->where('hostname', 'like', '%' . $filters['search'] . '%');
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

		if ($filters['batchsystem'])
		{
			$query->where('batchsystem', '=', $filters['batchsystem']);
		}

		if ($filters['policy'])
		{
			$query->where('schedulerpolicyid', '=', $filters['policy']);
		}

		$rows = $query
			->withCount('queues')
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		$policies = SchedulerPolicy::orderBy('name', 'asc')->get();
		$batchsystems = Batchsystem::orderBy('name', 'asc')->get();

		return view('queues::admin.schedulers.index', [
			'rows'    => $rows,
			'filters' => $filters,
			'policies' => $policies,
			'batchsystems' => $batchsystems,
		]);
	}

	/**
	 * Show the form for creating a new queue.
	 *
	 * @param  Request $request
	 * @return View
	 */
	public function create(Request $request)
	{
		$row = new Scheduler();
		$row->defaultmaxwalltime = 1209600;

		if ($fields = $request->old('fields'))
		{
			$row->fill($fields);
		}

		$policies = SchedulerPolicy::orderBy('name', 'asc')->get();
		$batchsystems = Batchsystem::orderBy('name', 'asc')->get();
		$resources = (new Asset)->tree();

		return view('queues::admin.schedulers.edit', [
			'row' => $row,
			'policies' => $policies,
			'batchsystems' => $batchsystems,
			'resources' => $resources,
		]);
	}

	/**
	 * Show the form for editing the specified queue.
	 *
	 * @param  Request $request
	 * @param  int $id
	 * @return View
	 */
	public function edit(Request $request, $id)
	{
		$row = Scheduler::findOrFail($id);

		if ($fields = $request->old('fields'))
		{
			$row->fill($fields);
		}

		$policies = SchedulerPolicy::orderBy('name', 'asc')->get();
		$batchsystems = Batchsystem::orderBy('name', 'asc')->get();
		$resources = (new Asset)->tree();

		return view('queues::admin.schedulers.edit', [
			'row' => $row,
			'policies' => $policies,
			'batchsystems' => $batchsystems,
			'resources' => $resources,
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
			'fields.hostname' => 'required|string|max:64',
			'fields.queuesubresourceid' => 'required|integer|min:1',
			'fields.batchsystem' => 'nullable|integer|min:1',
			'fields.datetimedraindown' => 'nullable|string',
			'fields.datetimelastimportstart' => 'nullable|string',
			'fields.schedulerpolicyid' => 'nullable|integer',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$id = $request->input('id');

		$row = Scheduler::findOrNew($id);
		$row->fill($request->input('fields'));

		$walltime = $request->input('maxwalltime');

		/*$row->defaultmaxwalltime = match ($request->input('unit'))
		{
			'days'    => ($walltime * 24 * 60 * 60),
			'hours'   => ($walltime * 60 * 60),
			'minutes' => ($walltime * 60),
			default   => $walltime,
		};*/

		switch ($request->input('unit'))
		{
			case 'days':
				$row->defaultmaxwalltime = $walltime * 24 * 60 * 60;
			break;
			case 'hours':
				$row->defaultmaxwalltime = $walltime * 60 * 60;
			break;
			case 'minutes':
				$row->defaultmaxwalltime = $walltime * 60;
			break;
			default:
				$row->defaultmaxwalltime = $walltime;
			break;
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
			$row = Scheduler::findOrFail($id);

			if (!$row)
			{
				continue;
			}

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
	 * Return to default page
	 *
	 * @return  RedirectResponse
	 */
	public function cancel()
	{
		return redirect(route('admin.queues.schedulers'));
	}
}
