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

		if (!in_array($filters['order'], ['id', 'title', 'position', 'state', 'access']))
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
			->leftJoin($m, $m . '.moduleid', $p . '.id');

		// Join over the extensions
		$query
			//->select($e . '.name AS name')
			->join($e, $e . '.element', $p . '.module', 'left')
			->groupBy(
				$p . '.id',
				$p . '.title',
				$p . '.note',
				$p . '.position',
				$p . '.module',
				$p . '.language',
				$p . '.checked_out',
				$p . '.checked_out_time',
				$p . '.published',
				$p . '.access',
				$p . '.ordering',
				//$l . '.title',
				$u . '.name',
				$a . '.title',
				$e . '.name',
				//$l . '.lang_code',
				$u . '.id',
				$a . '.id',
				$m . '.moduleid',
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
			$query->where($p . '.module', '=', $filters['widget']);
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
			->select(['extension_id', 'name', 'element'])
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
	 * @return Response
	 */
	public function create(Request $request)
	{
		app('request')->merge(['hidemainmenu' => 1]);

		$row = new Widget;
		$row->published = 1;
		$row->access = 1;

		if ($eid = $request->input('eid', 0))
		{
			$db = app('db');

			$ext = $db->table('extensions')
				->select(['element', 'client_id'])
				->where('extension_id', '=', $eid)
				//->where('element', '=', $eid)
				->where('type', '=', 'widget')
				->get()
				->first();

			if ($ext)
			{
				$row->module = $ext->element;
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
	 * @return Response
	 */
	public function edit($id, Request $request)
	{
		app('request')->merge(['hidemainmenu' => 1]);

		$row = Widget::findOrFail($id);

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		// Fail if checked out not by 'me'
		if ($row->checked_out
		 && $row->checked_out <> auth()->user()->id)
		{
			return $this->cancel()->with('warning', trans('global.checked out'));
		}

		if ($eid = $request->input('eid', 0))
		{
			$db = app('db');

			$ext = $db->table('extensions')
				->select('element, client_id')
				->where('extension_id', '=', $eid)
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
			if (!$row->checkout())
			{
				// Check-out failed, display a notice but allow the user to see the record.
				return $this->cancel()->with('warning', trans('global.check out failed'));
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
	 * @param  Request $request
	 * @return Response
	 */
	public function store(Request $request)
	{
		$request->validate([
			'fields.title' => 'required',
			'fields.position' => 'required'
		]);
 
		$id = $request->input('id');

		$row = $id ? Widget::findOrFail($id) : new Widget();

		$row->fill($request->input('fields'));
		$row->note = '';

		if ($params = $request->input('fields.params'))
		{
			foreach ($params as $key => $val)
			{
				$row->params->set($key, $val);
			}
		}

		if (!$row->save())
		{
			$error = $row->getError() ? $row->getError() : trans('messages.save failed');

			return redirect()->back()->withError($error);
		}

		// Update menu assignments
		$menu = $request->input('menu', array());
		$assignment = (isset($menu['assignment']) ? $menu['assignment'] : 0);
		$assigned   = (isset($menu['assigned']) ? $menu['assigned'] : array());

		if (!$row->saveAssignment($assignment, $assigned))
		{
			$error = $row->getError() ? $row->getError() : trans('messages.save failed');

			return redirect()->back()->withError($error);
		}

		$row->checkin();

		return $this->cancel()->withSuccess(trans('messages.update success'));
	}

	/**
	 * Method to edit an existing record.
	 *
	 * @return  void
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
			->select(['extension_id', 'name', 'element'])
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
			$request->session()->flash('success', trans('messages.item deleted', ['count' => $success]));
		}

		return $this->cancel();
	}

	/**
	 * Method to publish a list of items
	 *
	 * @return  void
	 */
	/*public function publish(Request $request)
	{
		return $this->state($request, 'publish');
	}

	public function unpublish(Request $request)
	{
		return $this->state($request, 'unpublish');
	}

	public function trash(Request $request)
	{
		return $this->state($request, 'trash');
	}

	public function state(Request $request, $task)
	{*/
	/**
	 * Sets the state of one or more entries
	 * 
	 * @return  void
	 */
	public function state(Request $request, $id)
	{
		$action = app('request')->segment(count($request->segments()) - 1);
		$value  = $action == 'publish' ? 1 : 0;

		// Incoming
		$ids = $request->input('id', array($id));
		$ids = (!is_array($ids) ? array($ids) : $ids);
		// Get items to publish from the request.
		/*$cid   = $request->input('cid');
		$data  = array(
			'publish'   => 1,
			'unpublish' => 0,
			'archive'   => 2,
			'trash'     => -2,
			'report'    => -3
		);
		$value = \App\Halcyon\Utility\Arr::getValue($data, $task, 0, 'int');*/

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

		/*if ($success)
		{
			// Clean the cache.
			//$this->cleanCache();

			// Set the success message
			if ($value == 1)
			{
				$ntext = 'widgets::widgets.N_ITEMS_PUBLISHED';
			}
			elseif ($value == 0)
			{
				$ntext = 'widgets::widgets.N_ITEMS_UNPUBLISHED';
			}
			elseif ($value == 2)
			{
				$ntext = 'widgets::widgets.N_ITEMS_ARCHIVED';
			}
			else
			{
				$ntext = 'widgets::widgets.N_ITEMS_TRASHED';
			}

			$request->session()->flash('success', trans($ntext, ['count' => $success]));
		}*/
		// Set message
		if ($success)
		{
			$msg = $value
				? 'widgets::widgets.items published'
				: 'widgets::widgets.items unpublished';

			$request->session()->flash('success', trans($msg, ['count' => $success]));
		}

		// Redirect back to the listing
		return $this->cancel();
	}

	/**
	 * Changes the order of one or more records.
	 *
	 * @return  boolean  True on success
	 */
	public function reorder(Request $request)
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
				$request->session()->flash('error', trans('JLIB_APPLICATION_ERROR_REORDER_FAILED', ['error' => $model->getError()]));
				continue;
			}

			$success++;
		}

		if ($success)
		{
			// Set the success message
			$request->session()->flash('success', trans('JLIB_APPLICATION_SUCCESS_ITEM_REORDERED'));
		}

		// Redirect back to the listing
		return $this->cancel();
	}

	/**
	 * Method to save the submitted ordering values for records.
	 *
	 * @return  boolean  True on success
	 */
	public function saveorder(Request $request)
	{
		// Get the input
		$pks   = $request->input('cid');
		$order = $request->input('order');

		// Sanitize the input
		\App\Halcyon\Utility\Arr::toInteger($pks);
		\App\Halcyon\Utility\Arr::toInteger($order);

		// Save the ordering
		$return = Widget::saveOrder($pks, $order);

		if ($return === false)
		{
			// Reorder failed
			$request->session()->flash('success', trans('JLIB_APPLICATION_ERROR_REORDER_FAILED'));
		}
		else
		{
			// Reorder succeeded.
			$request->session()->flash('success', trans('JLIB_APPLICATION_SUCCESS_ORDERING_SAVED'));
		}

		// Redirect back to the listing
		return $this->cancel();
	}

	/**
	 * Check in of one or more records.
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

			if (!$model->checkin())
			{
				$request->session()->flash('error', trans('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', ['error' => $model->getError()]));
				continue;
			}
		}

		// Redirect back to the listing
		return redirect(route('admin.widgets.index'));
	}

	/**
	 * Return to the main view
	 *
	 * @param   Request $request
	 * @return  Response
	 */
	public function cancel()
	{
		/*if ($id = app('request')->input('id'))
		{
			$model = Widget::find($id);

			if ($model && $model->isCheckedOut())
			{
				// Check-in failed, go back to the record and display a notice.
				if (!$model->checkIn())
				{
					app('request')->session()->flash('error', trans('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', ['error' => $model->getError()]));
				}
			}
		}*/

		return redirect(route('admin.widgets.index'));
	}

	/**
	 * Batch process records
	 *
	 * @param   Request $request
	 * @return  Response
	 */
	public function batch(Request $request)
	{
		$commands = $request->post('batch');

		// Sanitize user ids.
		$pks = array_unique($pks);
		\App\Halcyon\Utility\Arr::toInteger($pks);

		// Remove any values of zero.
		if (array_search(0, $pks, true))
		{
			unset($pks[array_search(0, $pks, true)]);
		}

		if (empty($pks))
		{
			return $this->cancel()->with('error', trans('global.no item selected'));
		}

		$done = false;

		if (!empty($commands['position_id']))
		{
			$cmd = \App\Halcyon\Utility\Arr::getValue($commands, 'move_copy', 'c');

			if (!empty($commands['position_id']))
			{
				if ($cmd == 'c')
				{
					$result = $this->batchCopy($commands['position_id'], $pks, $contexts);

					if (is_array($result))
					{
						$pks = $result;
					}
					else
					{
						return $this->cancel();
					}
				}
				elseif ($cmd == 'm' && !$this->batchMove($commands['position_id'], $pks, $contexts))
				{
					return $this->cancel();
				}

				$done = true;
			}
		}

		if (!empty($commands['assetgroup_id']))
		{
			if (!$this->batchAccess($commands['assetgroup_id'], $pks, $contexts))
			{
				return $this->cancel();
			}

			$done = true;
		}

		if (!empty($commands['language_id']))
		{
			if (!$this->batchLanguage($commands['language_id'], $pks, $contexts))
			{
				return $this->cancel();
			}

			$done = true;
		}

		if (!$done)
		{
			return $this->cancel()->with('error', trans('global.insufficient batch information'));
		}

		return $this->cancel();
	}

	/**
	 * Batch copy modules to a new position or current.
	 *
	 * @param   integer  $value      The new value matching a module position.
	 * @param   array    $pks        An array of row IDs.
	 * @param   array    $contexts   An array of item contexts.
	 * @return  boolean  True if successful, false otherwise and internal error is set.
	 */
	protected function batchCopy($value, $pks, $contexts)
	{
		// Set the variables
		$table = $this->getTable();
		$i = 0;

		foreach ($pks as $pk)
		{
			if (auth()->user()->can('create widgets'))
			{
				$model = Widget::find($pk);

				// Set the new position
				if ($value == 'noposition')
				{
					$position = '';
				}
				elseif ($value == 'nochange')
				{
					$position = $model->position;
				}
				else
				{
					$position = $value;
				}
				$model->position = $position;

				// Alter the title if necessary
				$data = $model->generateNewTitle($model->title, $model->position);
				$model->title = $data[0];

				// Reset the ID because we are making a copy
				$model->id = 0;

				// Unpublish the new module
				$model->published = 0;

				if (!$model->save())
				{
					$this->setError($model->getError());
					return false;
				}

				// Get the new item ID
				$newId = $model->id;

				// Add the new ID to the array
				$newIds[$i]	= $newId;
				$i++;

				// Now we need to handle the module assignments
				$db = app('db');
				$menus = $db->table('widgets_menu')
					->select('menuid')
					->where('moduleid', '=', $pk)
					->get();

				// Insert the new records into the table
				foreach ($menus as $menu)
				{
					$db->table('widgets_menu')
						->insert(array(
							'moduleid' => $newId,
							'menuid'   => $menu
						));
				}
			}
			else
			{
				$this->setError(trans('JLIB_APPLICATION_ERROR_BATCH_CANNOT_CREATE'));
				return false;
			}
		}

		return $newIds;
	}

	/**
	 * Batch move modules to a new position or current.
	 *
	 * @param   integer  $value     The new value matching a module position.
	 * @param   array    $pks       An array of row IDs.
	 * @param   array    $contexts  An array of item contexts.
	 * @return  boolean  True if successful, false otherwise and internal error is set.
	 */
	protected function batchMove($value, $pks, $contexts)
	{
		// Set the variables
		$i = 0;

		foreach ($pks as $pk)
		{
			if (auth()->user()->can('edit widgets'))
			{
				$model = Widget::find($pk);

				// Set the new position
				if ($value == 'noposition')
				{
					$position = '';
				}
				elseif ($value == 'nochange')
				{
					$position = $model->position;
				}
				else
				{
					$position = $value;
				}
				$model->position = $position;

				// Alter the title if necessary
				$data = $model->generateNewTitle($model->title, $model->position);
				$model->title = $data[0];

				// Unpublish the moved module
				$model->published = 0;

				if (!$model->save())
				{
					$this->setError($model->getError());
					return false;
				}
			}
			else
			{
				$this->setError(trans('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));
				return false;
			}
		}

		return true;
	}
}
