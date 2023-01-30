<?php

namespace App\Modules\Queues\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Modules\Queues\Models\Queue;
use App\Modules\Queues\Models\Type;
use App\Halcyon\Http\StatefulRequest;

class TypesController extends Controller
{
	/**
	 * Display a listing of the queue.
	 * 
	 * @param  StatefulRequest  $request
	 * @return View
	 */
	public function index(StatefulRequest $request)
	{
		// Get filters
		$filters = array(
			'search'   => null,
			// Paging
			'limit'    => config('list_limit', 20),
			'page'     => 1,
			// Sorting
			'order'     => Type::$orderBy,
			'order_dir' => Type::$orderDir,
		);

		$reset = false;
		$request = $request->mergeWithBase();
		foreach ($filters as $key => $default)
		{
			if ($key != 'page'
			 && $request->has($key) //&& session()->has('queues.types.filter_' . $key)
			 && $request->input($key) != session()->get('queues.types.filter_' . $key))
			{
				$reset = true;
			}
			$filters[$key] = $request->state('queues.types.filter_' . $key, $key, $default);
		}
		$filters['page'] = $reset ? 1 : $filters['page'];

		if (!in_array($filters['order'], ['id', 'name']))
		{
			$filters['order'] = Type::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Type::$orderDir;
		}

		// Build query
		$query = Type::query();

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

		$rows = $query
			->withCount('queues')
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		return view('queues::admin.types.index', [
			'rows'    => $rows,
			'filters' => $filters
		]);
	}

	/**
	 * Show the form for creating a new queue.
	 * 
	 * @return View
	 */
	public function create()
	{
		$row = new Type();

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		return view('queues::admin.types.edit', [
			'row' => $row
		]);
	}

	/**
	 * Show the form for editing the specified queue.
	 * 
	 * @param  int  $id
	 * @return View
	 */
	public function edit($id)
	{
		$row = Type::find($id);

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		return view('queues::admin.types.edit', [
			'row' => $row
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
			'fields.name' => 'required|string|max:20'
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$id = $request->input('id');

		$row = $id ? Type::findOrFail($id) : new Type();
		$row->name = $request->input('fields.name');

		if (!$row->save())
		{
			return redirect()->back()->withError(trans('global.messages.save failed'));
		}

		return $this->cancel()->withSuccess(trans('global.messages.item ' . ($id ? 'updated' : 'created')));
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
			$row = Type::findOrFail($id);

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
	 * @return  Response
	 */
	public function cancel()
	{
		return redirect(route('admin.queues.types'));
	}
}
