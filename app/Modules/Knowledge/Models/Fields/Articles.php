<?php

namespace App\Modules\Knowledge\Models\Fields;

use Illuminate\Support\Str;
use App\Halcyon\Form\Fields\Select;
use App\Modules\Knowledge\Models\Page;
use stdClass;

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
	 * @inheritdoc
	 */
	protected function getOptions()
	{
		$rows = Page::tree();

		$options = array();

		$opt = new stdClass;
		$opt->value = 0;
		$opt->text  = trans('knowledge::knowledge.select page');

		$options[] = $opt;

		foreach ($rows as $row)
		{
			$opt = new stdClass;
			$opt->value = $row->id;
			$opt->text  = str_repeat('|&mdash; ', $row->level) . e(Str::limit($row->title, 70));

			$options[] = $opt;
		}

		return $options;
	}
}
