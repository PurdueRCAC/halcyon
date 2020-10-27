<?php
/**
 * @package    framework
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Halcyon\Form\Fields;

/**
 * Supports a list of installed application languages
 */
class Language extends Select
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	protected $type = 'translator';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		// Initialize some field attributes.
		$client = (string) $this->element['client'];
		if ($client != 'site' && $client != 'admin')
		{
			$client = 'site';
		}

		$client_id = 0;

		if ($client == 'admin')
		{
			$client_id = 1;
		}

		// Merge any additional options
		$options = array_merge(
			parent::getOptions(),
			//app('translator')->getList($this->value, $path, true, true, $client_id)
		);

		return $options;
	}
}
