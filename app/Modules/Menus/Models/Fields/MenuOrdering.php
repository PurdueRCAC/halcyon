<?php

namespace App\Modules\Menus\Models\Fields;

use App\Halcyon\Form\Fields\Select;
use App\Modules\Menus\Models\Item;

/**
 * Form Field class
 */
class MenuOrdering extends Select
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	protected $type = 'MenuOrdering';

	/**
	 * Method to get the list of siblings in a menu.
	 * The method requires that parent be set.
	 *
	 * @return  array  The field option objects or false if the parent field has not been set
	 */
	protected function getOptions()
	{
		// Initialize variables.
		$options = array();

		// Get the parent
		$parent_id = $this->form->getValue('parent_id', 0);

		if (empty($parent_id))
		{
			return false;
		}

		$db = app('db');
		$query = Item::query()
			->select(['id AS value', 'title AS text'])
			->where('published', '>=', '0')
			->where('parent_id', '=', (int) $parent_id);

		if ($menuType = $this->form->getValue('menutype'))
		{
			$query->where('menutype', '=', $menuType);
		}
		else
		{
			$query->where('menutype', '!=', '');
		}

		$query->orderBy('lft', 'asc');

		$options = $query->get();

		$options = array_merge(
			array(array('value' => '-1', 'text' => trans('menus::menus.item.ordering first'))),
			$options->toArray(),
			array(array('value' => '-2', 'text' => trans('menus::menus.item.ordering last')))
		);

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}

	/**
	 * Method to get the field input markup
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		if ($this->form->getValue('id', 0) == 0)
		{
			return '<span class="readonly">' . trans('menus::menus.item.ordering hint') . '</span>';
		}

		return parent::getInput();
	}
}
