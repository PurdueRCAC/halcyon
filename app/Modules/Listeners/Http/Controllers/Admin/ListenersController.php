<?php

namespace App\Modules\Listeners\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Halcyon\Http\StatefulRequest;
use App\Modules\Listeners\Models\Listener;
use App\Modules\Users\Models\User;
use App\Halcyon\Access\Viewlevel;

class ListenersController extends Controller
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
			'state'     => 1,
			'access'    => 0,
			'folder'  => null,
			'enabled'    => null,
			// Pagination
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			'order'     => Listener::$orderBy,
			'order_dir' => Listener::$orderDir,
		);

		$reset = false;
		$request = $request->mergeWithBase();
		foreach ($filters as $key => $default)
		{
			if ($key != 'page' && $request->has($key) && session()->get('listeners.filter_' . $key) != $request->input($key))
			{
				$reset = true;
			}
			$filters[$key] = $request->state('listeners.filter_' . $key, $key, $default);
		}
		$filters['page'] = $reset ? 1 : $filters['page'];

		if (!in_array($filters['order'], ['id', 'name', 'element', 'folder', 'state', 'access', 'ordering']))
		{
			$filters['order'] = Listener::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Listener::$orderDir;
		}

		$query = Listener::query()
			->where('type', '=', 'listener');
			//->where('state', '>=', 0);

		$p = (new Listener)->getTable();
		$u = (new User)->getTable(); //'users';
		$a = (new Viewlevel)->getTable();'viewlevels';

		$query->select([$p . '.*', $u . '.name AS editor', $a . '.title AS access_level']);

		// Join over the users for the checked out user.
		$query
			//->select([$u . '.name AS editor'])
			->leftJoin($u, $u . '.id', $p . '.checked_out');

		// Join over the access groups.
		$query
			//->select([$a . '.title AS access_level'])
			->leftJoin($a, $a . '.id', $p . '.access');

		// Filter by access level.
		if ($filters['access'])
		{
			$query->where($p . '.access', '=', (int) $filters['access']);
		}

		// Filter by published state
		if (is_numeric($filters['state']))
		{
			$query->where($p . '.enabled', '=', (int) $filters['state']);
		}
		elseif ($filters['state'] === '')
		{
			$query->whereIn($p . '.enabled', array(0, 1));
		}

		// Filter by folder.
		if ($filters['folder'])
		{
			$query->where($p . '.folder', '=', $filters['folder']);
		}

		// Filter by search in id
		if (!empty($filters['search']))
		{
			if (stripos($filters['search'], 'id:') === 0)
			{
				$query->where($p . '.extension_id', '=', (int) substr($filters['search'], 3));
			}
			else
			{
				$query->where(function($where) use ($filters)
				{
					$where->where($p . '.name', 'like', '%' . $filters['search'] . '%')
						->orWhere($p . '.element', 'like', '%' . $filters['search'] . '%');
				});
			}
		}

		if ($filters['order'] == 'name')
		{
			$query->orderBy('name', $filters['order_dir']);
			$query->orderBy('ordering', 'asc');
		}
		else if ($filters['order'] == 'ordering')
		{
			$query->orderBy('folder', 'asc');
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
			->paginate($filters['limit'], ['*'], 'page', $filters['page'])
			->appends($filters);

		return view('listeners::admin.index', [
			'rows' => $rows,
			'filters' => $filters
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 * 
	 * @return Response
	 */
	public function create()
	{
		$row = new Listener;
		$row->registerLanguage();

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		return view('listeners::admin.edit', [
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
		$row = Listener::findOrFail($id);

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

		if ($row->id)
		{
			// Checkout the record
			if (!$row->checkOut())
			{
				// Check-out failed, display a notice but allow the user to see the record.
				return $this->cancel()->with('warning', trans('global.check out failed'));
			}
		}

		$row->registerLanguage();

		return view('listeners::admin.edit', [
			'row'  => $row,
			'form' => $row->getForm()
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
		$rules = [
			'fields.access' => 'nullable|integer',
			'fields.state'  => 'nullable|integer',
			'fields.params' => 'nullable|array',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}
 
		$id = $request->input('id');

		$row = $id ? Listener::findOrFail($id) : new Listener();

		$row->fill($request->input('fields'));

		if (!$row->save())
		{
			$error = $row->getError() ? $row->getError() : trans('global.messages.save failed');

			return redirect()->back()->withError($error);
		}

		$row->checkIn();

		return $this->cancel()->withSuccess(trans('global.messages.update success'));
	}

	/**
	 * Remove the specified resource from storage.
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
			$row = Listener::findOrFail($id);

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
	 * Method to publish a list of items
	 *
	 * @param   Request  $request
	 * @param   integer  $id
	 * @return  Response
	 */
	public function publish(Request $request, $id = 0)
	{
		return $this->state($request, $id, 1);
	}

	/**
	 * Method to unpublish a list of items
	 *
	 * @param   Request  $request
	 * @param   integer  $id
	 * @return  Response
	 */
	public function unpublish(Request $request, $id = 0)
	{
		return $this->state($request, $id, 0);
	}

	/**
	 * Method to change the state of a list of items
	 *
	 * @param   Request  $request
	 * @param   integer  $id
	 * @param   integer  $value
	 * @return  Response
	 */
	public function state(Request $request, $id = 0, $value = 1)
	{
		// Get items to publish from the request.
		$ids = $request->input('id', [$id]);

		$success = 0;

		foreach ($ids as $id)
		{
			// Load the record
			$model = Listener::findOrFail(intval($id));

			// Set state
			$model->enabled = $value;

			if (!$model->save())
			{
				$request->session()->flash('error', $model->getError());
				continue;
			}

			$success++;
		}

		if ($success)
		{
			// Set the success message
			if ($value == 1)
			{
				$ntext = 'published';
			}
			elseif ($value == 0)
			{
				$ntext = 'unpublished';
			}

			$request->session()->flash('success', trans('global.messages.item ' . $ntext, ['count' => $success]));
		}

		// Redirect back to the listing
		return $this->cancel();
	}

	/**
	 * Changes the order of one or more records.
	 *
	 * @param   Request  $request
	 * @return  Response
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
			$model = Listener::findOrFail(intval($id));

			if (!$model->move($inc))
			{
				$request->session()->flash('error', trans('global.error.reorder failed', ['error' => $model->getError()]));
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
		return $this->cancel();
	}

	/**
	 * Method to save the submitted ordering values for records.
	 *
	 * @param   Request  $request
	 * @return  Response
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
		$return = Listener::saveOrder($pks, $order);

		if ($return === false)
		{
			// Reorder failed
			$request->session()->flash('success', trans('global.error.reorder failed'));
		}
		else
		{
			// Reorder succeeded.
			$request->session()->flash('success', trans('global.messages.ordering saved'));
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
		$ids = $request->input('id', array());

		foreach ($ids as $id)
		{
			$model = Listener::findOrFail(intval($id));

			if (!$model->checkin())
			{
				$request->session()->flash('error', trans('global.error.checkin failed', ['error' => $model->getError()]));
				continue;
			}
		}

		// Redirect back to the listing
		return $this->cancel();
	}

	/**
	 * Return to the main view
	 *
	 * @return  Response
	 */
	public function cancel()
	{
		$request = request();
		$id = $request->input('id');

		if ($id && is_integer($id))
		{
			$model = Listener::find($id);

			if ($model && $model->checked_out && $model->checked_out == auth()->user()->id)
			{
				// Check-in failed, go back to the record and display a notice.
				if (!$model->checkin())
				{
					$request->session()->flash('error', trans('global.error.checkin failed', ['error' => $model->getError()]));
				}
			}
		}

		return redirect(route('admin.listeners.index'));
	}
}
