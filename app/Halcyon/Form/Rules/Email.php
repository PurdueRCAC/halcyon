<?php

namespace App\Halcyon\Form\Rules;

use App\Halcyon\Form\Form;
use App\Modules\Users\Models\User;
use App\Halcyon\Form\Rule;

/**
 * Form Rule class for email.
 */
class Email extends Rule
{
	/**
	 * The regular expression to use in testing a form field value.
	 *
	 * @var  string
	 */
	protected $regex = '^[a-zA-Z0-9.!#$%&’*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$';

	/**
	 * @inheritdoc
	 */
	public function test(&$element, $value, $group = null, &$input = null, &$form = null)
	{
		// If the field is empty and not required, the field is valid.
		$required = ((string) $element['required'] == 'true' || (string) $element['required'] == 'required');

		if (!$required && empty($value))
		{
			return true;
		}

		// Test the value against the regular expression.
		if (!parent::test($element, $value, $group, $input, $form))
		{
			return false;
		}

		// Check if we should test for uniqueness.
		$unique = ((string) $element['unique'] == 'true' || (string) $element['unique'] == 'unique');

		if ($unique)
		{
			// Get the extra field check attribute.
			$userId = ($form instanceof Form) ? $form->getValue('id') : '';

			$duplicate = User::query()
				->where('email', '=', $value)
				->where('id', '<>', (int) $userId)
				->count();

			if ($duplicate)
			{
				return false;
			}
		}

		return true;
	}
}
