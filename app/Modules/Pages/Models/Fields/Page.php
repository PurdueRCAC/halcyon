<?php

namespace App\Modules\Pages\Models\Fields;

use App\Halcyon\Form\Fields\Select;
use App\Halcyon\Html\Builder\Select as Dropdown;
use App\Modules\Pages\Models\Page as PageModel;

/**
 * Supports a modal article picker.
 */
class Page extends Select
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	protected $type = 'Page';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getOptions()
	{
		$options = PageModel::query()
			->select(['id AS value', 'title AS text', 'level'])
			//->where('level', '>', 0)
			->where('state', '=', 1)
			->orderBy('path', 'asc')
			->get();

		$options->each(function ($page, $key)
		{
			$page->text = str_repeat('|&mdash; ', $page->level) . e($page->text);
		});

		if ($this->element['option_blank'])
		{
			$options->prepend(Dropdown::option('', trans('- Select -'), 'value', 'text'));
		}

		return $options->toArray();
	}
}
