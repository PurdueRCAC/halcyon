<?php

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
		/*$items = Type::query()
			->select(['id AS value', 'name AS text'])
			->orderBy('name', 'asc')
			->get();*/

		$items = Type::tree();

		$types = array();
		foreach ($items as $item)
		{
			$item->value = $item->id;
			$item->text = $item->name;
			if ($item->level > 0)
			{
				$item->text = str_repeat('|&mdash;', $item->level) . ' ' . $item->name;
			}
			$types[] = $item;
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $types);

		return $options;
	}
}
