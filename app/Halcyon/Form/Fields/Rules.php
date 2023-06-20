<?php

namespace App\Halcyon\Form\Fields;

use Illuminate\Support\Collection;
use App\Halcyon\Form\Field;
use App\Halcyon\Access\Gate;
use App\Halcyon\Access\Role;
use App\Halcyon\Access\Asset;
use App\Halcyon\Html\Builder\Behavior;
use Exception;

/**
 * Field for assigning permissions to groups for a given asset
 */
class Rules extends Field
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'Rules';

	/**
	 * Method to get the field input markup for Access Control Lists.
	 * Optionally can be associated with a specific module and section.
	 *
	 * TODO: Add access check.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		//Behavior::tooltip();

		// Initialise some field attributes.
		$section    = $this->element['section'] ? (string) $this->element['section'] : 'global';
		$section    = $section == 'component' ? 'module' : $section;
		$module     = $this->element['module'] ? (string) $this->element['module'] : 'users';
		$assetField = $this->element['asset_field'] ? (string) $this->element['asset_field'] : 'asset_id';

		// Get the actions for the asset.
		$comfile = $module ? module_path($module) . '/Config/Access.xml' : '';
		$sectioned = "/access/section[@name='" . $section . "']/";
		$actions = Gate::getActionsFromFile($comfile, $sectioned);

		$comfile = module_path($module) . '/Config/permissions.php';

		if (!is_file($comfile))
		{
			$comfile = module_path('users') . '/Config/defaultpermissions.php';
		}

		if (is_file($comfile))
		{
			$actions = include $comfile;
		}

		if (!$actions)
		{
			$actions = array($section => array());
		}
			// Iterate over the children and add to the actions.
			foreach ($this->element->children() as $el)
			{
				if ($el->getName() == 'action')
				{
					$actions[$section][(string) $el['name']] = array(
						//'name' => (string) $el['name'],
						'title' => (string) $el['title'],
						'description' => (string) $el['description']
					);
				}
			}

		// Get the explicit rules for this asset.
		if ($section == 'module')
		{
			// Need to find the asset id by the name of the module.
			$asset = Asset::query()
				->where('name', '=', $module)
				->first();

			$assetId = $asset ? (int) $asset->id : 0;
		}
		else
		{
			$assetId = (int) $this->element['asset_id'];
			// Find the asset id of the content.
			// Note that for global configuration, com_config injects asset_id = 1 into the form.
			$assetId = $assetId ?: $this->form->getValue($assetField);
		}

		// Full width format.

		// Get the rules for just this asset (non-recursive).
		//echo '------------<br />';
		//var_dump($assetId);
		$assetRules = Gate::getAssetRules($assetId);
		//echo '------------<br />';

		// Get the available user groups.
		$groups = $this->getUserGroups();

		// Build the form control.
		$curLevel = 0;

		// Prepare output
		$html = array();
		$html[] = '<div id="permissions-sliders" class="pane-sliders">';
		$html[] = '<p class="rule-desc">' . trans('access.rules.setting desc') . '</p>';
		$html[] = '<div id="permissions-rules">';
		// If AssetId is blank and section wasn't set to module, set it to the module name here for inheritance checks.
		$assetId = empty($assetId) && $section != 'module' ? $module : $assetId;

		// Start a row for each user group.
		foreach ($groups as $group)
		{
			$difLevel = $group->level - $curLevel;

			$html[] = '<h3 class="pane-toggler title">';
			//$html[] = '<input type="checkbox" name="cb' . $group->value . '" value="' . $group->value . '" /> &nbsp;';
			$html[] = str_repeat('<span class="level">|&mdash;</span> ', $curLevel = $group->level) . $group->text;
			$html[] = ' <span class="badge badge-secondary">' . number_format($group->maps_count) . '</span></h3>';
			$html[] = '<div class="pane-slider">';
			$html[] = '<div class="pane-slider content pane-hide">';

			$html[] = '<table class="table group-rules">';
			$html[] = '<thead>';
			$html[] = '<tr>';

			$html[] = '<th class="actions" id="actions-th' . $group->value . '">';
			$html[] = '<span class="acl-action">' . trans('access.rules.action') . '</span>';
			$html[] = '</th>';

			$html[] = '<th class="settings" id="settings-th' . $group->value . '">';
			$html[] = '<span class="acl-action">' . trans('access.rules.select setting') . '</span>';
			$html[] = '</th>';

			// The calculated setting is not shown for the root group of global configuration.
			$canCalculateSettings = ($group->parent_id || !empty($module));
			if ($canCalculateSettings)
			{
				$html[] = '<th id="aclactionth' . $group->value . '">';
				$html[] = '<span class="acl-action">' . trans('access.rules.calculated setting') . '</span>';
				$html[] = '</th>';
			}

			$html[] = '</tr>';
			$html[] = '</thead>';
			$html[] = '<tbody>';

			//$section = $section ?: 'module';

			foreach ($actions[$section] as $name => $action)
			{
				$action['name'] = $name;

				$html[] = '<tr>';
				$html[] = '<td headers="actions-th' . $group->value . '">';
				$html[] = '<label data-toggle="tooltip" for="' . $this->id . '_' . $action['name'] . '_' . $group->value . '" title="' . htmlspecialchars(trans($action['title']) . '::' . trans($action['description']), ENT_COMPAT, 'UTF-8') . '">';
				$html[] = trans($action['title']);
				$html[] = '</label>';
				$html[] = '</td>';

				$html[] = '<td headers="settings-th' . $group->value . '">';

				$html[] = '<select class="form-control" name="' . $this->name . '[' . $action['name'] . '][' . $group->value . ']" id="' . $this->id . '_' . $action['name']
					. '_' . $group->value . '" title="'
					. trans('access.rules.select allow deny role', ['title' => trans($action['title']), 'role' => trim($group->text)]) . '">';
				$inheritedRule = Gate::checkRole($group->value, $action['name'], $assetId);

				// Get the actual setting for the action for this group.
				$assetRule = $assetRules->allow($action['name'], $group->value);

				// Build the dropdowns for the permissions sliders

				// The parent group has "Not Set", all children can rightly "Inherit" from that.
				$html[] = '<option value=""' . ($assetRule === null ? ' selected="selected"' : '') . '>' . trans(empty($group->parent_id) && empty($module) ? 'access.rules.not set' : 'access.rules.inherited') . '</option>';
				$html[] = '<option value="1"' . ($assetRule === true ? ' selected="selected"' : '') . '>' . trans('access.rules.allowed') . '</option>';
				$html[] = '<option value="0"' . ($assetRule === false ? ' selected="selected"' : '') . '>' . trans('access.rules.denied') . '</option>';

				$html[] = '</select>';

				// If this asset's rule is allowed, but the inherited rule is deny, we have a conflict.
				if (($assetRule === true) && ($inheritedRule === false))
				{
					$html[] = '&#160; ' . trans('access.rules.conflict');
				}

				$html[] = '</td>';

				// Build the Calculated Settings column.
				// The inherited settings column is not displayed for the root group in global configuration.
				if ($canCalculateSettings)
				{
					$html[] = '<td headers="aclactionth' . $group->value . '">';

					// This is where we show the current effective settings considering currrent group, path and cascade.
					// Check whether this is a module or global. Change the text slightly.
					if (Gate::checkRole($group->value, 'admin', $assetId) !== true)
					{
						if ($inheritedRule === null)
						{
							$html[] = '<span class="state no icon-unset">' . trans('access.rules.not allowed') . '</span>';
						}
						elseif ($inheritedRule === true)
						{
							$html[] = '<span class="state yes icon-allowed">' . trans('access.rules.allowed') . '</span>';
						}
						elseif ($inheritedRule === false)
						{
							if ($assetRule === false)
							{
								$html[] = '<span class="state no icon-denied">' . trans('access.rules.not allowed') . '</span>';
							}
							else
							{
								$html[] = '<span class="state no icon-denied"><span class="fa fa-lock"></span> ' . trans('access.rules.not allowed locked') . '</span>';
							}
						}
					}
					elseif (!empty($module))
					{
						$html[] = '<span class="state yes icon-allowed"><span class="fa fa-lock"></span> ' . trans('access.rules.allowed admin') . '</span>';
					}
					else
					{
						// Special handling for  groups that have global admin because they can't be denied.
						// The admin rights can be changed.
						if ($action['name'] === 'admin')
						{
							$html[] = '<span class="state yes icon-allowed">' . trans('access.rules.allowed') . '</span>';
						}
						elseif ($inheritedRule === false)
						{
							// Other actions cannot be changed.
							$html[] = '<span class="state no icon-denied"><span class="fa fa-lock">' . trans('access.rules.not allowed admin conflict') . '</span></span>';
						}
						else
						{
							$html[] = '<span class="state yes icon-allowed"><span class="fa fa-lock">' . trans('access.rules.allowed admin') . '</span></span>';
						}
					}

					$html[] = '</td>';
				}

				$html[] = '</tr>';
			}

			$html[] = '</tbody>';
			$html[] = '</table>';

			$html[] = '</div>';
			$html[] = '</div>';
		}

		$html[] = '</div>';
		$html[] = '<div class="rule-notes">';
		if ($section == 'module' || $section == null)
		{
			$html[] = trans('access.rules.setting notes');
		}
		else
		{
			$html[] = trans('access.rules.setting notes item');
		}
		$html[] = '</div>';
		$html[] = '</div>';

		return implode("\n", $html);
	}

	/**
	 * Get a list of the user groups.
	 *
	 * @return  Collection
	 */
	protected function getUserGroups()
	{
		return Role::tree();
	}
}
