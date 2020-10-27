<?php
/**
 * @package    framework
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Halcyon\Form;

use Exception;
use Lang;

// Detect if we have full UTF-8 and unicode PCRE support.
if (!defined('COMPAT_UNICODE_PROPERTIES'))
{
	define('COMPAT_UNICODE_PROPERTIES', (bool) @preg_match('/\pL/u', 'a'));
}

/**
 * Form Rule class.
 *
 * @todo  Rewrite all of this.
 */
class Rule
{
	/**
	 * The regular expression to use in testing a form field value.
	 *
	 * @var  string
	 */
	protected $regex;

	/**
	 * The regular expression modifiers to use when testing a form field value.
	 *
	 * @var  string
	 */
	protected $modifiers;

	/**
	 * Method to test the value.
	 *
	 * @param   object   &$element  The SimpleXMLElement object representing the <field /> tag for the form field object.
	 * @param   mixed    $value     The form field value to validate.
	 * @param   string   $group     The field name group control value. This acts as as an array container for the field.
	 *                              For example if the field has name="foo" and the group value is set to "bar" then the
	 *                              full field name would end up being "bar[foo]".
	 * @param   object   &$input    An optional Registry object with the entire data set to validate against the entire form.
	 * @param   object   &$form     The form object for which the field is being tested.
	 * @return  boolean  True if the value is valid, false otherwise.
	 * @throws  Exception on invalid rule.
	 */
	public function test(&$element, $value, $group = null, &$input = null, &$form = null)
	{
		// Check for a valid regex.
		if (empty($this->regex))
		{
			throw new Exception(trans('global.error.invalid form rule', ['class' => get_class($this)]));
		}

		// Add unicode property support if available.
		if (JCOMPAT_UNICODE_PROPERTIES)
		{
			$this->modifiers = (strpos($this->modifiers, 'u') !== false) ? $this->modifiers : $this->modifiers . 'u';
		}

		// Test the value against the regular expression.
		if (preg_match(chr(1) . $this->regex . chr(1) . $this->modifiers, $value))
		{
			return true;
		}

		return false;
	}
}
