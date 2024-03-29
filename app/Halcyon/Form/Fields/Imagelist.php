<?php

namespace App\Halcyon\Form\Fields;

/**
 * Supports an HTML select list of image
 */
class Imagelist extends Filelist
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'Imagelist';

	/**
	 * Method to get the list of images field options.
	 * Use the filter attribute to specify allowable file extensions.
	 *
	 * @return  array<int,\stdClass>  The field option objects.
	 */
	protected function getOptions()
	{
		// Define the image file type filter.
		$filter = '\.png$|\.gif$|\.jpg$|\.bmp$|\.ico$|\.jpeg$|\.psd$|\.eps$';

		// Set the form field element attribute for file type filter.
		$this->element->addAttribute('filter', $filter);

		// Get the field options.
		return parent::getOptions();
	}
}
