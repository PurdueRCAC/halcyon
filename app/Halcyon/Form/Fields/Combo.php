<?php

namespace App\Halcyon\Form\Fields;

use App\Halcyon\Form\Fields\Select;

/**
 * Implements a combo box field.
 */
class Combo extends Select
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'Combo';

	/**
	 * Method to get the field input markup for a combo box field.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		// Initialize variables.
		$html = array();
		$attr = '';

		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="form-control combobox ' . (string) $this->element['class'] . '"' : ' class="form-control combobox"';
		$attr .= ((string) $this->element['readonly'] == 'true') ? ' readonly="readonly"' : '';
		$attr .= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$attr .= ((string) $this->element['required'] == 'true') ? ' required="required"' : '';
		$attr .= $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		// Get the field options.
		$options = $this->getOptions();

		// Build the input for the combo box.
		$html[] = '<input type="text" name="' . $this->name . '" id="' . $this->id . '" list="' . $this->id . 'list" value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"' . $attr . '/>';

		// Build the list for the combo box.
		$html[] = '<datalist id="' . $this->id . 'list">';
		foreach ($options as $option)
		{
			$html[] = '<option value="' . htmlspecialchars($option->text, ENT_COMPAT, 'UTF-8') . '">' . $option->text . '</option>';
		}
		$html[] = '</datalist>';

		return implode($html);
	}
}
