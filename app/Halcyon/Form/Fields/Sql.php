<?php

namespace App\Halcyon\Form\Fields;

use App\Halcyon\Html\Builder\Select as Dropdown;
use Illuminate\Support\Facades\DB;

/**
 * Supports an custom SQL select list
 */
class Sql extends Select
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'SQL';

	/**
	 * Method to get the custom field options.
	 * Use the query attribute to supply a query to generate the list.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		// Initialize variables.
		$options = array();

		// Initialize some field attributes.
		$key       = $this->element['key_field']   ? (string) $this->element['key_field']   : 'value';
		$value     = $this->element['value_field'] ? (string) $this->element['value_field'] : (string) $this->element['name'];
		$translate = $this->element['translate']   ? (string) $this->element['translate']   : false;
		$query     = (string) $this->element['query'];

		// Set the query and get the result list.
		$items = DB::select($query);

		// Build the field options.
		if (!empty($items))
		{
			foreach ($items as $item)
			{
				if ($translate == true)
				{
					$options[] = Dropdown::option($item->{$key}, trans($item->{$value}));
				}
				else
				{
					$options[] = Dropdown::option($item->{$key}, $item->{$value});
				}
			}
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
