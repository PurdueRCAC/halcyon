<?php

namespace App\Modules\Queues\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Queues\Models\Queue;
use App\Modules\Queues\Models\Type;
use App\Halcyon\Http\StatefulRequest;

class TypesController extends Controller
{
	/**
	 * Display a listing of the queue.
	 * 
	 * @param  StatefulRequest  $request
	 * @return Response
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

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->state('queues.types.filter_' . $key, $key, $default);
		}

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
	 * @return Response
	 */
	public function create()
	{
		$row = new Type();

		return view('queues::admin.types.edit', [
			'row' => $row
		]);
	}

	/**
	 * Show the form for editing the specified queue.
	 * 
	 * @param  integer  $id
	 * @return Response
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
		$request->validate([
			'fields.name' => 'required|string|max:20'
		]);

		$id = $request->input('id');

		$row = $id ? Type::findOrFail($id) : new Type();
		$row->name = $request->input('fields.name');

		if (!$row->save())
		{
			$error = $row->getError() ? $row->getError() : trans('global.messages.save failed');

			return redirect()->back()->withError($error);
		}

		return $this->cancel()->withSuccess(trans('global.messages.item ' . ($id ? 'updated' : 'created'), ['name' => trans('queues::queues.type')]));
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
		return redirect(route('admin.queues.types'));
	}
}
