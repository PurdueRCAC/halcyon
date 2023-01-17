<?php

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
	 * @return  array<int,\stdClass>  The field option objects.
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
