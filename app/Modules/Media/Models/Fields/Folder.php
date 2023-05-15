<?php

namespace App\Modules\Media\Models\Fields;

use App\Modules\Media\Helpers\MediaHelper;
use App\Halcyon\Form\Fields\Select;
use stdClass;

/**
 * Folder form field
 */
class Folder extends Select
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	protected $type = 'Folder';

	/**
	 * Method to get the list of article types for the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$base = storage_path('app/public');
		$folders = MediaHelper::getTree($base);

		$types = array();
		foreach ($folders as $file)
		{
			$item = new stdClass;
			$item->value = $file['relname'];
			$item->text  = $item->value;

			$types[] = $item;
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $types);

		return $options;
	}
}
