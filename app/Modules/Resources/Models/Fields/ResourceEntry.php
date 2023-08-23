<?php

namespace App\Modules\Resources\Models\Fields;

use App\Halcyon\Form\Fields\Select;
use App\Modules\Resources\Models\Asset;
use stdClass;

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
	 * @return  array<int,\Illuminate\Support\Fluent|stdClass>
	 */
	protected function getOptions()
	{
		$rows = Asset::query()
			->select('id', 'name')
			->orderBy('name', 'asc')
			->get();

		$options = array();

		foreach ($rows as $row)
		{
			$item = new stdClass;
			$item->value = $row->id;
			$item->text = $row->name;

			$options[] = $item;
		}

		return $options;
	}
}
