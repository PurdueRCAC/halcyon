<?php

namespace App\Halcyon\Form\Fields;

use App\Halcyon\Html\Builder\Select as Dropdown;

/**
 * Supports an HTML select list of folder
 */
class Folderlist extends Select
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'Folderlist';

	/**
	 * Method to get the field options.
	 *
	 * @return  array<int,\stdClass>  The field option objects.
	 */
	protected function getOptions()
	{
		// Initialize variables.
		$options = array();

		// Initialize some field attributes.
		//$filter = (string) $this->element['filter'];
		$exclude = (string) $this->element['exclude'];
		$hideNone = (string) $this->element['hide_none'];
		$hideDefault = (string) $this->element['hide_default'];

		// Get the path in which to search for file options.
		$path = (string) $this->element['directory'];
		if (!is_dir($path))
		{
			$path = storage_path($path);
		}

		// Prepend some default options based on field attributes.
		if (!$hideNone)
		{
			$options[] = Dropdown::option( '-1', trans('global.option.do not use'));
		}
		if (!$hideDefault)
		{
			$options[] = Dropdown::option('', trans('global.option.use default'));
		}

		// Get a list of folders in the search path with the given filter.
		$folders = app('filesystem')->directories($path);

		// Build the options list from the list of folders.
		if (is_array($folders))
		{
			foreach ($folders as $folder)
			{

				// Check to see if the file is in the exclude mask.
				if ($exclude)
				{
					if (preg_match(chr(1) . $exclude . chr(1), $folder))
					{
						continue;
					}
				}

				$options[] = Dropdown::option($folder, $folder);
			}
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
