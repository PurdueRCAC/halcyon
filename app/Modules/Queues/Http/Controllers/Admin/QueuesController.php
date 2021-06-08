<?php

namespace App\Modules\Queues\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Halcyon\Http\StatefulRequest;
use App\Modules\Queues\Models\Queue;
use App\Modules\Queues\Models\Type;
use App\Modules\Queues\Models\Scheduler;
use App\Modules\Queues\Models\SchedulerPolicy;
use App\Modules\Queues\Models\Walltime;
use App\Modules\Queues\Models\Size;
use App\Modules\Resources\Models\Subresource;
use App\Modules\Resources\Models\Child;
use App\Modules\Resources\Models\Asset;

class QueuesController extends Controller
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
			'search'    => null,
			'state'     => 'enabled',
			'type'      => 0,
			'scheduler' => 0,
			'resource'  => 0,
			'class'     => null,
			// Paging
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			// Sorting
			'order'     => Queue::$orderBy,
			'order_dir' => Queue::$orderDir,
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->state('queues.filter_' . $key, $key, $default);
		}

		if (!in_array($filters['order'], ['id', 'name', 'enabled', 'type', 'parent', 'queuetype', 'groupid']))
		{
			$filters['order'] = Queue::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Queue::$orderDir;
		}

		// Build query
		$q = (new Queue)->getTable();
		$c = (new Child)->getTable();
		$r = (new Asset)->getTable();

		$query = Queue::query()
			->select($q . '.*')
			->join($c, $c . '.subresourceid', $q . '.subresourceid')
			->join($r, $r . '.id', $c . '.resourceid')
			->where(function($where) use ($r)
			{
				$where->whereNull($r . '.datetimeremoved')
					->orWhere($r . '.datetimeremoved', '=', '0000-00-00 00:00:00');
			})
			->withTrashed();

		if ($filters['search'])
		{
			if (is_numeric($filters['search']))
			{
				$query->where($q . '.id', '=', (int)$filters['search']);
			}
			else
			{
				$query->where($q . '.name', 'like', '%' . strtolower((string)$filters['search']) . '%');
			}
		}

		if ($filters['state'] == 'trashed')
		{
			$query->where(function($where) use ($q)
			{
				$where->whereNotNull($q . '.datetimeremoved')
					->where($q . '.datetimeremoved', '!=', '0000-00-00 00:00:00');
			});
		}
		elseif ($filters['state'] == 'enabled')
		{
			$query
				->where(function($where) use ($q)
				{
					$where->whereNull($q . '.datetimeremoved')
						->orWhere($q . '.datetimeremoved', '=', '0000-00-00 00:00:00');
				})
				->where($q . '.enabled', '=', 1);
		}
		elseif ($filters['state'] == 'disabled')
		{
			$query
				->where(function($where) use ($q)
				{
					$where->whereNull($q . '.datetimeremoved')
						->orWhere($q . '.datetimeremoved', '=', '0000-00-00 00:00:00');
				})
				->where($q . '.enabled', '=', 0);
		}
		else
		{
			$query
				->where(function($where) use ($q)
				{
					$where->whereNull($q . '.datetimeremoved')
						->orWhere($q . '.datetimeremoved', '=', '0000-00-00 00:00:00');
				});
		}

		if ($filters['type'] > 0)
		{
			$query->where($q . '.queuetype', '=', (int)$filters['type']);
		}

		if ($filters['scheduler'])
		{
			$query->where($q . '.schedulerid', '=', (int)$filters['scheduler']);
		}

		if ($filters['resource'])
		{
			if (substr($filters['resource'], 0, 1) == 's')
			{
				$query->where($q . '.subresourceid', '=', (int)substr($filters['resource'], 1));
			}
			else
			{
				$query->where($r . '.id', '=', (int)$filters['resource']);
			}
		}

		if ($filters['class'] == 'system')
		{
			$query->where($q . '.groupid', '<=', 0);
		}
		elseif ($filters['class'] == 'owner')
		{
			$query->where($q . '.groupid', '>', 0);
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		/*$resources = (new Asset)->tree();*/
		$resources = Asset::query()
			->withTrashed()
			->whereIsActive()
			->where('batchsystem', '>', 0)
			->where('listname', '!=', '')
			->orderBy('name', 'asc')
			->get();

		$types = Type::orderBy('name', 'asc')->get();

		return view('queues::admin.queues.index', [
			'rows'  => $rows,
			'types' => $types,
			'resources' => $resources,
			'filters' => $filters,
		]);
	}

	/**
	 * Show the form for creating a new queue.
	 * 
	 * @param  Request $request
	 * @return Response
	 */
	public function create(Request $request)
	{
		$row = new Queue();
		$row->queuetype = 1;
		$row->maxjobsqueued = 12000;
		$row->maxjobsqueueduser = 5000;
		$row->maxjobsrun = 0;
		$row->maxjobcores = 0;
		$row->maxjobsrunuser = 0;
		$row->maxijobfactor = 2;
		$row->maxijobuserfactor = 1;
		$row->nodecoresdefault = 0;
		$row->priority = 1000;
		$row->defaultwalltime = 0.5;
		$row->enabled = 1;

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		$types = Type::orderBy('name', 'asc')->get();
		$schedulers = Scheduler::orderBy('hostname', 'asc')->get();
		$schedulerpolicies = SchedulerPolicy::orderBy('name', 'asc')->get();
		$subresources = array();
		$resources = (new Asset)->tree();

		return view('queues::admin.queues.edit', [
			'row'   => $row,
			'types' => $types,
			'schedulers' => $schedulers,
			'schedulerpolicies' => $schedulerpolicies,
			'resources' => $resources,
			'subresources' => $subresources,
		]);
	}

	/**
	 * Show the form for editing the specified queue.
	 * 
	 * @param  Request $request
	 * @param  integer $id
	 * @return Response
	 */
	public function edit(Request $request, $id)
	{
		$row = Queue::find($id);

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		$types = Type::orderBy('name', 'asc')->get();
		$schedulers = Scheduler::orderBy('hostname', 'asc')->get();
		$schedulerpolicies = SchedulerPolicy::orderBy('name', 'asc')->get();
		$subresources = array();
		$resources = (new Asset)->tree();

		return view('queues::admin.queues.edit', [
			'row'   => $row,
			'types' => $types,
			'schedulers' => $schedulers,
			'schedulerpolicies' => $schedulerpolicies,
			'resources' => $resources,
			'subresources' => $subresources,
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
		//$request->validate([
			'fields.name' => 'required|string|max:64',
			'fields.schedulerid' => 'required|integer',
			'fields.subresourceid' => 'required|integer',
			'fields.groupid' => 'nullable|integer',
			'fields.free' => 'nullable|integer',
		//]);
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$id = $request->input('id');

		$row = $id ? Queue::findOrFail($id) : new Queue();
		$row->fill($request->input('fields'));
		if (!$row->groupid)
		{
			$row->groupid = -1;
		}

		if (!$id)
		{
			$exists = Queue::query()
				->withTrashed()
				->whereIsActive()
				->where('name', '=', $row->name)
				->where('schedulerid', '=', $row->schedulerid)
				->first();

			if ($exists)
			{
				return redirect()->back()->withError(trans('queues::queues.error.queue already exists'));
			}
		}

		if (!$request->has('fields.free'))
		{
			$row->free = 0;
		}

		if (!$request->has('fields.reservation'))
		{
			$row->reservation = 0;
		}

		if (!$row->aclgroups)
		{
			$row->aclgroups = '';
		}
		if (!$row->nodememmin)
		{
			$row->nodememmin = 0;
		}
		if (!$row->nodememmax)
		{
			$row->nodememmax = 0;
		}

		if (!$row->save())
		{
			$error = $row->getError() ? $row->getError() : trans('global.messages.save failed');

			return redirect()->back()->withError($error);
		}

		$walltime = Walltime::query()
			->where('queueid', '=', $row->id)
			->orderBy('id', 'asc')
			->first();
		if (!$walltime)
		{
			$walltime = new Walltime;
		}
		$walltime->queueid = $row->id;
		$walltime->walltime = intval($request->input('maxwalltime')) * 60 * 60;
		$walltime->save();

		if (!$id && $request->input('queueclass') == 'standby')
		{
			$size = new Size;
			$size->queueid = $row->id;
			$size->corecount = 20000;
			$size->datetimestart = $row->datetimecreated;
			$size->save();
		}

		return $this->cancel()->withSuccess(trans('global.messages.update success'));
	}

	/**
	 * Sets the state of one or more entries
	 * 
	 * @param  Request $request
	 * @param  integer $id
	 * @return  Response
	 */
	public function state(Request $request, $id = 0)
	{
		$action = app('request')->segment(3);
		$state  = $action == 'enable' ? 1 : 0;

		// Incoming
		$ids = $request->input('id', $id);
		$ids = (!is_array($ids) ? array($ids) : $ids);

		// Check for an ID
		if (count($ids) < 1)
		{
			$request->session()->flash('warning', trans($state ? 'queues::queues.select to enable' : 'queues::queues.select to disable'));
			return $this->cancel();
		}

		$success = 0;

		// Update record(s)
		foreach ($ids as $id)
		{
			$row = Queue::findOrFail(intval($id));

			if ($row->enabled != $state)
			{
				if (!$row->update(['enabled' => $state]))
				{
					$request->session()->flash('error', $row->getError());
					continue;
				}
			}

			$success++;
		}

		// Set message
		if ($success)
		{
			$msg = $state
				? 'queues::queues.messages.items enabled'
				: 'queues::queues.messages.items disabled';

			$request->session()->flash('success', trans($msg, ['count' => $success]));
		}

		return $this->cancel();
	}

	/**
	 * Sets the state of one or more entries
	 * 
	 * @param  Request $request
	 * @param  integer $id
	 * @return  Response
	 */
	public function scheduling(Request $request, $id = 0)
	{
		$action = app('request')->segment(3);
		$state  = $action == 'start' ? 1 : 0;

		// Incoming
		$ids = $request->input('id', $id);
		$ids = (!is_array($ids) ? array($ids) : $ids);

		// Check for an ID
		if (count($ids) < 1)
		{
			$request->session()->flash('warning', trans($state ? 'queues::queues.select to start' : 'queues::queues.select to stop'));
			return $this->cancel();
		}

		$success = 0;

		// Update record(s)
		foreach ($ids as $id)
		{
			$row = Queue::findOrFail(intval($id));

			if ($row->started != $state)
			{
				if (!$row->update(['started' => $state]))
				{
					$request->session()->flash('error', $row->getError());
					continue;
				}
			}

			$success++;
		}

		// Set message
		if ($success)
		{
			$msg = $state
				? 'queues::queues.messages.items started'
				: 'queues::queues.messages.items stopped';

			$request->session()->flash('success', trans($msg, ['count' => $success]));
		}

		return $this->cancel();
	}

	/**
	 * Remove the specified queue from storage.
	 * 
	 * @param  Request  $request
	 * @return Response
	 */
	public function delete(Request $request)
	{
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		foreach ($ids as $id)
		{
			$row = Queue::findOrFail($id);

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
		return redirect(route('admin.queues.index'));
	}
}
