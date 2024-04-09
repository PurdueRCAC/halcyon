<?php

namespace App\Modules\Menus\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Modules\Menus\Models\Type;
use App\Modules\Menus\Models\Item;
use App\Halcyon\Access\Viewlevel;
use App\Halcyon\Http\Concerns\UsesFilters;

class MenusController extends Controller
{
	use UsesFilters;

	/**
	 * Display a listing of the resource.
	 * 
	 * @param  Request $request
	 * @return View
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = $this->getStatefulFilters($request, 'menus', [
			'search'    => null,
			'state'     => 'published',
			'access'    => null,
			'parent'    => 0,
			// Paging
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			// Sorting
			'order'     => Type::$orderBy,
			'order_dir' => Type::$orderDir,
		]);

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

		if ($filters['search'])
		{
			$query->where(function($where) use ($filters)
			{
				$where->where('title', 'like', '%' . $filters['search'] . '%')
					->orWhere('menutype', 'like', '%' . $filters['search'] . '%')
					->orWhere('description', 'like', '%' . $filters['search'] . '%');
			});
		}

		if ($filters['state'] == 'trashed')
		{
			$query->onlyTrashed();
		}
		elseif ($filters['state'] == '*')
		{
			$query->withTrashed();
		}

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
	 * @param   Request  $request
	 * @return  View
	 */
	public function create(Request $request)
	{
		$row = new Type();

		if ($fields = $request->old('fields'))
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
	 * @param   Request  $request
	 * @param   int  $id
	 * @return  View
	 */
	public function edit(Request $request, int $id)
	{
		$row = Type::findOrFail($id);

		if ($fields = $request->old('fields'))
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
	 * @return  RedirectResponse
	 */
	public function store(Request $request)
	{
		$rules = [
			'title' => 'required|string|max:48',
			'menutype' => 'required|string|max:24',
			'description' => 'nullable|string|max:255'
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$id = $request->input('id');

		$row = Type::findOrNew($id);
		foreach ($rules as $key => $rule)
		{
			if ($request->has($key))
			{
				$row->{$key} = $request->input($key);
			}
		}

		if (!$row->save())
		{
			return redirect()->back()->withError(trans('global.messages.save failed'));
		}

		return $this->cancel()->with('success', trans('global.messages.item ' . ($id ? 'updated' : 'created')));
	}

	/**
	 * Remove the specified entry
	 *
	 * @param   Request  $request
	 * @return  RedirectResponse
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
			$row = Type::find($id);

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
		return redirect(route('admin.menus.index'));
	}

	/**
	 * Rebuild the nested set tree.
	 *
	 * @param   Request $request
	 * @param   string  $menutype
	 * @return  RedirectResponse
	 */
	public function rebuild(Request $request, string $menutype)
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

	/**
	 * Sets the state of one or more entries
	 * 
	 * @param   Request $request
	 * @return  RedirectResponse
	 */
	public function restore(Request $request)
	{
		// Incoming
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		// Check for an ID
		if (count($ids) < 1)
		{
			$request->session()->flash('warning', trans('menus::menus.select to restore'));
			return $this->cancel();
		}

		$success = 0;

		// Update record(s)
		foreach ($ids as $id)
		{
			$row = Type::withTrashed()->findOrFail(intval($id));

			if (!$row->restore())
			{
				$request->session()->flash('error', trans('global.messages.restore failed'));
				continue;
			}

			$success++;
		}

		// Set message
		if ($success)
		{
			$request->session()->flash('success', trans('menus::menus.items restored', ['count' => $success]));
		}

		return $this->cancel();
	}
}
