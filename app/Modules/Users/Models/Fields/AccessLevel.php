<?php
namespace App\Modules\Users\Models\Fields;

use App\Halcyon\Access\Viewlevel;
use App\Halcyon\Form\Fields\Select;

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
	 * @return  array<int,Role>  The field option objects.
	 */
	protected function getOptions()
	{
		$all = new Viewlevel;
		$all->value = 0;
		$all->text = '- ' . trans('global.all') . ' -';

		$options = Viewlevel::query()
			->orderBy('id', 'asc')
			->get();

		$options->each(function($item)
		{
			$item->value = $item->id;
			$item->text = $item->title;
		});

		$options->prepend($all);

		return $options->toArray();
	}
}
