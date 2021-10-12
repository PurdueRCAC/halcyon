<?php

namespace App\Modules\Resources\Models\Fields;

use App\Halcyon\Form\Fields\Select;
use App\Modules\Resources\Models\Asset;

/**
 * Select field of available Resources
 */
class ResourceEntry extends Select
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	protected $type = 'ResourceEntry';

	/**
	 * Method to get the field options.
	 *
	 * @return  array
	 */
	protected function getOptions()
	{
		$rows = Asset::query()
			->select('id', 'name')
			->orderBy('name', 'asc')
			->get();

		foreach ($rows as $row)
		{
			$options[] = array(
				'value' => $row->id,
				'text'  => $row->name
			);
		}

		return $options;
	}
}
