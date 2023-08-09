<?php

namespace App\Halcyon\Form\Rules;

use App\Halcyon\Form\Rule;

/**
 * Requires the value entered be one of the options in a field of type="list"
 */
class Options extends Rule
{
	/**
	 * @inheritdoc
	 */
	public function test(&$element, $value, $group = null, &$input = null, &$form = null)
	{
		// Check each value and return true if we get a match
		foreach ($element->option as $option)
		{
			if ($value == (string) $option->attributes()->value)
			{
				return true;
			}
		}

		return false;
	}
}
