<?php

namespace App\Halcyon\Form\Fields;

use App\Halcyon\Form\Field;
use App\Halcyon\Html\Builder\Asset;

/**
 * Text field for passwords
 */
class Password extends Field
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	protected $type = 'Password';

	/**
	 * Method to get the field input markup for password.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		// Initialize some field attributes.
		$size      = $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
		$maxLength = $this->element['maxlength'] ? ' maxlength="' . (int) $this->element['maxlength'] . '"' : '';
		$class     = $this->element['class'] ? ' class="form-control ' . (string) $this->element['class'] . '"' : 'class="form-control"';
		$auto      = ((string) $this->element['autocomplete'] == 'off') ? ' autocomplete="off"' : '';
		$readonly  = ((string) $this->element['readonly'] == 'true') ? ' readonly="readonly"' : '';
		$disabled  = ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$meter     = ((string) $this->element['strengthmeter'] == 'true' ? ' data-meter="true"' : '');
		$required  = ((string) $this->element['required'] == 'true'    ? ' required="required"' : '');
		$threshold = $this->element['threshold'] ? (int) $this->element['threshold'] : 66;

		$value = '';
		if ($this->value && is_string($this->value))
		{
			$value = $this->value;
		}

		return '<input type="password" name="' . $this->name . '" id="' . $this->id . '"' .
			' value="' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '"' .
			$auto . $class . $readonly . $disabled . $size . $maxLength . $meter . $required . ' autocomplete="off" />';
	}
}
