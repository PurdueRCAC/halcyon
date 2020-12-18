<?php

namespace App\Modules\Resources\Entities\Fields;

use App\Halcyon\Form\Fields\Select;
use App\Modules\Resources\Entities\Asset;

/**
 * Supports a modal article picker.
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
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getOptions()
	{
		$rows = Asset::query()
			->where(function($where)
			{
				$where->whereNull('datetimeremoved')
					->orWhere('datetimeremoved', '=', '0000-00-00 00:00:00');
			})
			->orderBy('name', 'asc')
			->get();

		foreach ($rows as $row)
		{
			$options[] = array(
				'value' => $row->id,
				'text' => $row->name
			);
		}

		return $options;
	}
}
