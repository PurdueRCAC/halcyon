<?php

namespace App\Modules\Menus\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Modules\Menus\Models\Type;
use App\Modules\Menus\Models\Item;
use App\Halcyon\Access\Viewlevel;
use App\Halcyon\Http\StatefulRequest;

class MenusController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * 
	 * @param  StatefulRequest $request
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

		$reset = false;
		$request = $request->mergeWithBase();
		foreach ($filters as $key => $default)
		{
			if ($key != 'page'
			 && $request->has($key) && session()->has('menus.filter_' . $key)
			 && $request->input($key) != session()->get('menus.filter_' . $key))
			{
				$reset = true;
			}
			$filters[$key] = $request->state('menus.filter_' . $key, $key, $default);
		}
		$filters['page'] = $reset ? 1 : $filters['page'];

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
			$params = new \Illuminate\Config\Repository($result->params ? json_decode($result->params, true) : []);

			$menuType = $params->get('menutype');
			if (!isset($widgets[$menuType]))
			{
				$widgets[$menuType] = array();
			}
			$widgets[$menuType][] = $result;
		}

		$widget = app('db')
			->table('extensions')
			->select(['id', 'name', 'element'])
			->where('type', '=', 'widget')
			->where('client_id', '=', 0)
			->where('enabled', '=', 1)
			->where('element', '=', 'menu')
			->orderBy('element', 'asc')
			->first();

		return view('menus::admin.menus.index', [
			'rows'    => $rows,
			'filters' => $filters,
			'widgets' => $widgets,
			'menuwidget' => $widget
		]);
	}

	/**
	 * Show the form for creating a new article
	 *
	 * @return  Response
	 */
	public function create()
	{
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
		//$request->validate([
		$rules = [
			'fields.menutype' => 'required|string|max:24',
			'fields.title' => 'required|string|max:48'
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
		$row->fill($request->input('fields'));

		if (!$row->save())
		{
			$error = $row->getError() ? $row->getError() : trans('global.messages.save failed');

			return redirect()->back()->withError($error);
		}

		return $this->cancel()->with('success', trans('global.messages.item ' . ($id ? 'updated' : 'created')));
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
		return redirect(route('admin.menus.index'));
	}

	/**
	 * Rebuild the nested set tree.
	 *
	 * @param   Request $request
	 * @param   string  $menutype
	 * @return  Response
	 */
	public function rebuild(Request $request, $menutype)
	{
		// Initialise variables.
		$model = new Item;

		if ($model->rebuild(1))
		{
			// Reorder succeeded.
			$request->session()->flash('success', trans('menus::menus.rebuild succeeded'));
		}
		else
		{
			// Rebuild failed.
			$request->session()->flash('error', trans('menus::menus.rebuild failed'));
		}

		return redirect(route('admin.menus.items', ['menutype' => $menutype]));
	}
}
