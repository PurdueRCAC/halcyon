<?php
namespace App\Modules\Users\Models\Fields;

use App\Halcyon\Access\Viewlevel;
use App\Halcyon\Form\Fields\Select;
use stdClass;

/**
 * Access level select
 */
class AccessLevel extends Select
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	protected $type = 'AccessLevel';

	/**
	 * Method to get the list of menus for the field options.
	 *
	 * @return  array<int,\Illuminate\Support\Fluent|\stdClass>  The field option objects.
	 */
	protected function getOptions()
	{
		$all = new stdClass;
		$all->value = 0;
		$all->text = '- ' . trans('global.all') . ' -';

		$options = array();
		$options[] = $all;

		$levels = Viewlevel::query()
			->orderBy('id', 'asc')
			->get();

		foreach ($levels as $level)
		{
			$option = new stdClass;
			$option->value = $level->id;
			$option->text  = $level->title;

			$options[] = $option;
		}

		return $options;
	}
}
