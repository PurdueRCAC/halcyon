<?php
namespace App\Modules\Users\Models\Fields;

use App\Halcyon\Access\Role;
use App\Halcyon\Form\Fields\Select;
use Illuminate\Support\Facades\DB;

/**
 * Form Field class
 */
class Roles extends Select
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	protected $type = 'Roles';

	/**
	 * Method to get the list of menus for the field options.
	 *
	 * @return  array<int,Role>  The field option objects.
	 */
	protected function getOptions()
	{
		$none = new Role;
		$none->value = 0;
		$none->text = trans('global.none');

		$options = Role::query()
			->select(['a.id', 'a.title', 'a.parent_id', DB::raw('COUNT(DISTINCT b.id) AS level')])
			->from($none->getTable() . ' AS a')
			->leftJoin($none->getTable() . ' AS b', function ($join)
				{
					$join->on('a.lft', '>', 'b.lft')
						->on('a.rgt', '<', 'b.rgt');
				})
			->groupBy(['a.id', 'a.title', 'a.lft', 'a.rgt', 'a.parent_id'])
			->orderBy('a.lft', 'asc')
			->get();

		$options->each(function($item)
		{
			$item->value = $item->id;
			$item->text = str_repeat('|&mdash;', $item->level) . $item->title;
		});

		$options->prepend($none);

		return $options->toArray();
	}
}
