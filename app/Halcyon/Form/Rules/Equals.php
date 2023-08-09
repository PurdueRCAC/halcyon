<?php

namespace App\Halcyon\Form\Rules;

use App\Halcyon\Form\Form;
use App\Halcyon\Form\Rule;
use Exception;

/**
 * Form Rule class for testing a value equals another.
 */
class Equals extends Rule
{
	/**
	 * @inheritdoc
	 */
	public function test(&$element, $value, $group = null, &$input = null, &$form = null)
	{
		// Initialize variables.
		$field = (string) $element['field'];

		// Check that a validation field is set.
		if (!$field)
		{
			return new Exception('core::core.error.invalid form rule' . get_class($this));
		}

		// Check that a valid Form object is given for retrieving the validation field value.
		if (!($form instanceof Form))
		{
			return new Exception('core::core.error.invalid form object' . get_class($this));
		}

		// Test the two values against each other.
		if ($value == $input->get($field))
		{
			return true;
		}

		return false;
	}
}
