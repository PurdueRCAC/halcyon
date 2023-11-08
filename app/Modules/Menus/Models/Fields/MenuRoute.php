<?php

namespace App\Modules\Menus\Models\Fields;

use App\Halcyon\Form\Field;
use App\Halcyon\Html\Builder\Select as Dropdown;
use App\Halcyon\Form\Fields\Groupedlist;
use App\Modules\Menus\Events\CollectingRoutes;
use stdClass;

/**
 * Supports an HTML grouped select list of menu item grouped by menu
 */
class MenuRoute extends Groupedlist
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'MenuRoute';

	/**
	 * Method to get the field option groups.
	 *
	 * @return  array<string,array<int,\stdClass>>  The field option objects as a nested array in groups.
	 */
	protected function getGroups()
	{
		event($e = new CollectingRoutes);

		$routes = $e->routes;

		// Build the groups arrays.
		$groups = array();

		foreach ($routes as $group => $items)
		{
			// Initialize the group.
			$groups[$group] = array();

			// Build the options array.
			foreach ($items as $item)
			{
				$item['path'] = trim($item['path'], '/');
				if ($item['path'] && substr($item['path'], 0, 4) != 'http')
				{
					$item['path'] = '/' . $item['path'];
				}

				//$selected = $item['value'] == $this->value ? ' selected="selected"' : '';

				$opt = new stdClass;
				$opt->value = $item['value'];
				$opt->text = $item['indent'] . $item['text'];
				$opt->indent = $item['indent'];
				$opt->path = $item['path'];
				$opt->selected = $item['value'] == $this->value ? ' selected="selected"' : '';

				$groups[$group][] = $opt;

				//$groups[$group][] = '<option value="' . e($item['value']) . '" data-indent="' . e($item['indent']) . '" data-path="' . e($item['path']) . '"' . $selected . '>' . e($item['indent'] . $item['text']). '</option>';
			}
		}

		ksort($groups);

		return $groups;
	}

	/**
	 * Method to get the field input markup fora grouped list.
	 * Multiselect is enabled by using the multiple attribute.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		// Initialize variables.
		$html = array();
		$attr = '';

		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="form-control ' . (string) $this->element['class'] . '"' : ' class="form-control"';
		$attr .= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$attr .= ((string) $this->element['readonly'] == 'true') ? ' readonly="readonly"' : '';
		$attr .= $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
		$attr .= $this->element['required'] ? ' required="required"' : '';
		$attr .= $this->multiple ? ' multiple="multiple"' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		if (!strstr($this->value, '::'))
		{
			$this->value = 'pages::' . $this->value;
		}

		// Get the field groups.
		$groups = (array) $this->getGroups();

		$html = array();
		$html[] = '<select name="' . e($this->name) . '" id="' . $this->id . '"' . $attr . '>';
		foreach ($groups as $title => $items)
		{
			$title = str_replace('00_', '', $title);

			$html[] = '<optgroup label="' . e($title) . '">';
			foreach ($items as $item)
			{
				//$html[] = $item;
				$html[] = '<option value="' . e($item->value) . '" data-indent="' . e($item->indent) . '" data-path="' . e($item->path) . '"' . $item->selected . '>' . e($item->text) . '</option>';
			}
			$html[] = '</optgroup>';
		}
		$html[] = '</select>';

		return implode("\n", $html);
	}
}
