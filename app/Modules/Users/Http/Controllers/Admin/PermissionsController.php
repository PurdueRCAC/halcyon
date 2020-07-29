<?php

namespace App\Modules\Users\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Users\Models\Permissions;
use App\Halcyon\Access\Role;
use App\Halcyon\Access\Gate;

class PermissionsController extends Controller
{
	/**
	 * Display config options for a module
	 *
	 * @return  Response
	 */
	public function index()
	{
		if (!auth()->user()
		 || !auth()->user()->can('admin'))
		{
			abort(403);
		}

		$model = new Permissions;
		$form = $model->getForm();

		// Initialise some field attributes.
		$section = 'core';
		$actions = null;

		// Get the actions for the asset.
		//$comfile = module_path('core') . '/Config/permissions.php';
		$comfile = module_path('users') . '/Config/defaultpermissions.php';
		if (is_file($comfile))
		{
			$actions = include $comfile;
		}

		if (!$actions)
		{
			$actions = array($section => array());
		}

		$assetId = 1;

		// Get the rules for just this asset (non-recursive).
		$assetRules = Gate::getAssetRules($assetId);

		// Get the available user roles.
		$roles = Role::tree();

		return view('users::admin.permissions.index', [
			'form' => $form,
			'section' => $section,
			'module' => '',
			'roles' => $roles,
			'assetId' => $assetId,
			'assetRules' => $assetRules,
			'actions' => $actions,
		]);
	}

	/**
	 * Display config options for a module
	 *
	 * @param   string   $module
	 * @return  Response
	 */
	public function module($module)
	{
		//$module = new Models\Component();

		//$form = $module->getForm();
		$module = Extension::findModuleByName($module);

		if (!$module || !$module->extension_id)
		{
			abort(404);
		}

		if (!auth()->user()
		 || !auth()->user()->can('admin ' . $module->element))
		{
			abort(403);
		}

		$form = $module->getForm();

		//$module->registerLanguage();

		return view('config::admin.module', [
			'module' => $module,
			'form' => $form
		]);
	}

	/**
	 * Store config changes
	 *
	 * @param  Request $request
	 * @return Response
	 */
	public function update($module, Request $request)
	{
		$id     = $request->input('id');
		//$option = $request->input('module');

		$module = Extension::findOrFail($id);

		if (!$module || !$module->extension_id)
		{
			abort(404);
		}

		if (!auth()->user()
		 || !auth()->user()->can('admin ' . $module->element))
		{
			abort(403);
		}

		$data   = $request->input('params', array());

		// Save the rules.
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
				return redirect()->back()->withInput()->withError($asset->getError());
			}

		return redirect(route('admin.' . $module->element . '.index'))->with('success', trans('config::config.configuration saved'));
	}

	/**
	 * Return to the main view
	 *
	 * @return  Response
	 */
	public function cancel()
	{
		return redirect(route('admin.users.permissions'));
	}
}
