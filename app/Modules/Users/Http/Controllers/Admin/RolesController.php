<?php

namespace App\Modules\Users\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Halcyon\Access\Role;
use App\Halcyon\Access\Gate;
use App\Halcyon\Access\Rules;
use App\Halcyon\Access\Asset;

class RolesController extends Controller
{
	/**
	 * Display a listing of roles.
	 *
	 * @param  Request $request
	 * @return View
	 */
	public function index(Request $request)
	{
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

		return view('users::admin.roles.index', [
			//'form' => $form,
			'section' => $section,
			'module' => '',
			'roles' => $roles,
			'assetId' => $assetId,
			'assetRules' => $assetRules,
			'actions' => $actions,
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 * 
	 * @return View
	 */
	public function create()
	{
		$row = new Role;

		$options = Role::query()
			->orderBy('lft', 'asc')
			->get();

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		return view('users::admin.roles.edit', [
			'row' => $row,
			'options' => $options
		]);
	}

	/**
	 * Store a newly created entry
	 * 
	 * @param  Request $request
	 * @return RedirectResponse
	 */
	public function store(Request $request)
	{
		$rules = [
			'fields.title' => 'required|string|max:100'
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$id = $request->input('id');

		$row = $id ? Role::findOrFail($id) : new Role();
		$row->fill($request->input('fields'));

		// Check the super admin permissions for group
		// We get the parent group permissions and then check the group permissions manually
		// We have to calculate the group permissions manually because we haven't saved the group yet
		$parentSuperAdmin = Gate::checkRole($row->parent_id, 'admin');

		// Get admin rules from the root asset
		$rules = Gate::getAssetRules('root.1')->getData();

		// Get the value for the current group (will be true (allowed), false (denied), or null (inherit)
		$roleSuperAdmin = $rules['admin']->allow($row->id);

		// We only need to change the $roleSuperAdmin if the parent is true or false. Otherwise, the value set in the rule takes effect.
		if ($parentSuperAdmin === false)
		{
			// If parent is false (Denied), effective value will always be false
			$roleSuperAdmin = false;
		}
		elseif ($parentSuperAdmin === true)
		{
			// If parent is true (allowed), group is true unless explicitly set to false
			$roleSuperAdmin = ($roleSuperAdmin === false) ? false : true;
		}

		// Check for non-super admin trying to save with super admin group
		$iAmSuperAdmin = auth()->user()->can('admin');

		if (!$iAmSuperAdmin && $roleSuperAdmin)
		{
			return redirect()->back()->withError(trans('users::users.error.not super admin'));
		}

		// Check for super-admin changing self to be non-super-admin
		// First, are we a super admin?
		/*if ($iAmSuperAdmin)
		{
			// Next, are we a member of the current group?
			$myRoles = Gate::getRolesByUser(auth()->user()->id, false);

			if (in_array($fields['id'], $myRoles))
			{
				// Now, would we have super admin permissions without the current group?
				$otherGroups = array_diff($myRoles, array($fields['id']));
				$otherSuperAdmin = false;
				foreach ($otherGroups as $otherGroup)
				{
					$otherSuperAdmin = ($otherSuperAdmin) ? $otherSuperAdmin : Gate::checkRole($otherGroup, 'admin');
				}

				// If we would not otherwise have super admin permissions
				// and the current group does not have super admin permissions, throw an exception
				if (!$otherSuperAdmin && !$groupSuperAdmin)
				{
					Notify::error(Lang::txt('users::users.error.cannot demote self'));
					return $this->editTask($row);
				}
			}
		}*/

		if (!$row->save())
		{
			return redirect()->back()->withError(trans('global.messages.save failed'));
		}

		return $this->cancel()->with('success', trans('global.messages.' . ($id ? 'item updated' : 'item created')));
	}

	/**
	 * Show the form for editing the specified resource.
	 * 
	 * @param  int  $id
	 * @return View
	 */
	public function edit($id)
	{
		$row = Role::findOrFail($id);

		$options = Role::query()
			->where('id', '!=', $row->id)
			->orderBy('lft', 'asc')
			->get();

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		return view('users::admin.roles.edit', [
			'row' => $row,
			'options' => $options
		]);
	}

	/**
	 * Remove the specified resource from storage.
	 * 
	 * @param  Request $request
	 * @return RedirectResponse
	 */
	public function delete(Request $request)
	{
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;
		$errors = [];

		foreach ($ids as $id)
		{
			$row = Role::findOrFail($id);

			if (!$row->delete())
			{
				$errors[] = trans('global.messages.delete failed');
				continue;
			}

			$success++;
		}

		if ($errors)
		{
			$request->session()->flash('error', collect($errors));
		}

		if ($success)
		{
			$request->session()->flash('success', trans('global.messages.item deleted', ['count' => $success]));
		}

		return $this->cancel();
	}

	/**
	 * Store config changes
	 *
	 * @param  Request $request
	 * @return RedirectResponse
	 */
	public function update(Request $request)
	{
		$data = $request->input('permissions', array());

		// Save the rules.
		foreach ($data as $k => $v)
		{
			$data[$k] = array_filter($v);
		}

		$rules = new Rules($data);

		// Check that we aren't removing our Super User permission
		// Need to get roles from database, since they might have changed
		$myRoles = Gate::getRolesByUser(auth()->user()->id);
		$myRules = $rules->getData();

		$hasSuperAdmin = $myRules['admin']->allow($myRoles);
		/*$hasSuperAdmin = null;
		foreach ($myRoles as $role)
		{
			$hasSuperAdmin = $myRules['admin']->allow($role);
			if ($hasSuperAdmin)
			{
				break;
			}
		}*/

		if (!$hasSuperAdmin)
		{
			return redirect()->back()->withInput()->withError(trans('users::users.error.removing super admin'));
		}

		$asset = Asset::getRoot();

		if (!$asset->id)
		{
			return redirect()->back()->withInput()->withError(trans('users::users.error.root asset not found'));
		}

		$asset->rules = (string) $rules;

		if (!$asset->save())
		{
			return redirect()->back()->withInput()->withError(trans('users::users.error.asset save failed'));
		}

		return $this->cancel()->with('success', trans('users::users.configuration saved'));
	}

	/**
	 * Return to the main view
	 *
	 * @return  RedirectResponse
	 */
	public function cancel()
	{
		return redirect(route('admin.users.roles'));
	}
}
