<?php

namespace App\Halcyon\Form\Fields;

use App\Halcyon\Html\Builder\Select as Dropdown;

/**
 * Supports an HTML select list of files
 */
class Filelist extends Select
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'Filelist';

	/**
	 * Method to get the list of files for the field options.
	 * Specify the target directory with a directory attribute
	 * Attributes allow an exclude mask and stripping of extensions from file name.
	 * Default attribute may optionally be set to null (no file) or -1 (use a default).
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
		$stripExt = (string) $this->element['stripext'];
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
			$options[] = Dropdown::option('-1', trans('global.option.do not use'));
		}
		if (!$hideDefault)
		{
			$options[] = Dropdown::option('', trans('global.option.use default'));
		}

		// Get a list of files in the search path with the given filter.
		$files = app('filesystem')->files($path);

		// Build the options list from the list of files.
		if (is_array($files))
		{
			foreach ($files as $file)
			{

				// Check to see if the file is in the exclude mask.
				if ($exclude)
				{
					if (preg_match(chr(1) . $exclude . chr(1), $file))
					{
						continue;
					}
				}

				// If the extension is to be stripped, do it.
				if ($stripExt)
				{
					$file = app('filesystem')->getDefaultDriver()->name($file);
				}

				$options[] = Dropdown::option($file, $file);
			}
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
