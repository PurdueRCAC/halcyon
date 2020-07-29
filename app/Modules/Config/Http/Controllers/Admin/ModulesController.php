<?php

namespace App\Modules\Config\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Config\Models\Extension;
use App\Halcyon\Access\Asset;
use App\Halcyon\Access\Rules;

class ModulesController extends Controller
{
	/**
	 * Display config options for a module
	 *
	 * @param   string   $module
	 * @return  Response
	 */
	public function index($module)
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
				return redirect()->back()->withInput()->withError($asset->getError());
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
			return redirect()->back()->withInput()->withError($module->getError());
		}

		return redirect(route('admin.' . $module->element . '.index'))->with('success', trans('config::config.configuration saved'));
	}
}
