<?php

namespace App\Modules\Knowledge\Models\Fields;

use Illuminate\Support\Str;
use App\Halcyon\Form\Fields\Select;
use App\Modules\Knowledge\Models\Page;

/**
 * Supports an article picker.
 */
class Articles extends Select
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	protected $type = 'Articles';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  array  The field input markup.
	 */
	protected function getOptions()
	{
		$rows = Page::tree();

		$options = array();

		$options[] = array(
			'value' => 0,
			'text' => trans('knowledge::knowledge.select page')
		);

		foreach ($rows as $row)
		{
			$options[] = array(
				'value' => $row->id,
				'text' => str_repeat('|&mdash; ', $row->level) . e(Str::limit($row->title, 70))
			);
		}

		return $options;
	}
}
