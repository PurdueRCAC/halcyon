<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Modules\News\Models\Fields;

use App\Modules\News\Models\Type;
use App\Halcyon\Form\Fields\Select;

/**
 * Form Field class
 */
class Types extends Select
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	protected $type = 'Types';

	/**
	 * Method to get the list of menus for the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$items = Type::query()
			->select(['id AS value', 'name AS text'])
			->orderBy('name', 'asc')
			->get();

		$menus = array();
		foreach ($items as $menu)
		{
			$menus[] = $menu;
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $menus);

		return $options;
	}
}
