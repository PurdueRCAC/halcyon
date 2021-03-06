<?php

namespace App\Halcyon\Form\Fields;

use App\Halcyon\Html\Builder\Access;

/**
 * Provides a list of access levels. Access levels control what users in specific
 * groups can see.
 */
class Accesslevel extends Select
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'Accesslevel';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		// Initialize variables.
		$attr = '';

		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="form-control ' . (string) $this->element['class'] . '"' : 'class="form-control"';
		$attr .= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$attr .= $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
		$attr .= $this->multiple ? ' multiple="multiple"' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		// Get the field options.
		$options = $this->getOptions();

		return Access::level($this->name, $this->value, $attr, $options, $this->id);
	}
}
