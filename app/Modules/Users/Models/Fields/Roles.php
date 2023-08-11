<?php
namespace App\Modules\Users\Models\Fields;

use App\Halcyon\Access\Role;
use App\Halcyon\Form\Fields\Select;
use Illuminate\Support\Facades\DB;
use stdClass;

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
	 * @return  array<int,\Illuminate\Support\Fluent|\stdClass>  The field option objects.
	 */
	protected function getOptions()
	{
		$none = new stdClass;
		$none->value = 0;
		$none->text = trans('global.none');

		$options = array();
		$options[] = $none;

		$tbl = (new Role)->getTable();

		$roles = Role::query()
			->select(['a.id', 'a.title', 'a.parent_id', DB::raw('COUNT(DISTINCT b.id) AS level')])
			->from($tbl . ' AS a')
			->leftJoin($tbl . ' AS b', function ($join)
				{
					$join->on('a.lft', '>', 'b.lft')
						->on('a.rgt', '<', 'b.rgt');
				})
			->groupBy(['a.id', 'a.title', 'a.lft', 'a.rgt', 'a.parent_id'])
			->orderBy('a.lft', 'asc')
			->get();

		foreach ($roles as $role)
		{
			$option = new stdClass;
			$option->value = $role->id;
			$option->text  = str_repeat('|&mdash;', $role->level) . $role->title;

			$options[] = $option;
		}

		return $options;
	}
}
