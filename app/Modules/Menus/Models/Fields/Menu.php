<?php

namespace App\Modules\Menus\Models\Fields;

use App\Halcyon\Form\Fields\Select;
use App\Modules\Menus\Models\Type;

/**
 * Supports an HTML select list of menus
 */
class Menu extends Select
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'Menu';

	/**
	 * Method to get the list of menus for the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$items = Type::query()
			->select(['menutype AS value', 'title AS text'])
			->orderBy('title', 'asc')
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

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		// Initialize variables.
		$html = array();
		$html[] = parent::getInput();

		$html[] = '<script type="application/json" id="menutypes" data-field="' . $this->id . '">';

		$items = Type::query()
			->orderBy('title', 'asc')
			->get();

		$data = array();
		foreach ($items as $menu)
		{
			$tree = array();
			$tree[] = ['value' => 1, 'text' => trans('menus::menus.root')];
			foreach ($menu->tree as $item)
			{
				$tree[] = ['value' => $item->value, 'text' => str_repeat('- ', $item->level) . $item->text];
			}
			$data[$menu->menutype] = $tree;
		}

		$html[] = json_encode($data);
		$html[] = '</script>';

		return implode("\n", $html);
	}
}
