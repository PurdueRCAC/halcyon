<?php

namespace App\Modules\Queues\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Queues\Models\Scheduler;
use App\Modules\Queues\Models\SchedulerPolicy;
use App\Modules\Resources\Entities\Batchsystem;
use App\Modules\Resources\Entities\Asset;
use App\Halcyon\Http\StatefulRequest;

class SchedulersController extends Controller
{
	/**
	 * Display a listing of the queue.
	 * @return Response
	 */
	public function index(StatefulRequest $request)
	{
		// Get filters
		$filters = array(
			'search'   => null,
			'batchsystem' => null,
			'policy' => null,
			'state' => 'enabled',
			// Paging
			'limit'    => config('list_limit', 20),
			'page'     => 1,
			// Sorting
			'order'     => Scheduler::$orderBy,
			'order_dir' => Scheduler::$orderDir,
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->state('queues.schedulers.filter_' . $key, $key, $default);
		}

		if (!in_array($filters['order'], ['id', 'hostname']))
		{
			$filters['order'] = Scheduler::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Scheduler::$orderDir;
		}

		// Build query
		$query = Scheduler::query();

		if ($filters['state'] == 'trashed')
		{
			$query->onlyTrashed();
			$query->where('datetimeremoved', '!=', '0000-00-00 00:00:00');
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
	 * @return Response
	 */
	public function create()
	{
		$row = new Scheduler();

		return view('queues::admin.schedulers.edit', [
			'row' => $row
		]);
	}

	/**
	 * Show the form for editing the specified queue.
	 * @return Response
	 */
	public function edit($id)
	{
		$row = Scheduler::findOrFail($id);

		if ($fields = app('request')->old('fields'))
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
	 * @param  Request $request
	 * @return Response
	 */
	public function store(Request $request)
	{
		$request->validate([
			'fields.name' => 'required|max:20'
		]);

		$id = $request->input('id');

		$row = $id ? Scheduler::findOrFail($id) : new Scheduler();

		//$row->fill($request->input('fields'));
		$row->set([
			'name' => $request->input('name')
		]);

		if (!$row->save())
		{
			$error = $row->getError() ? $row->getError() : trans('messages.save failed');

			return redirect()->back()->withError($error);
		}

		return $this->cancel()->withSuccess(trans('messages.update success'));
	}

	/**
	 * Remove the specified queue from storage.
	 * @return Response
	 */
	public function delete(Request $request)
	{
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		foreach ($ids as $id)
		{
			$row = Scheduler::findOrFail($id);

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
	 * Return to default page
	 *
	 * @return  Response
	 */
	public function cancel()
	{
		return redirect(route('admin.queues.schedulers'));
	}
}
