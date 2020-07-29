<?php

namespace App\Modules\Listeners\Http\Controllers\Admin;

use Illuminate\Http\Request;
//use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
//use Illuminate\Support\Facades\DB;
use App\Halcyon\Http\StatefulRequest;
use App\Modules\Listeners\Models\Listener;
use App\Modules\Users\Models\User;
use App\Halcyon\Access\Viewlevel;

class ListenersController extends Controller
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
			'state'     => '',
			'access'    => 0,
			'folder'  => null,
			'enabled'    => null,
			// Pagination
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			'order'     => Listener::$orderBy,
			'order_dir' => Listener::$orderDir,
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->state('listeners.filter_' . $key, $key, $default);
		}

		if (!in_array($filters['order'], array_keys((new Listener)->getAttributes())))
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
	 * @return Response
	 */
	public function create()
	{
		app('request')->merge(['hidemainmenu' => 1]);

		$row = new Listener;
		$row->registerLanguage();

		return view('listeners::admin.edit', [
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
			if (!$row->checkout())
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
	 * @param  Request $request
	 * @return Response
	 */
	public function store(Request $request)
	{
		$request->validate([
			'fields.folder' => 'required',
			'fields.element' => 'required'
		]);
 
		$id = $request->input('id');

		$row = $id ? Listener::findOrFail($id) : new Listener();

		$row->fill($request->input('fields'));

		$row->params = json_encode($request->input('params', []));

		if (!$row->save())
		{
			$error = $row->getError() ? $row->getError() : trans('messages.save failed');

			return redirect()->back()->withError($error);
		}

		return $this->cancel()->withSuccess(trans('messages.update success'));
	}

	/**
	 * Remove the specified resource from storage.
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
			$request->session()->flash('success', trans('messages.item deleted', ['count' => $success]));
		}

		return $this->cancel();
	}

	/**
	 * Method to publish a list of items
	 *
	 * @param   object  $request
	 * @return  void
	 */
	public function publish(Request $request, $id = 0)
	{
		return $this->state($request, $id, 1);
	}

	/**
	 * Method to unpublish a list of items
	 *
	 * @param   object  $request
	 * @return  void
	 */
	public function unpublish(Request $request, $id = 0)
	{
		return $this->state($request, $id, 0);
	}

	/**
	 * Method to change the state of a list of items
	 *
	 * @param   object  $request
	 * @param   integer  $value
	 * @return  void
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
	 * @param   object  $request
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
				$request->session()->flash('error', trans('global.ERROR_REORDER_FAILED', ['error' => $model->getError()]));
				continue;
			}

			$success++;
		}

		if ($success)
		{
			// Set the success message
			$request->session()->flash('success', trans('global.SUCCESS_ITEM_REORDERED'));
		}

		// Redirect back to the listing
		return $this->cancel();
	}

	/**
	 * Method to save the submitted ordering values for records.
	 *
	 * @param   object  $request
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
			$request->session()->flash('success', trans('global.ERROR_REORDER_FAILED'));
		}
		else
		{
			// Reorder succeeded.
			$request->session()->flash('success', trans('global.SUCCESS_ORDERING_SAVED'));
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
				$request->session()->flash('error', trans('global.ERROR_CHECKIN_FAILED', ['error' => $model->getError()]));
				continue;
			}
		}

		// Redirect back to the listing
		return $this->cancel();
	}

	/**
	 * Return to the main view
	 *
	 * @param   Request $request
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
					$request->session()->flash('error', trans('global.ERROR_CHECKIN_FAILED', ['error' => $model->getError()]));
				}
			}
		}

		return redirect(route('admin.listeners.index'));
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
			if (auth()->user()->can('edit listeners'))
			{
				$model = Listener::find($pk);

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
				$this->setError(trans('global.ERROR_BATCH_CANNOT_EDIT'));
				return false;
			}
		}

		return true;
	}
}
