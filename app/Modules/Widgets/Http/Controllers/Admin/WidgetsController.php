<?php

namespace App\Modules\Widgets\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\View\View;
use App\Modules\Widgets\Models\Extension;
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
	 * @return View
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

		$reset = false;
		$request = $request->mergeWithBase();
		foreach ($filters as $key => $default)
		{
			if ($key != 'page'
			 && $request->has($key) //&& session()->has('widgets.filter_' . $key)
			 && $request->input($key) != session()->get('widgets.filter_' . $key))
			{
				$reset = true;
			}
			$filters[$key] = $request->state('widgets.filter_' . $key, $key, $default);
		}
		$filters['page'] = $reset ? 1 : $filters['page'];

		$filters['order'] = Widget::getSortField($filters['order']);
		$filters['order_dir'] = Widget::getSortDirection($filters['order_dir']);

		$rows = Widget::query()
			->withFilters($filters)
			->with('viewlevel')
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		// Get a list of all available widgets
		$widgets = Extension::query()
			->select(['id', 'name', 'element', 'type'])
			->where('type', '=', 'widget')
			->where('client_id', '=', (int) $filters['client_id'])
			->where('enabled', '=', 1)
			->get()
			->each(function($item, $key)
			{
				$name = strtolower($item->element);

				$item->registerLanguage();
				$item->name = trans('widget.' . $name . '::' . $name . '.widget name');
				$item->desc = trans('widget.' . $name . '::' . $name . '.widget desc');
			})
			->sortBy('name')
			->all();

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
	 * @return View
	 */
	public function create(Request $request)
	{
		$row = new Widget;
		$row->published = 1;
		$row->access = 1;

		if ($eid = $request->input('eid', 0))
		{
			$ext = Extension::query()
				->select('element', 'client_id')
				->where('id', '=', $eid)
				->where('type', '=', 'widget')
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

		if ($fields = $request->old('fields'))
		{
			$row->fill($fields);
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
	 * @param  int  $id
	 * @param  Request $request
	 * @return RedirectResponse|View
	 */
	public function edit(int $id, Request $request)
	{
		$row = Widget::findOrFail($id);

		if ($fields = $request->old('fields'))
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
			$ext = Extension::query()
				->select('element', 'client_id')
				->where('id', '=', $eid)
				->where('type', '=', 'widget')
				->first();

			if ($ext)
			{
				$row->widget = $ext->element;
				$row->client_id = $ext->client_id;
			}
		}

		/*if ($row->id)
		{
			// Checkout the record
			if (!$row->checkOut())
			{
				// Check-out failed, display a notice but allow the user to see the record.
				return $this->cancel($request)->with('warning', trans('global.messages.check out failed'));
			}
		}*/

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
	 * @return RedirectResponse
	 */
	public function store(Request $request)
	{
		$rules = [
			'fields.title'    => 'required|string|max:100',
			'fields.position' => 'required|string|max:50',
			'fields.widget'   => 'required|string|max:50',
			'fields.params'   => 'nullable|array',
			'fields.menu'     => 'nullable|array',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$id = $request->input('id');

		$row = Widget::findOrNew($id);

		$row->fill($request->input('fields'));
		$row->note = '';
		$row->language = '*';

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
			return redirect()->back()->withError(trans('global.messages.save failed'));
		}

		// Update menu assignments
		$menu = $request->input('menu', array());
		$assignment = (isset($menu['assignment']) ? $menu['assignment'] : 0);
		$assigned   = (isset($menu['assigned']) ? $menu['assigned'] : array());

		if (!$row->saveAssignment($assignment, $assigned))
		{
			return redirect()->back()->withError(trans('global.messages.save failed'));
		}

		$row->checkin();

		return $this->cancel($request)->withSuccess(trans('global.messages.item ' . ($id ? 'updated' : 'created')));
	}

	/**
	 * Method to edit an existing record.
	 *
	 * @param  Request $request
	 * @return View
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
		$items = Extension::query()
			->select('id', 'name', 'element')
			->where('type', '=', 'widget')
			->where('client_id', '=', (int) $filters['client_id'])
			->where('enabled', '=', 1)
			->orderBy($filters['order'], $filters['order_dir'])
			->get();

		foreach ($items as $item)
		{
			$item->registerLanguage();

			/*$widget = ucfirst($item->element);
			$path = app_path() . '/Widgets/' . $widget;
			$name = strtolower($item->element);
			app('translator')->addNamespace('widget.' . $name, $path . '/lang');*/

			$item->name = trans('widget.' . $name . '::' . $name . '.widget name');
			$item->desc = trans('widget.' . $name . '::' . $name . '.widget desc');
		}

		$items = collect($items)
			->sortBy('name')
			->all();

		return view('widgets::admin.select', [
			'items'   => $items,
			'filters' => $filters
		]);
	}

	/**
	 * Remove the specified item
	 * 
	 * @param  Request $request
	 * @return RedirectResponse
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
			$row = Widget::find($id);

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

		return $this->cancel($request);
	}

	/**
	 * Sets the state of one or more entries
	 * 
	 * @param  Request $request
	 * @param  int $id
	 * @return RedirectResponse
	 */
	public function state(Request $request, int $id)
	{
		$action = $request->segment(count($request->segments()) - 1);
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
				$request->session()->flash('error', trans('global.messages.save failed'));
				continue;
			}

			$success++;
		}

		// Set message
		if ($success)
		{
			$msg = $value
				? 'global.messages.item published'
				: 'global.messages.item unpublished';

			$request->session()->flash('success', trans($msg, ['count' => $success]));
		}

		// Redirect back to the listing
		return $this->cancel($request);
	}

	/**
	 * Reorder entries
	 * 
	 * @param   int  $id
	 * @param   Request $request
	 * @return  RedirectResponse
	 */
	public function reorder(int $id, Request $request)
	{
		// Incoming
		//$id = $request->input('id');

		// Get the element being moved
		$row = Widget::findOrFail($id);
		$move = ($request->segment(3) == 'orderup') ? -1 : +1;

		if (!$row->move($move))
		{
			$request->session()->flash('error', trans('global.messages.move failed'));
		}

		// Redirect
		return $this->cancel($request);
	}

	/**
	 * Method to save the submitted ordering values for records.
	 *
	 * @param  Request $request
	 * @return RedirectResponse
	 */
	public function saveorder(Request $request)
	{
		// Get the input
		$pks   = $request->input('cid', []);
		$order = $request->input('order', []);

		// Sanitize the input
		$pks = array_map('intval', $pks);
		$order = array_map('intval', $order);

		// Save the ordering
		$return = Widget::saveOrder($pks, $order);

		if ($return === false)
		{
			// Reorder failed
			$request->session()->flash('error', trans('global.messages.items reordering failed'));
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
	 * @return  RedirectResponse
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
	 * @return RedirectResponse
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
						$request->session()->flash('error', trans('global.messages.checkin failed'));
					}
				}
			}
		}

		return redirect(route('admin.widgets.index'));
	}
}
