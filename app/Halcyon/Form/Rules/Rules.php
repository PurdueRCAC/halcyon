<?php

namespace App\Halcyon\Form\Rules;

use App\Halcyon\Access\Gate;
use App\Halcyon\Form\Rule;

/**
 * Form Rule class for rules.
 */
class Rules extends Rule
{
	/**
	 * @inheritdoc
	 */
	public function test(&$element, $value, $group = null, &$input = null, &$form = null)
	{
		// Get the possible field actions and the ones posted to validate them.
		$fieldActions = self::getFieldActions($element);
		$valueActions = self::getValueActions($value);

		// Make sure that all posted actions are in the list of possible actions for the field.
		foreach ($valueActions as $action)
		{
			if (!in_array($action, $fieldActions))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to get the list of permission action names from the form field value.
	 *
	 * @param   mixed  $value  The form field value to validate.
	 * @return  array<int,string>  A list of permission action names from the form field value.
	 */
	protected function getValueActions($value)
	{
		// Initialise variables.
		$actions = array();

		// Iterate over the asset actions and add to the actions.
		foreach ((array) $value as $name => $rules)
		{
			$actions[] = $name;
		}

		return $actions;
	}

	/**
	 * Method to get the list of possible permission action names for the form field.
	 *
	 * @param   object  $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
	 * @return  array<int,string>   A list of permission action names from the form field element definition.
	 */
	protected function getFieldActions($element)
	{
		// Initialise variables.
		$actions = array();

		// Initialise some field attributes.
		$section = $element['section'] ? (string) $element['section'] : '';
		$module  = $element['module'] ? (string) $element['module'] : '';

		// Get the asset actions for the element.
		//$comfile   = $module ? app_path('/Modules/' . $module . '/Config/Access.xml') : '';
		//$section   = $section ? "/access/section[@name='" . $section . "']/" : '';
		//$elActions = Gate::getActionsFromFile($comfile, $section);

		$elActions = include module_path('Core') . '/Config/permissions.php';

		$comfile = $module ? module_path($module) . '/Config/permissions.php' : '';
		$section = $element['section'] ? (string) $element['section'] : '';
		if (is_file($comfile))
		{
			$elActions = include $comfile;
		}
		$elActions = isset($elActions[$section]) ? $elActions[$section] : $elActions;


		if (is_array($elActions))
		{
			// Iterate over the asset actions and add to the actions.
			foreach ($elActions as $k => $item)
			{
				$actions[] = isset($item['name']) ? $item['name'] : $k;
			}
		}

		// Iterate over the children and add to the actions.
		foreach ($element->children() as $el)
		{
			if ($el->getName() == 'action')
			{
				$actions[] = (string) $el['name'];
			}
		}

		return $actions;
	}
}
