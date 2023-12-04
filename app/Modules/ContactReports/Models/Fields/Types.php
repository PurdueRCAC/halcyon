<?php

namespace App\Modules\ContactReports\Models\Fields;

use App\Modules\ContactReports\Models\Type;
use App\Halcyon\Form\Fields\Select;
use stdClass;

/**
 * New Type form field
 */
class Types extends Select
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	protected $type = 'Types';

	/**
	 * Method to get the list of article types for the field options.
	 *
	 * @return  array<int,\stdClass|\Illuminate\Support\Fluent>  The field option objects.
	 */
	protected function getOptions()
	{
		$items = Type::query()->orderBy('name', 'asc')->get();

		$types = array();
		foreach ($items as $item)
		{
			$type = new stdClass;
			$type->value = $item->id;
			$type->text  = $item->name;

			$types[] = $type;
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $types);

		return $options;
	}
}
