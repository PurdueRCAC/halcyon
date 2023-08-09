<?php

namespace App\Halcyon\Form\Rules;

use App\Halcyon\Form\Rule;

/**
 * Form Rule class for color values.
 */
class Color extends Rule
{
	/**
	 * @inheritdoc
	 */
	public function test(&$element, $value, $group = null, &$input = null, &$form = null)
	{
		$value = trim($value);

		if (empty($value))
		{
			// A color field can't be empty, we default to black. This is the same as the HTML5 spec.
			$value = '#000000';
			return true;
		}

		if ($value[0] != '#')
		{
			return false;
		}

		// Remove the leading # if present to validate the numeric part
		$value = ltrim($value, '#');

		// The value must be 6 or 3 characters long
		if (!((strlen($value) == 6 || strlen($value) == 3) && ctype_xdigit($value)))
		{
			return false;
		}

		// Prepend the # again
		$value = '#' . $value;

		return true;
	}
}
