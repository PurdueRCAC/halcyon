<?php

namespace App\Halcyon\Form\Rules;

use App\Modules\Users\Models\User;
use App\Halcyon\Form\Rule;

/**
 * Form Rule class for usernames.
 */
class Username extends Rule
{
	/**
	 * Method to test for a valid color in hexadecimal.
	 *
	 * @param   object   &$element  The SimpleXMLElement object representing the <field /> tag for the form field object.
	 * @param   mixed    $value     The form field value to validate.
	 * @param   string   $group     The field name group control value. This acts as as an array container for the field.
	 *                              For example if the field has name="foo" and the group value is set to "bar" then the
	 *                              full field name would end up being "bar[foo]".
	 * @param   object   &$input    An optional Repository object with the entire data set to validate against the entire form.
	 * @param   object   &$form     The form object for which the field is being tested.
	 * @return  boolean  True if the value is valid, false otherwise.
	 */
	public function test(&$element, $value, $group = null, &$input = null, &$form = null)
	{
		$duplicate = User::query()
			->where('username', '=', $value)
			->where('id', '<>', (int) $userId)
			->count();

		if ($duplicate)
		{
			return false;
		}

		return true;
	}
}
