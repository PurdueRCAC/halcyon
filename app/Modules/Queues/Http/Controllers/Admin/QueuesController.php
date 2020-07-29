<?php

namespace App\Modules\Queues\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Halcyon\Http\StatefulRequest;
use App\Modules\Queues\Models\Queue;
use App\Modules\Queues\Models\Type;
use App\Modules\Queues\Models\Scheduler;
use App\Modules\Queues\Models\SchedulerPolicy;
use App\Modules\Resources\Entities\Subresource;
use App\Modules\Resources\Entities\Child;
use App\Modules\Resources\Entities\Asset;

class QueuesController extends Controller
{
	/**
	 * Display a listing of the queue.
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
			//'start'    => $request->input('limitstart', 0),
			// Sorting
			'order'     => Queue::$orderBy,
			'order_dir' => Queue::$orderDir,
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->state('queues.filter_' . $key, $key, $default);
		}

		if (!in_array($filters['order'], ['id', 'name', 'state', 'type', 'parent', 'queuetype', 'groupid']))
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
			->whereNull($r . '.datetimeremoved');

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
			$query->onlyTrashed();
			//$query->where($q . '.datetimeremoved', '!=', '0000-00-00 00:00:00');
		}
		elseif ($filters['state'] == 'enabled')
		{
			$query//->where('datetimeremoved', '=', '0000-00-00 00:00:00')
				->where($q . '.enabled', '=', 1);
		}
		else
		{
			$query//->where('datetimeremoved', '=', '0000-00-00 00:00:00')
				->where($q . '.enabled', '=', 0);
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
			$query->where($r . '.id', '=', (int)$filters['resource']);
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

		$resources = (new Asset)->tree();
		/*$resources = Asset::query()
			->where('batchsystem', '>=', 0)
			->orderBy('name', 'asc')
			->get();*/

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
	 * @return Response
	 */
	public function create(Request $request)
	{
		app('request')->merge(['hidemainmenu' => 1]);

		$row = new Queue();
		$row->queuetype = 1;
		$row->maxjobsqueued = 12000;
		$row->maxjobsqueueduser = 5000;
		$row->priority = 1000;

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
	 * Store a newly created queue in storage.
	 * @param  Request $request
	 * @return Response
	 */
	/*public function store(Request $request)
	{
		$request->validate([
			'name' => 'required'
		]);

		$queue = new Queue([
			'name'         => $request->get('name'),
			'parentid'     => $request->get('parentid'),
			'rolename'     => $request->get('rolename'),
			'listname'     => $request->get('listname'),
			'queuetype' => $request->get('queuetype'),
			'producttype'  => $request->get('producttype')
		]);

		$queue->save();

		return $this->cancel()->with('success', 'Queue saved!');
	}*/

	/**
	 * Show the form for editing the specified queue.
	 * @return Response
	 */
	public function edit(Request $request, $id)
	{
		app('request')->merge(['hidemainmenu' => 1]);

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
	 * @param  Request $request
	 * @return Response
	 */
	public function store(Request $request, $id)
	{
		$request->validate([
			'fields.name' => 'required|string|max:64',
			'fields.schedulerid' => 'required|integer',
			'fields.subresourceid' => 'required|integer',
			'fields.groupid' => 'nullable|integer',
			'fields.free' => 'nullable|integer',
		]);

		$id = $request->input('id');

		$row = $id ? Queue::findOrFail($id) : new Queue();

		$row->fill($request->input('fields'));

		if (!$row->save())
		{
			$error = $row->getError() ? $row->getError() : trans('messages.save failed');

			return redirect()->back()->withError($error);
		}

		/*if ($row->scheduler->resource
		 && $row->scheduler->resource->rolename)
		{
			foreach ($queue->group->managers as $user)
			{
				event($resourcemember = new ResourceMemberStatus($user, $queue->scheduler->resource));

				if ($resourcemember->status <= 0)
				{
					throw new \Exception(__METHOD__ . '(): Bad status for `resourcemember` ' . $user->id);
				}
				elseif ($resourcemember->status == 1 || $resourcemember->status == 4)
				{
					event($resourcemember = new ResourceMemberCreated($user, $queue->scheduler->resource));
				}
			}
		}*/

		return $this->cancel()->withSuccess(trans('messages.update success'));
	}

	/**
	 * Sets the state of one or more entries
	 * 
	 * @return  void
	 */
	public function state(Request $request, $id)
	{
		$action = app('request')->segment(count($request->segments()) - 1);
		$state  = $action == 'enable' ? 1 : 0;

		// Incoming
		$ids = $request->input('id', array($id));
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

			if ($row->enabled == $state)
			{
				continue;
			}

			$row->timestamps = false;
			$row->state = $state;

			if (!$row->save())
			{
				$request->session()->flash('error', $row->getError());
				continue;
			}

			$success++;
		}

		// Set message
		if ($success)
		{
			$msg = $state
				? 'queues::queues.items enabled'
				: 'queues::queues.items disabled';

			$request->session()->flash('success', trans($msg, ['count' => $success]));
		}

		return $this->cancel();
	}

	/**
	 * Remove the specified queue from storage.
	 * @return Response
	 */
	public function delete($id)
	{
		$queue = Queue::find($id);

		if (!$queue->trashed())
		{
			$queue->delete();
		}
		else
		{
			$queue->forceDelete();
		}

		return $this->cancel()->with('success', 'Queue deleted!');
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
