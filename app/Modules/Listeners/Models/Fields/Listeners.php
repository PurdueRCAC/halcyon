<?php

namespace App\Modules\Listeners\Models\Fields;

use Illuminate\Support\Str;
use App\Halcyon\Form\Field;
use App\Modules\Listeners\Models\Listener;

/**
 * Form Field class for listing listeners
 */
class Listeners extends Select
{
	/**
	 * The field type.
	 *
	 * @var  string
	 */
	protected $type = 'Listeners';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return  array  An array of Html options.
	 */
	protected function getOptions()
	{
		// Initialise variables
		$folder = (string)$this->element['folder'];

		if (empty($folder))
		{
			abort(500, trans('global.error.listener directory empty'));
		}

		// Get list of listeners
		$options = Listener::query()
			->select('element AS value', 'name AS text')
			->where('type', '=', 'listener')
			->where('folder', '=', $folder)
			->where('enabled', '=', '1')
			->orderBy('ordering', 'asc')
			->orderBy('name', 'asc')
			->get();

		foreach ($options as $item)
		{
			$item->registerLanguage();

			$item->text = trans('plugin.' . $folder . '.' . $item->value . '::' . $item->value . '.widget name');
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options->toArray());

		return $options;
	}
}
