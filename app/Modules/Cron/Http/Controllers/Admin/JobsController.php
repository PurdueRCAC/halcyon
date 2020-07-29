<?php

namespace App\Modules\Cron\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Halcyon\Http\StatefulRequest;
use App\Modules\Cron\Models\Job;

class JobsController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * @return Response
	 */
	public function index(StatefulRequest $request)
	{
		$filters = array(
			'search' => '',
			'state'  => '*',
			'dont_overlap' => -1,
			// Paging
			'limit' => config('list_limit', 20),
			'page' => 1,
			// Sorting
			'order' => 'command',
			'order_dir' => 'asc'
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->state('cron.filter_' . $key, $key, $default);
		}

		$query = Job::query();

		if ($filters['search'])
		{
			$query->where(function($where) use ($filters)
			{
				$where->where('command', 'like', '%' . $filters['search'] . '%')
					->orWhere('description', 'like', '%' . $filters['search'] . '%');
			});
		}

		if ($filters['state'] == 'published')
		{
			$query->where('state', '=', 1);
		}
		elseif ($filters['state'] == 'unpublished')
		{
			$query->where('state', '=', 0);
		}

		if ($filters['dont_overlap'] == 1)
		{
			$query->where('active', '=', 0)
				->where('dont_overlap', '=', $filters['dont_overlap']);
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		return view('cron::admin.index', [
			'rows'    => $rows,
			'filters' => $filters,
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 * @return Response
	 */
	public function create()
	{
		$row = new Job;

		$commands = Job::getCommands();

		return view('cron::admin.edit', [
			'row'      => $row,
			'commands' => $commands,
		]);
	}

	/**
	 * Show the form for editing the specified resource.
	 * @return Response
	 */
	public function edit($id)
	{
		$row = Job::findOrFail($id);

		$commands = Job::getCommands();

		return view('cron::admin.edit', [
			'row'      => $row,
			'commands' => $commands,
		]);
	}

	/**
	 * Store a newly created resource in storage.
	 * @param  Request $request
	 * @return Response
	 */
	public function store(Request $request)
	{
		$request->validate([
			'fields.name' => 'required|string|max:255',
			'fields.command' => 'required|string|max:255',
			'fields.parameters' => 'nullable|string|max:255',
			'fields.state' => 'nullable|in:0,1',
			'fields.dont_overlap' => 'nullable|in:0,1',
		]);

		$id = $request->input('fields.id');
		$fields = $request->input('fields');

		$row = $id ? Job::findOrFail($id) : new Job;

		if ($id)
		{
			$row->updated_by = auth()->user()->id;
		}
		else
		{
			$row->created_by = auth()->user()->id;
		}

		$recurrence = array();
		if (isset($fields['minute']))
		{
			$recurrence[] = ($fields['minute']['c']) ? $fields['minute']['c'] : $fields['minute']['s'];
		}
		if (isset($fields['hour']))
		{
			$recurrence[] = ($fields['hour']['c']) ? $fields['hour']['c'] : $fields['hour']['s'];
		}
		if (isset($fields['day']))
		{
			$recurrence[] = ($fields['day']['c']) ? $fields['day']['c'] : $fields['day']['s'];
		}
		if (isset($fields['month']))
		{
			$recurrence[] = ($fields['month']['c']) ? $fields['month']['c'] : $fields['month']['s'];
		}
		if (isset($fields['dayofweek']))
		{
			$recurrence[] = ($fields['dayofweek']['c']) ? $fields['dayofweek']['c'] : $fields['dayofweek']['s'];
		}
		if (!empty($recurrence))
		{
			$fields['recurrence'] = implode(' ', $recurrence);
		}
		unset($fields['minute']);
		unset($fields['hour']);
		unset($fields['day']);
		unset($fields['month']);
		unset($fields['dayofweek']);

		$row->fill($fields);

		if (!$row->save())
		{
			$error = $row->getError() ? $row->getError() : trans('messages.save failed');

			return redirect()->back()->withError($error);
		}

		return $this->cancel()->with('success', trans('messages.item saved'));
	}

	/**
	 * Remove the specified resource from storage.
	 * @return Response
	 */
	public function delete($id)
	{
		$order = Job::findOrFail($id);
		$order->delete();

		return $this->cancel()->with('success', trans('messages.item deleted'));
	}

	/**
	 * Return to default page
	 *
	 * @return  Response
	 */
	public function cancel()
	{
		return redirect(route('admin.cron.index'));
	}
}
