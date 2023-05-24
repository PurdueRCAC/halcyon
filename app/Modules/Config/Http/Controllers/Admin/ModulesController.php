<?php

namespace App\Modules\Config\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
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
	 * @return  View
	 */
	public function index($module)
	{
		$mod = Extension::findModuleByName($module);

		if (!$mod || !$mod->id)
		{
			abort(404);
		}

		if (!auth()->user()
		 || !auth()->user()->can('admin ' . $mod->element))
		{
			abort(403);
		}

		$form = $mod->getForm();

		//$mod->registerLanguage();

		return view('config::admin.module', [
			'module' => $mod,
			'form' => $form
		]);
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
		$id = $request->input('id');

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
				$data['rules'][$k] = array_filter($v, static function ($var)
				{
					return $var !== null;
				});
			}

			$rules = new Rules($data['rules']);
			$asset = Asset::findByName($module->element);

			if (!$asset->id)
			{
				$root = Asset::getRoot();

				$asset->name  = $module->element;
				$asset->title = $module->element;
				$asset->parent_id = $root->id;
				$asset->rules = (string) $rules;
				if (!$asset->saveAsLastChildOf($root))
				{
					return redirect()->back()->withInput()->withError(trans('config::config.failed to save configuration'));
				}
			}
			else
			{
				$asset->rules = (string) $rules;

				if (!$asset->save())
				{
					return redirect()->back()->withInput()->withError(trans('config::config.failed to save configuration'));
				}
			}

			// We don't need this anymore
			unset($data['rules']);
		}

		foreach ($data as $k => $v)
		{
			$module->params->set($k, $v);
		}

		// Attempt to save the configuration.
		if (!$module->save())
		{
			return redirect()->back()->withInput()->withError(trans('config::config.failed to save configuration'));
		}

		return redirect(route('admin.' . strtolower($module->element) . '.index'))->with('success', trans('config::config.configuration saved'));
	}
}
