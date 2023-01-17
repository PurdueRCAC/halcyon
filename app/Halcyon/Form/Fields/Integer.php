<?php

namespace App\Halcyon\Form\Fields;

use App\Halcyon\Form\Field;
use App\Halcyon\Html\Builder\Select as Dropdown;

/**
 * Provides a select list of integers with specified first, last and step values.
 */
class Integer extends Select
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	protected $type = 'Integer';

	/**
	 * Method to get the field options.
	 *
	 * @return  array<int,\stdClass>  The field option objects.
	 */
	protected function getOptions()
	{
		// Initialize variables.
		$options = array();

		// Initialize some field attributes.
		$first = (int) $this->element['first'];
		$last  = (int) $this->element['last'];
		$step  = (int) $this->element['step'];

		// Sanity checks.
		if ($step == 0)
		{
			// Step of 0 will create an endless loop.
			return $options;
		}
		elseif ($first < $last && $step < 0)
		{
			// A negative step will never reach the last number.
			return $options;
		}
		elseif ($first > $last && $step > 0)
		{
			// A position step will never reach the last number.
			return $options;
		}

		// Build the options array.
		for ($i = $first; $i <= $last; $i += $step)
		{
			$options[] = Dropdown::option($i);
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
