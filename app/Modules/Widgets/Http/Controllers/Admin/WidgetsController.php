<?php

namespace App\Modules\Widgets\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Modules\Widgets\Models\Widget;
use App\Modules\Widgets\Models\Menu;
use App\Modules\Users\Models\User;
use App\Halcyon\Access\Viewlevel;
use App\Halcyon\Http\StatefulRequest;

class WidgetsController extends Controller
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
			'search'    => null,
			'state'     => '*',
			'access'    => null,
			'position'  => null,
			'widget'    => null,
			'language'  => null,
			'client_id' => 0,
			// Pagination
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			'order'     => Widget::$orderBy,
			'order_dir' => Widget::$orderDir,
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->state('widgets.filter_' . $key, $key, $default);
		}

		if (!in_array($filters['order'], ['id', 'title', 'position', 'state', 'widget', 'access']))
		{
			$filters['order'] = Widget::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Widget::$orderDir;
		}

		//$rows = Widget::paginate($filters['limit']);

		$query = Widget::query();

		$p = (new Widget)->getTable();
		$u = (new User)->getTable();
		$a = (new Viewlevel)->getTable();
		$m = (new Menu)->getTable();
		$e = 'extensions';
		$l = 'languages';

		$query->select(
				$p . '.*',
				$l . '.title AS language_title',
				$u . '.name AS editor',
				$a . '.title AS access_level',
				DB::raw('MIN(' . $m . '.menuid) AS pages'),
				$e . '.name AS name'
			)
			->where($e . '.type', '=', 'widget')
			->where($p . '.client_id', '=', $filters['client_id']);

		// Join over the language
		$query
			//->select($l . '.title AS language_title')
			->leftJoin($l, $l . '.lang_code', $p . '.language');

		// Join over the users for the checked out user.
		$query
			//->select($u . '.name AS editor')
			->leftJoin($u, $u . '.id', $p . '.checked_out');

		// Join over the access groups.
		$query
			//->select($a . '.title AS access_level')
			->leftJoin($a, $a . '.id', $p . '.access');

		// Join over the access groups.
		$query
			//->select('MIN(' . $m . '.menuid) AS pages')
			->leftJoin($m, $m . '.widgetid', $p . '.id');

		// Join over the extensions
		$query
			//->select($e . '.name AS name')
			->join($e, $e . '.element', $p . '.widget', 'left')
			->groupBy(
				$p . '.id',
				$p . '.title',
				$p . '.note',
				$p . '.position',
				$p . '.widget',
				$p . '.language',
				$p . '.checked_out',
				$p . '.checked_out_time',
				$p . '.published',
				$p . '.access',
				$p . '.ordering',
				$p . '.content',
				$p . '.showtitle',
				$p . '.params',
				$p . '.client_id',
				$l . '.title',
				$u . '.name',
				$a . '.title',
				$e . '.name',
				//$l . '.lang_code',
				$u . '.id',
				$a . '.id',
				$m . '.widgetid',
				$e . '.element',
				$p . '.publish_up',
				$p . '.publish_down',
				$e . '.enabled'
			);

		// Filter by access level.
		if ($filters['access'])
		{
			$query->where($p . '.access', '=', (int) $filters['access']);
		}

		// Filter by published state
		/*if (is_numeric($filters['state']))
		{
			$query->where($p . '.published', '=', (int) $filters['state']);
		}
		elseif ($filters['state'] === '')
		{
			$query->whereIn($p . '.published', array(0, 1));
		}*/
		if ($filters['state'] == 'published')
		{
			$query->where($p . '.published', '=', 1);
		}
		elseif ($filters['state'] == 'unpublished')
		{
			$query->where($p . '.published', '=', 0);
		}
		elseif ($filters['state'] == 'trashed')
		{
			$query->where($p . '.published', '=', -2);
		}

		// Filter by position.
		if ($filters['position'])
		{
			if ($filters['position'] == 'none')
			{
				$filters['position'] = '';
			}
			$query->where($p . '.position', '=', $filters['position']);
		}

		// Filter by module.
		if ($filters['widget'])
		{
			$query->where($p . '.widget', '=', $filters['widget']);
		}

		// Filter by search
		if (!empty($filters['search']))
		{
			if (stripos($filters['search'], 'id:') === 0)
			{
				$query->where($p . '.id', '=', (int) substr($filters['search'], 3));
			}
			else
			{
				$query->where(function($where) use ($p, $filters)
				{
					$where->where($p . '.title', 'like', '%' . $filters['search'] . '%')
						->orWhere($p . '.note', 'like', '%' . $filters['search'] . '%');
				});
			}
		}

		// Filter by module.
		if ($filters['language'])
		{
			$query->where($p . '.language', '=', $filters['language']);
		}

		// Order records
		if ($filters['order'] == 'name')
		{
			$query->orderBy('name', $filters['order_dir']);
			$query->orderBy('ordering', 'asc');
		}
		else if ($filters['order'] == 'ordering')
		{
			$query->orderBy('position', 'asc');
			$query->orderBy('ordering', $filters['order_dir']);
			$query->orderBy('name', 'asc');
		}
		else
		{
			$query->orderBy($filters['order'], $filters['order_dir']);
			$query->orderBy('name', 'asc');
			$query->orderBy('ordering', 'asc');
		}

		$rows = $query
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);;

		// Select the required fields from the table.
		$items = app('db')->table('extensions')
			->select(['id', 'name', 'element'])
			->where('type', '=', 'widget')
			->where('client_id', '=', (int) $filters['client_id'])
			->where('enabled', '=', 1)
			->get();

		foreach ($items as $item)
		{
			$widget = ucfirst($item->element);
			$path = app_path() . '/Widgets/' . $widget;
			app('translator')->addNamespace('widget.' . $item->element, $path . '/lang');

			$item->name = trans('widget.' . $item->element . '::' . $item->element . '.widget name');
			$item->desc = trans('widget.' . $item->element . '::' . $item->element . '.widget desc');
		}

		$widgets = collect($items)->sortBy('name')->all();

		return view('widgets::admin.index', [
			'rows' => $rows,
			'filters' => $filters,
			'widgets' => $widgets,
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 * 
	 * @param  Request $request
	 * @return Response
	 */
	public function create(Request $request)
	{
		$row = new Widget;
		$row->published = 1;
		$row->access = 1;

		if ($eid = $request->input('eid', 0))
		{
			$db = app('db');

			$ext = $db->table('extensions')
				->select(['element', 'client_id'])
				->where('id', '=', $eid)
				//->where('element', '=', $eid)
				->where('type', '=', 'widget')
				->get()
				->first();

			if ($ext)
			{
				$row->widget = $ext->element;
				$row->client_id = $ext->client_id;
			}
		}

		if ($params = $request->input('params'))
		{
			foreach ($params as $key => $val)
			{
				$row->params->set($key, $val);
			}
		}

		$row->registerLanguage();

		return view('widgets::admin.edit', [
			'row'  => $row,
			'form' => $row->getForm()
		]);
	}

	/**
	 * Show the form for editing the specified resource.
	 * 
	 * @param  integer  $id
	 * @param  Request $request
	 * @return Response
	 */
	public function edit($id, Request $request)
	{
		$row = Widget::findOrFail($id);

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		// Fail if checked out not by 'me'
		if ($row->checked_out
		 && $row->checked_out <> auth()->user()->id)
		{
			return $this->cancel($request)->with('warning', trans('global.messages.checked out'));
		}

		if ($eid = $request->input('eid', 0))
		{
			$db = app('db');

			$ext = $db->table('extensions')
				->select('element, client_id')
				->where('id', '=', $eid)
				->where('type', '=', 'module')
				->first();

			if ($ext)
			{
				$row->module = $ext->element;
				$row->client_id = $ext->client_id;
			}
		}

		if ($row->id)
		{
			// Checkout the record
			if (!$row->checkOut())
			{
				// Check-out failed, display a notice but allow the user to see the record.
				return $this->cancel($request)->with('warning', trans('global.messages.check out failed'));
			}
		}

		$row->registerLanguage();

		return view('widgets::admin.edit', [
			'row'  => $row,
			'form' => $row->getForm(),
		]);
	}

	/**
	 * Update the specified resource in storage.
	 * 
	 * @param  Request $request
	 * @return Response
	 */
	public function store(Request $request)
	{
		$request->validate([
			'fields.title'    => 'required|string|max:100',
			'fields.position' => 'required|string|max:50',
			'fields.widget'   => 'required|string|max:50',
			'fields.params'   => 'nullable|array',
			'fields.menu'     => 'nullable|array',
		]);

		$id = $request->input('id');

		$row = $id ? Widget::findOrFail($id) : new Widget();

		$row->fill($request->input('fields'));
		$row->note = '';

		// Set params
		$row->params;

		if ($params = $request->input('fields.params'))
		{
			foreach ($params as $key => $val)
			{
				$row->params->set($key, $val);
			}
		}

		if (!$row->save())
		{
			$error = $row->getError() ? $row->getError() : trans('global.messages.save failed');

			return redirect()->back()->withError($error);
		}

		// Update menu assignments
		$menu = $request->input('menu', array());
		$assignment = (isset($menu['assignment']) ? $menu['assignment'] : 0);
		$assigned   = (isset($menu['assigned']) ? $menu['assigned'] : array());

		if (!$row->saveAssignment($assignment, $assigned))
		{
			$error = $row->getError() ? $row->getError() : trans('global.messages.save failed');

			return redirect()->back()->withError($error);
		}

		$row->checkin();

		return $this->cancel($request)->withSuccess(trans('global.messages.item ' . ($id ? 'updated' : 'created')));
	}

	/**
	 * Method to edit an existing record.
	 *
	 * @param  Request $request
	 * @return Response
	 */
	public function select(Request $request)
	{
		// Get filters
		$filters = array(
			'client_id' => 0,
			// Pagination
			'limit'     => config('list_limit', 20),
			'order'     => 'name',
			'order_dir' => 'asc',
		);

		foreach ($filters as $key => $default)
		{
			// Check request
			$val = $request->input('filter_' . $key);

			// If empty
			if (!$request->has('filter_' . $key))// && $val === null)
			{
				// Check the session
				$val = $request->session()->get('widgets.filter_' . $key, $default);
			}
			else
			{
				// Save to session
				$request->session()->put('widgets.filter_' . $key, $val);
			}

			$filters[$key] = $val;
		}

		// Select the required fields from the table.
		$items = app('db')->table('extensions')
			->select(['id', 'name', 'element'])
			->where('type', '=', 'widget')
			->where('client_id', '=', (int) $filters['client_id'])
			->where('enabled', '=', 1)
			->orderBy($filters['order'], $filters['order_dir'])
			->get();

		foreach ($items as $item)
		{
			if (substr($item->element, 0, 4) == 'mod_')
			{
				$item->element = substr($item->element, 4);
			}

			$widget = ucfirst($item->element);
			$path = app_path() . '/Widgets/' . $widget;
			app('translator')->addNamespace('widget.' . $item->element, $path . '/lang');

			$item->name = trans('widget.' . $item->element . '::' . $item->element . '.widget name');
			$item->desc = trans('widget.' . $item->element . '::' . $item->element . '.widget desc');
		}

		$items = collect($items)->sortBy('name')->all();

		return view('widgets::admin.select', [
			'items'   => $items,
			'filters' => $filters
		]);
	}

	/**
	 * Remove the specified item
	 * 
	 * @param  Request $request
	 * @return Response
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
			$row = Widget::findOrFail($id);

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

		return $this->cancel($request);
	}

	/**
	 * Sets the state of one or more entries
	 * 
	 * @param  Request $request
	 * @param  integer $id
	 * @return Response
	 */
	public function state(Request $request, $id)
	{
		$action = app('request')->segment(count($request->segments()) - 1);
		$value  = $action == 'publish' ? 1 : 0;

		// Incoming
		$ids = $request->input('id', array($id));
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		foreach ($ids as $id)
		{
			// Load the record
			$model = Widget::findOrFail(intval($id));

			// Set state
			$model->timestamps = false;
			$model->published = $value;

			if (!$model->save())
			{
				$request->session()->flash('error', $model->getError());
				continue;
			}

			$success++;
		}

		// Set message
		if ($success)
		{
			$msg = $value
				? 'global.messages.items published'
				: 'global.messages.items unpublished';

			$request->session()->flash('success', trans($msg, ['count' => $success]));
		}

		// Redirect back to the listing
		return $this->cancel($request);
	}

	/**
	 * Changes the order of one or more records.
	 *
	 * @param  Request $request
	 * @return  boolean  True on success
	 */
	/*public function reorder(Request $request)
	{
		// Initialise variables.
		$ids = $request->input('cid');
		$inc = ($this->getTask() == 'orderup') ? -1 : +1;

		$success = 0;

		foreach ($ids as $id)
		{
			// Load the record and reorder it
			$model = Widget::findOrFail(intval($id));

			if (!$model->move($inc))
			{
				$request->session()->flash('error', trans('global.messages.items reordering failed'));
				continue;
			}

			$success++;
		}

		if ($success)
		{
			// Set the success message
			$request->session()->flash('success', trans('global.messages.items reordered'));
		}

		// Redirect back to the listing
		return $this->cancel($request);
	}*/

	/**
	 * Reorder entries
	 * 
	 * @param   integer  $id
	 * @param   Request $request
	 * @return  Response
	 */
	public function reorder($id, Request $request)
	{
		// Incoming
		//$id = $request->input('id');

		// Get the element being moved
		$row = Widget::findOrFail($id);
		$move = ($request->segment(4) == 'orderup') ? -1 : +1;

		if (!$row->move($move))
		{
			$request->session()->flash('error', $row->getError());
		}

		// Redirect
		return $this->cancel($request);
	}

	/**
	 * Method to save the submitted ordering values for records.
	 *
	 * @param  Request $request
	 * @return  boolean  True on success
	 */
	public function saveorder(Request $request)
	{
		// Get the input
		$pks   = $request->input('cid', []);
		$order = $request->input('order', []);

		// Sanitize the input
		\App\Halcyon\Utility\Arr::toInteger($pks);
		\App\Halcyon\Utility\Arr::toInteger($order);

		// Save the ordering
		$return = Widget::saveOrder($pks, $order);

		if ($return === false)
		{
			// Reorder failed
			$request->session()->flash('success', trans('global.messages.items reordering failed'));
		}
		else
		{
			// Reorder succeeded.
			$request->session()->flash('success', trans('global.messages.items reordered'));
		}

		// Redirect back to the listing
		return $this->cancel($request);
	}

	/**
	 * Check in one or more records.
	 *
	 * @param   Request $request
	 * @return  Response
	 */
	public function checkin(Request $request)
	{
		// Incoming
		$ids = (array)$request->input('id', array());

		foreach ($ids as $id)
		{
			$model = Widget::findOrFail(intval($id));

			if (!$model->checkIn())
			{
				$request->session()->flash('error', trans('global.messages.checkin failed'));
				continue;
			}
		}

		return redirect(route('admin.widgets.index'));
	}

	/**
	 * Return to the main view
	 *
	 * @param  Request $request
	 * @return  Response
	 */
	public function cancel(Request $request)
	{
		if ($ids = $request->input('id'))
		{
			$ids = is_array($ids) ?: array($ids);

			foreach ((array)$ids as $id)
			{
				$model = Widget::find((int)$id);

				if ($model && $model->isCheckedOut())
				{
					// Check-in failed, go back to the record and display a notice.
					if (!$model->checkIn())
					{
						app('request')->session()->flash('error', trans('global.messages.checkin failed'));
					}
				}
			}
		}

		return redirect(route('admin.widgets.index'));
	}
}
