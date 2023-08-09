<?php

namespace App\Halcyon\Form\Fields;

use App\Halcyon\Html\Builder\Input;
use App\Halcyon\Form\Field;
use Carbon\Carbon;
//use DateTimeZone;

/**
 * Provides a pop up date picker linked to a button.
 * Optionally may be filtered to use user's or server's time zone.
 */
class Calendar extends Field
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'Calendar';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		// Initialize some field attributes.
		$format = $this->element['format'] ? (string) $this->element['format'] : '%Y-%m-%d %H:%M:%S';

		// Build the attributes array.
		$attributes = array();
		if ($this->element['size'])
		{
			$attributes['size'] = (int) $this->element['size'];
		}
		if ($this->element['maxlength'])
		{
			$attributes['maxlength'] = (int) $this->element['maxlength'];
		}
		if ($this->element['class'])
		{
			$attributes['class'] = (string) $this->element['class'];
		}
		if ($this->element['placeholder'])
		{
			$attributes['placeholder'] = trans((string) $this->element['placeholder']);
		}
		if ((string) $this->element['readonly'] == 'true')
		{
			$attributes['readonly'] = 'readonly';
		}
		if ((string) $this->element['disabled'] == 'true')
		{
			$attributes['disabled'] = 'disabled';
		}
		if ((string) $this->element['required'] == 'true')
		{
			$attributes['required'] = 'required';
		}
		$attributes['time'] = false;
		if ((string) $this->element['time'] == 'true')
		{
			$attributes['time'] = true;
		}
		if ($this->element['onchange'])
		{
			$attributes['onchange'] = (string) $this->element['onchange'];
		}

		// Handle the special case for "now".
		if (is_string($this->value) && strtoupper($this->value) == 'NOW')
		{
			$this->value = date($format);
		}

		if ($this->value)
		{
			// Get a date object based on the correct timezone.
			$date = Carbon::parse($this->value);

			// Transform the date string.
			$this->value = $date->toDateTimeString();
		}

		$attributes['id'] = $this->id;
		$attributes['format'] = $format;

		return Input::calendar($this->name, $this->value, $attributes);
	}
}
