<?php

namespace App\Halcyon\Form\Fields;

use App\Halcyon\Form\Field;
use App\Halcyon\Html\Builder\Behavior;

/**
 * This implementation is designed to be compatible with HTML5's <input type="color">
 */
class Color extends Field
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	protected $type = 'Color';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		if (empty($this->value))
		{
			// A color field can't be empty, we default to black. This is the same as the HTML5 spec.
			$this->value = '#000000';
		}

		$this->value = '#' . ltrim((string)$this->value, '#');

		// Initialize some field attributes.
		$attributes = array(
			'type'         => 'text',
			'value'        => htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8'),
			'name'         => $this->name,
			'id'           => $this->id,
			'size'         => ($this->element['size']      ? (int) $this->element['size']      : ''),
			'maxlength'    => ($this->element['maxlength'] ? (int) $this->element['maxlength'] : ''),
			'class'        => 'form-control' . ($this->element['class']     ? ' ' . (string) $this->element['class']  : ''),
			'autocomplete' => ((string) $this->element['autocomplete'] == 'off' ? 'off'      : ''),
			'readonly'     => ((string) $this->element['readonly'] == 'true'    ? 'readonly' : ''),
			'disabled'     => ((string) $this->element['disabled'] == 'true'    ? 'disabled' : ''),
			'required'     => ((string) $this->element['required'] == 'true'    ? 'required' : ''),
			'onchange'     => ($this->element['onchange']  ? (string) $this->element['onchange'] : '')
		);

		if (!$attributes['disabled'])
		{
			Behavior::colorpicker();

			$attributes['class'] .= ' input-colorpicker';
		}

		$attr = array();
		foreach ($attributes as $key => $val)
		{
			if (!$val)
			{
				continue;
			}
			$attr[] = $key . '="' . $val . '"';
		}

		return '<span class="input-color"><input ' . implode(' ', $attr) . ' /></span>';
	}
}
