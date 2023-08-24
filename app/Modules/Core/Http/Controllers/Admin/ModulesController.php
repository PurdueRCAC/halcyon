<?php

namespace App\Modules\Core\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Artisan;
use App\Modules\Core\Models\Extension;
use App\Halcyon\Http\StatefulRequest;
use App\Halcyon\Access\Rules;
use App\Halcyon\Access\Asset;

class ModulesController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * 
	 * @param  StatefulRequest $request
	 * @return View
	 */
	public function index(StatefulRequest $request)
	{
		$filters = array(
			'search'   => null,
			'state'    => 'enabled',
			// Paging
			'limit'    => config('list_limit', 20),
			'page'     => 1,
			// Sorting
			'order'     => 'name',
			'order_dir' => 'asc'
		);

		$reset = false;
		$request = $request->mergeWithBase();
		foreach ($filters as $key => $default)
		{
			if ($key != 'page'
			 && $request->has($key) //&& session()->has('core.modules.filter_' . $key)
			 && $request->input($key) != session()->get('core.modules.filter_' . $key))
			{
				$reset = true;
			}
			$filters[$key] = $request->state('core.modules.filter_' . $key, $key, $default);
		}
		$filters['page'] = $reset ? 1 : $filters['page'];

		if (!in_array($filters['order'], ['id', 'name', 'element', 'folder', 'ordering', 'enabled']))
		{
			$filters['order'] = 'name';
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = 'asc';
		}

		// Get records
		$query = Extension::query()
			->where('type', '=', 'module');

		if ($filters['state'] != '*')
		{
			if ($filters['state'] == 'enabled')
			{
				$query->where('enabled', '=', 1);
			}
			elseif ($filters['state'] == 'disabled')
			{
				$query->where('enabled', '=', 0);
			}
		}

		if ($filters['search'])
		{
			if (is_numeric($filters['search']))
			{
				$query->where('id', '=', $filters['search']);
			}
			else
			{
				$query->where(function($where) use ($filters)
				{
					$where->where('name', 'like', '%' . $filters['search'] . '%')
						->orWhere('name', 'like', $filters['search'] . '%')
						->orWhere('name', 'like', '%' . $filters['search']);
				});
			}
		}

		$query->orderBy($filters['order'], $filters['order_dir']);
		
		if ($filters['order'] != 'ordering')
		{
			$query->orderBy('ordering', $filters['order_dir']);
		}

		$rows = $query->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		return view('core::admin.modules.index', [
			'rows'    => $rows,
			'filters' => $filters,
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 * 
	 * @param Request $request
	 * @param int $id
	 * @return View
	 */
	public function edit(Request $request, $id)
	{
		$row = Extension::findOrFail($id);
		$row->folder = $row->folder ?: 'extensions';

		return view('core::admin.modules.edit', [
			'row'  => $row,
		]);
	}

	/**
	 * Update the specified extension
	 *
	 * @param   Request $request
	 * @return  RedirectResponse
	 */
	public function store(Request $request)
	{
		$rules = [
			'name'    => 'required|string|max:255',
			'folder'  => 'nullable|string|max:255',
			'enabled' => 'nullable|integer',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$id = $request->input('id');

		$row = $id ? Extension::findOrFail($id) : new Extension;
		if ($request->has('name'))
		{
			$row->name = $request->input('name');
		}
		if ($request->has('folder'))
		{
			$row->folder = $request->input('folder');
		}
		if ($request->has('enabled'))
		{
			$row->enabled = $request->input('enabled');
		}

		if (!$row->save())
		{
			return redirect()->back()->withError(trans('global.messages.save failed'));
		}

		return redirect(route('admin.modules.index'))->withSuccess(trans('global.messages.item updated'));
	}

	/**
	 * Sets the state of one or more entries
	 * 
	 * @param   Request $request
	 * @param   int     $id
	 * @return  RedirectResponse
	 */
	public function state(Request $request, $id = null)
	{
		$action = app('request')->segment(count($request->segments()) - 1);
		$state  = $action == 'enable' ? 1 : 0;

		// Incoming
		$ids = $request->input('id', array($id));
		$ids = (!is_array($ids) ? array($ids) : $ids);

		// Check for an ID
		if (count($ids) < 1)
		{
			$request->session()->flash('warning', trans($state ? 'global.select to publish' : 'global.select to unpublish'));
			return redirect(route('admin.modules.index'));
		}

		$success = 0;

		// Update record(s)
		foreach ($ids as $id)
		{
			$row = Extension::findOrFail(intval($id));

			if ($row->enabled == $state || $row->protected)
			{
				continue;
			}

			$row->enabled = $state;

			if (!$row->save())
			{
				$request->session()->flash('error', trans('global.messages.save failed'));
				continue;
			}

			$success++;
		}

		// Set message
		if ($success)
		{
			$msg = $state
				? 'global.items published'
				: 'global.items unpublished';

			$request->session()->flash('success', trans($msg, ['count' => $success]));
		}

		return redirect(route('admin.modules.index'));
	}

	/**
	 * Store config changes
	 *
	 * @param  string  $module
	 * @param  Request $request
	 * @return RedirectResponse
	 */
	public function update($module, Request $request)
	{
		/*$request->validate([
			'name' => 'required'
		]);

		$order = new Extension([
			'name'         => $request->get('name'),
			'parentid'     => $request->get('parentid'),
			'rolename'     => $request->get('rolename'),
			'listname'     => $request->get('listname'),
			'resourcetype' => $request->get('resourcetype'),
			'producttype'  => $request->get('producttype')
		]);

		$order->save();

		event('onAfterSaveOrder', $order);

		return redirect(route('admin.resources.index'))->with('success', 'Resource saved!');*/

		//$module  = new Extension();
		$id     = $request->input('id');
		//$option = $request->input('module');

		$module = Extension::findOrFail($id);

		if (!$module || !$module->id)
		{
			abort(404);
		}

		if (!auth()->user()
		 || !auth()->user()->can('admin ' . $module->element))
		{
			abort(403);
		}

		$data   = $request->input('params', array());

		// Validate the posted data.
		//$return = $module->validate($module->getForm(), $data);
		// Filter and validate the form data.
		$form   = $module->getForm();
		$data   = $form->filter($data);
		$return = $form->validate($data);

		if ($return instanceof \Exception)
		{
			return redirect()->back()->withInput()->withError($return->getMessage());
		}

		// Check the validation results.
		if ($return === false)
		{
			$errors = array();
			foreach ($form->getErrors() as $err)
			{
				if ($err instanceof \Exception)
				{
					$errors[] = $err->getMessage();
				}
				else
				{
					$errors[] = $err;
				}
			}

			return redirect()->back()->withInput()->withErrors($errors);
		}

		// Save the rules.
		if (!empty($data)
		 && isset($data['rules']))
		{
			foreach ($data['rules'] as $k => $v)
			{
				$data['rules'][$k] = array_filter($v);
			}

			$rules = new Rules($data['rules']);
			$asset = Asset::findByName($module->element);

			if (!$asset->id)
			{
				$root = Asset::getRoot();

				$asset->name  = $module->element;
				$asset->title = $module->element;
				$asset->parent_id = $root->id;
				$asset->saveAsLastChildOf($root);
			}
			$asset->rules = (string) $rules;

			if (!$asset->save())
			{
				return redirect()->back()->withInput()->withError(trans('global.messages.save failed'));
			}

			// We don't need this anymore
			unset($data['rules']);
		}

		$module->params = json_encode($data);
		/*foreach ($data as $k => $v)
		{
			$module->params()->set($k, $v);
		}*/

		// Attempt to save the configuration.
		if (!$module->save())
		{
			return redirect()->back()->withInput()->withError(trans('global.messages.save failed'));
		}

		return redirect(route('admin.' . strtolower($module->element) . '.index'))->with('success', trans('config::config.configuration saved'));
	}

	/**
	 * Scan for new modules
	 *
	 * @param  Request $request
	 * @return View
	 */
	public function scan(Request $request)
	{
		$modules = Extension::query()
			->select('element')
			->where('type', '=', 'module')
			->get()
			->pluck('element')
			->toArray();

		$rows = array();
		foreach (app('files')->directories(app_path('Modules')) as $dir)
		{
			$name = basename($dir);
			$element = strtolower($name);

			if (in_array($element, $modules))
			{
				continue;
			}

			$row = new Extension;
			$row->type = 'module';
			$row->name = $element;
			$row->element = $element;

			$rows[] = $row;
		}

		return view('core::admin.modules.scan', [
			'rows' => $rows,
		]);
	}

	/**
	 * Scan for new modules
	 *
	 * @param  Request $request
	 * @param  string $element
	 * @return RedirectResponse
	 */
	public function install(Request $request, $element)
	{
		//$element = $request->input('element');

		$module = Extension::query()
			->select('*')
			->where('type', '=', 'module')
			->where('element', '=', $element)
			->first();

		if (!$module)
		{
			$row = new Extension;
			$row->type = 'module';
			$row->name = $element;
			$row->element = $element;
			$row->access = 1;
			$row->enabled = 0;
			$row->client_id = 1;
			$row->save();

			Artisan::call('module:migrate', [
				'element' => 1
			]);

			Artisan::call('module:publish', [
				'element' => 1
			]);
		}

		return redirect(route('admin.modules.index'))->with('success', trans('core::modules.module installed'));
	}
}
