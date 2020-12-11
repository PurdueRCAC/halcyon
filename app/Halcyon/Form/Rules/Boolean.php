<?php

namespace App\Halcyon\Form\Rules;

use App\Halcyon\Form\Rule;

/**
 * Form Rule for boolean values.
 */
class Boolean extends Rule
{
	/**
	 * The regular expression to use in testing a form field value.
	 *
	 * @var  string
	 */
	protected $regex = '^(?:[01]|true|false)$';

	/**
	 * The regular expression modifiers to use when testing a form field value.
	 *
	 * @var  string
	 */
	protected $modifiers = 'i';
}
