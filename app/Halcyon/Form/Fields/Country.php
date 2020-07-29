<?php
/**
 * @package    framework
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Halcyon\Form\Fields;

use App\Halcyon\Geocode\Geocode;
use App\Halcyon\Html\Builder\Select as Dropdown;

/**
 * Supports a list of country options.
 */
class Country extends Select
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	protected $type = 'Country';

	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	protected static $countries = null;

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		// Initialize variables.
		$options = array();

		if (!self::$countries)
		{
			self::$countries = array();

			$countries = Geocode::countries();

			if ($countries && !empty($countries))
			{
				self::$countries = $countries;
			}
		}

		if ($this->element['option_blank'])
		{
			$options[] = Dropdown::option('', trans('- Select -'), 'value', 'text');
		}

		foreach (self::$countries as $option)
		{
			// Create a new option object based on the <option /> element.
			$tmp = Dropdown::option(
				(string) $option->code,
				app('translator')->alt(trim((string) $option->name), preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)), 'value', 'text'
			);

			// Add the option object to the result set.
			$options[] = $tmp;
		}

		reset($options);

		return $options;
	}
}
