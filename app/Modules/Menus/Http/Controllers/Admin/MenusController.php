<?php

namespace App\Modules\Menus\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Menus\Models\Type;
use App\Modules\Menus\Models\Item;
use App\Halcyon\Access\Viewlevel;
use App\Halcyon\Http\StatefulRequest;

class MenusController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * @return Response
	 */
	public function index(StatefulRequest $request)
	{
		// Get filters
		$filters = array(
			'search'   => null,
			'state'    => 'published',
			'access'   => null,
			'parent'   => 0,
			// Paging
			'limit'    => config('list_limit', 20),
			'page'     => 1,
			// Sorting
			'order'     => Type::$orderBy,
			'order_dir' => Type::$orderDir,
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->state('menus.filter_' . $key, $key, $default);
		}

		if (!in_array($filters['order'], ['id', 'title', 'published', 'access', 'items_count']))
		{
			$filters['order'] = Type::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Type::$orderDir;
		}

		// Get records
		$query = Type::query();

		$rows = $query
			->withCount('items')
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		$results = app('db')
			->table('widgets AS a')
			->select(['a.id', 'a.title', 'a.params', 'a.position', 'ag.title AS access_title'])
			->leftJoin((new Viewlevel)->getTable() . ' AS ag', 'ag.id', 'a.access')
			->where('widget', '=', 'menu')
			->get();

		$widgets = array();

		foreach ($results as $result)
		{
			$params = new \App\Halcyon\Config\Registry($result->params);

			$menuType = $params->get('menutype');
			if (!isset($widgets[$menuType]))
			{
				$widgets[$menuType] = array();
			}
			$widgets[$menuType][] = $result;
		}

		return view('menus::admin.menus.index', [
			'rows'    => $rows,
			'filters' => $filters,
			'widgets' => $widgets
		]);
	}

	/**
	 * Show the form for creating a new article
	 *
	 * @return  Response
	 */
	public function create()
	{
		app('request')->merge(['hidemainmenu' => 1]);

		$row = new Type();

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		return view('menus::admin.menus.edit', [
			'row' => $row
		]);
	}

	/**
	 * Show the form for editing the specified entry
	 *
	 * @param   integer  $id
	 * @return  Response
	 */
	public function edit($id)
	{
		app('request')->merge(['hidemainmenu' => 1]);

		$row = Type::findOrFail($id);

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		return view('menus::admin.menus.edit', [
			'row' => $row
		]);
	}

	/**
	 * Store a newly created entry
	 *
	 * @param   Request  $request
	 * @return  Response
	 */
	public function store(Request $request)
	{
		$request->validate([
			'fields.menutype' => 'required|string|max:24',
			'fields.title' => 'required|string|max:48'
		]);

		$id = $request->input('id');

		$row = $id ? Type::findOrFail($id) : new Type();
		$row->fill($request->input('fields'));

		if (!$row->save())
		{
			$error = $row->getError() ? $row->getError() : trans('messages.save failed');

			return redirect()->back()->withError($error);
		}

		return $this->cancel()->with('success', trans('messages.item saved'));
	}

	/**
	 * Remove the specified entry
	 *
	 * @param   Request  $request
	 * @return  Response
	 */
	public function delete(Request $request)
	{
		// Incoming
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		foreach ($ids as $id)
		{
			// Delete the entry
			// Note: This is recursive and will also remove all descendents
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
		return redirect(route('admin.menus.index'));
	}

	/**
	 * Rebuild the nested set tree.
	 *
	 * @return  bool  False on failure or error, true on success.
	 */
	public function rebuild(Request $request, $menutype)
	{
		// Initialise variables.
		$model = new Item;

		if ($model->rebuild(1))
		{
			// Reorder succeeded.
			$request->session()->flash('success', trans('COM_MENUS_ITEMS_REBUILD_SUCCESS'));
		}
		else
		{
			// Rebuild failed.
			$request->session()->flash('error', trans('COM_MENUS_ITEMS_REBUILD_FAILED'));
		}

		return redirect(route('admin.menus.items', ['menutype' => $menutype]));
	}
}
