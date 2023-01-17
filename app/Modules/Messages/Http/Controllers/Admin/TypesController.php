<?php

namespace App\Modules\Messages\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use App\Modules\Messages\Models\Type;
use App\Modules\Resources\Models\Asset;
use App\Halcyon\Http\StatefulRequest;

class TypesController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @param   StatefulRequest  $request
	 * @return  View
	 */
	public function index(StatefulRequest $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			'order'     => Type::$orderBy,
			'order_dir' => Type::$orderDir,
		);

		$reset = false;
		$request = $request->mergeWithBase();
		foreach ($filters as $key => $default)
		{
			if ($key != 'page'
			 && $request->has($key) //&& session()->has('messages.types.filter_' . $key)
			 && $request->input($key) != session()->get('messages.types.filter_' . $key))
			{
				$reset = true;
			}
			$filters[$key] = $request->state('messages.types.filter_' . $key, $key, $default);
		}
		$filters['page'] = $reset ? 1 : $filters['page'];

		if (!in_array($filters['order'], ['id', 'name', 'resourceid', 'classname']))
		{
			$filters['order'] = Type::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Type::$orderDir;
		}

		$query = Type::query()
			->with('resource');

		if ($filters['search'])
		{
			$query->where('name', 'like', '%' . $filters['search'] . '%');
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		return view('messages::admin.types.index', [
			'filters' => $filters,
			'rows'    => $rows,
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return  View
	 */
	public function create()
	{
		$row = new Type();

		$resources = (new Asset)->tree();

		return view('messages::admin.types.edit', [
			'row' => $row,
			'resources' => $resources
		]);
	}

	/**
	 * Show the form for editing the specified entry
	 *
	 * @param   integer   $id
	 * @return  View
	 */
	public function edit($id)
	{
		$row = Type::findOrFail($id);

		$resources = (new Asset)->tree();

		return view('messages::admin.types.edit', [
			'row' => $row,
			'resources' => $resources
		]);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param   Request  $request
	 * @return  Response
	 */
	public function store(Request $request)
	{
		$request->validate([
			'fields.name' => 'required|string|max:24',
			'fields.classname' => 'nullable|string|max:24',
			'fields.resourceid' => 'nullable|integer',
		]);

		$id = $request->input('id');

		$row = $id ? Type::findOrFail($id) : new Type();

		$row->fill($request->input('fields'));

		if (!$row->save())
		{
			return redirect()->back()->withError(trans('global.messages.save failed'));
		}

		return $this->cancel()->with('success', trans('global.messages.item saved'));
	}

	/**
	 * Remove the specified entry
	 *
	 * @param   Request $request
	 * @return  Response
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
	 * Return to the main view
	 *
	 * @return  Response
	 */
	public function cancel()
	{
		return redirect(route('admin.messages.types'));
	}
}
