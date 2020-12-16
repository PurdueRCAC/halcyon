<?php

namespace App\Modules\Menus\Models\Fields;

use App\Halcyon\Form\Fields\Select;
use App\Modules\Menus\Models\Item;
use Illuminate\Support\Facades\DB;

/**
 * Form Field class
 */
class MenuParent extends Select
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	protected $type = 'MenuParent';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		// Initialize variables.
		$options = array();
		$table = (new Item)->getTable();

		$query = DB::table($table . ' AS a')
			->select(['a.id AS value', 'a.title AS text', 'a.level'])
			//->leftJoin('menu AS b', DB::raw('a.lft > b.lft AND a.rgt < b.rgt'), DB::raw(''), DB::raw(''));
			->leftJoin($table . ' AS b', function($join)
				{
					$join->on('a.lft', '>', 'b.lft')
						->on('a.rgt', '<', 'b.rgt');
				});

		if ($menuType = $this->form->getValue('menutype'))
		{
			$query->where('a.menutype', '=', $menuType);
		}
		else
		{
			$query->where('a.menutype', '!=', '');
		}

		// Prevent parenting to children of this item.
		if ($id = $this->form->getValue('id'))
		{
			$query->leftJoin($table . ' AS p', 'p.id', DB::raw((int) $id));
			$query->whereRaw('NOT(a.lft >= p.lft AND a.rgt <= p.rgt)');
		}

		$query->where('a.published', '!=', '-2')
			->groupBy('a.id', 'a.title', 'a.level', 'a.lft', 'a.rgt', 'a.menutype', 'a.parent_id', 'a.published')
			->orderBy('a.lft', 'asc');

		$options = $query->get();

		// Pad the option text with spaces using depth level as a multiplier.
		for ($i = 0, $n = count($options); $i < $n; $i++)
		{
			$options[$i]->text = str_repeat('- ', $options[$i]->level) . $options[$i]->text;
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options->toArray());

		return $options;
	}
}
