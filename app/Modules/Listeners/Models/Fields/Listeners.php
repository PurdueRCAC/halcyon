<?php

namespace App\Modules\Listeners\Models\Fields;

use Illuminate\Support\Str;
use App\Halcyon\Form\Field;

/**
 * Form Field class for listing plugins
 */
class Plugins extends Select
{
	/**
	 * The field type.
	 *
	 * @var  string
	 */
	protected $type = 'Plugins';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return  array  An array of Html options.
	 */
	protected function getOptions()
	{
		// Initialise variables
		$folder = (string)$this->element['folder'];

		if (!empty($folder))
		{
			// Get list of plugins
			$db = app('db');

			$options = $db->table('extensions')
				->select('element AS value', 'name AS text')
				->where('folder', '=', $folder)
				->where('enabled', '=', '1')
				->orderBy('ordering', 'asc')
				->orderBy('name', 'asc')
				->get();

			foreach ($options as $item)
			{
				$path = app_path('Listeners/' . Str::studly($folder) . '/' . Str::studly($item->value));

				app('translator')->addNamespace('listeners.' . $folder . '.' . $item->value, $path . '/lang');

				$item->text = trans('plugin.' . $folder . '.' . $item->value . '::' . $item->value . '.widget name');
			}
		}
		else
		{
			abort(500, trans('global.error.listener directory empty'));
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options->toArray());

		return $options;
	}
}
