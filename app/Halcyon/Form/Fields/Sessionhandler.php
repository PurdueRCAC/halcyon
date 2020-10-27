<?php
/**
 * @package    framework
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Halcyon\Form\Fields;

use App\Halcyon\Html\Builder\Select as Dropdown;
use App\Halcyon\Session\Manager;

/**
 * Provides a select list of session handler options.
 */
class Sessionhandler extends Select
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	protected $type = 'Sessionhandler';

	/**
	 * Method to get the session handler field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		// Initialize variables.
		$options = array();

		// Get the options from Session.
		foreach (Manager::getStores() as $store)
		{
			$options[] = Dropdown::option($store, trans('global.other.session.' . $store), 'value', 'text');
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
