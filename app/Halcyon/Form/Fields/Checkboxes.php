<?php

namespace App\Halcyon\Form\Fields;

use App\Halcyon\Form\Field;
use App\Halcyon\Html\Builder\Select as Dropdown;

/**
 * Form Field class.
 * Displays options as a list of check boxes.
 * Multiselect may be forced to be true.
 */
class Checkboxes extends Field
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	protected $type = 'Checkboxes';

	/**
	 * Flag to tell the field to always be in multiple values mode.
	 *
	 * @var  bool
	 */
	protected $forceMultiple = true;

	/**
	 * Method to get the field input markup for check boxes.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		// Initialize variables.
		$html = array();

		// Initialize some field attributes.
		$class = $this->element['class'] ? ' class="radio ' . (string) $this->element['class'] . '"' : ' class="radio"';

		// Start the checkbox field output.
		$html[] = '<fieldset id="' . $this->id . '"' . $class . '>';

		// Get the field options.
		$options = $this->getOptions();

		$found  = false;
		$values = (array)$this->value;

		// Build the checkbox field output.
		$i = 0;
		$html[] = '<ul>';
		foreach ($options as $option)
		{
			// Initialize some option attributes.
			$checked = (in_array((string) $option->value, $values) ? ' checked="checked"' : '');
			$class = ' class="form-check-input' . (!empty($option->class) ? ' ' . $option->class : '') . '"';
			$disabled = !empty($option->disable) ? ' disabled="disabled"' : '';

			// Add data attributes
			$dataAttributes = '';
			foreach (get_object_vars($option) as $field => $value)
			{
				$dataField = strtolower(substr($field, 0, 4));
				if ($dataField == 'data')
				{
					$dataAttributes .= ' ' . $field . '="' . $value . '"';
				}
			}

			if ($checked)
			{
				foreach ($values as $k => $v)
				{
					if ($v == $option->value)
					{
						unset($values[$k]);
					}
				}
				$found = true;
			}

			// Initialize some JavaScript option attributes.
			$onclick = !empty($option->onclick) ? ' onclick="' . $option->onclick . '"' : '';

			$html[] = '<li' . (isset($this->element['inline']) ? ' class="d-inline mr-3"' : '') . '>';
			$html[] = '<span class="form-check">';
			$html[] = '<input type="checkbox" id="' . $this->id . $i . '" name="' . $this->name . '"' .
				' value="' . htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8') . '"' . $checked . $class . $onclick . $disabled . $dataAttributes . '/>';

			$html[] = '<label for="' . $this->id . $i . '"' . $class . ' class="form-check-label">' . trans($option->text) . '</label>';
			$html[] = '</span>';
			$html[] = '</li>';

			$i++;
		}

		if ($this->element['option_other'])
		{
			$values = implode('', $values);
			$values = trim($values);

			$checked = '';
			if (!empty($values))
			{
				$checked = ' checked="checked"';
			}
			$class = ' class="form-check-input"';
			$disabled = '';
			$onclick = '';

			$html[] = '<li>';
			$html[] = '<span class="form-check">';
			$html[] = '<input type="checkbox" id="' . $this->id . ($i + 1) . '" name="' . $this->name . '" value=""' . $checked . $class . $onclick . $disabled . '/>';
			$html[] = '<label for="' . $this->id . ($i + 1) . '" class="form-check-label">' . trans('global.other') . '</label>';
			$html[] = '</span>';
			$html[] = '<input type="text" id="' . $this->id . '_other" name="' . substr($this->getName($this->fieldname . '_other'), 0, -2) . '" value="' . ($checked ? htmlspecialchars($values, ENT_COMPAT, 'UTF-8') : '') . '"' . $class . $onclick . $disabled . '/>';
			$html[] = '</li>';
		}

		$html[] = '</ul>';

		// End the checkbox field output.
		$html[] = '</fieldset>';

		return implode($html);
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  array<int,\stdClass>  The field option objects.
	 */
	protected function getOptions()
	{
		// Initialize variables.
		$options = array();

		foreach ($this->element->children() as $option)
		{
			// Only add <option /> elements.
			if ($option->getName() != 'option')
			{
				continue;
			}

			$label = (isset($option[0]) ? $option[0] : $option['label']);

			// Create a new option object based on the <option /> element.
			$tmp = Dropdown::option((string) $option['value'], trim((string) $label), 'value', 'text',
				((string) $option['disabled'] == 'true')
			);

			// Add data attributes
			foreach ($option->attributes() as $index => $value)
			{
				$dataCheck = strtolower(substr($index, 0, 4));
				if ($dataCheck == 'data')
				{
					$tmp->$index = (string) $value;
				}
			}

			// Set some option attributes.
			$tmp->class = (string) $option['class'];

			// Set some JavaScript option attributes.
			$tmp->onclick = (string) $option['onclick'];

			// Add the option object to the result set.
			$options[] = $tmp;
		}

		reset($options);

		return $options;
	}
}
