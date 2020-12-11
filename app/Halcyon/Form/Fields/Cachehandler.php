<?php

namespace App\Halcyon\Form\Fields;

use App\Halcyon\Html\Builder\Select as Dropdown;
use App\Halcyon\Cache\Manager;

/**
 * Provides a list of available cache handlers
 */
class Cachehandler extends Select
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'Cachehandler';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		// Initialize variables.
		$options = array();

		// Convert to name => name array.
		foreach (Manager::getStores() as $store)
		{
			$options[] = Dropdown::option($store, trans('global.cache.' . $store), 'value', 'text');
		}

		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
