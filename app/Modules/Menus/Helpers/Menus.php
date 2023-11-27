<?php

namespace App\Modules\Menus\Helpers;

use Illuminate\Support\Fluent;
use App\Modules\Menus\Models\Type;
use App\Modules\Menus\Models\Item;
use App\Halcyon\Access\Gate;

/**
 * Menus helper.
 */
class Menus
{
	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @param   int  $parentId  The menu ID.
	 * @return  Fluent
	 */
	public static function getActions($parentId = 0)
	{
		$result = new Fluent;

		if (empty($parentId))
		{
			$assetName = 'menus';
		}
		else
		{
			$assetName = 'menus.item.' . (int) $parentId;
		}

		$actions = Gate::getActionsFromFile(dirname(__DIR__) . '/Config/access.xml');

		foreach ($actions as $action)
		{
			$result->{$action->name} = auth()->user()->can($action->name . ' ' . $assetName);
		}

		return $result;
	}

	/**
	 * Get a list of menu links for one or all menus.
	 *
	 * @param   string  $menuType   An option menu to filter the list on, otherwise all menu links are returned as a grouped array.
	 * @param   int     $parentId   An optional parent ID to pivot results around.
	 * @param   int     $mode       An optional mode. If parent ID is set and mode=2, the parent and children are excluded from the list.
	 * @param   array<int,int>   $published  An optional array of states
	 * @param   array<int,string>   $languages
	 * @return  mixed
	 */
	public static function getMenuLinks($menuType = null, $parentId = 0, $mode = 0, $published=array(), $languages=array())
	{
		$db = app('db');
		$table = (new Item)->getTable();

		$query = $db->table($table . ' AS a')
			->select([
				'a.id AS value',
				'a.title AS text',
				'a.level',
				'a.menutype',
				'a.type',
				'a.checked_out'
			])
			->whereNull('a.deleted_at');
		$query->leftJoin($table . ' AS b', function($join)
		{
			$join->on('a.lft', '>', 'b.lft')
				->on('a.rgt', '<', 'b.rgt');
		});

		// Filter by the type
		if ($menuType)
		{
			$query->where(function($q) use ($menuType)
			{
				$q->where('a.menutype', '=', $menuType)
					->orWhere('a.parent_id', '=', 0);
			});
		}

		if ($parentId)
		{
			if ($mode == 2)
			{
				// Prevent the parent and children from showing.
				$query->leftJoin($table . ' AS p', 'p.id', "$parentId");
				$query->where(function($q)
				{
					$q->where('a.lft', '<=', 'p.lft')
						->orWhere('a.rgt', '>=', 'p.rgt');
				});
			}
		}

		if (!empty($languages))
		{
			$query->whereIn('a.language', $languages);
		}

		if (!empty($published))
		{
			$query->whereIn('a.published', $published);
		}

		$query->where('a.published', '!=', '-2');
		$query->groupBy('a.id', 'a.title', 'a.level', 'a.menutype', 'a.type', 'a.checked_out', 'a.lft');
		$query->orderBy('a.lft', 'ASC');

		// Get the options.
		$links = $query->get();

		// Pad the option text with spaces using depth level as a multiplier.
		foreach ($links as &$link)
		{
			$link->text = str_repeat('- ', $link->level) . $link->text;
		}

		if (empty($menuType))
		{
			// If the menutype is empty, group the items by menutype.
			$menuTypes = Type::query()
				->where('menutype', '<>', '')
				->orderBy('title', 'asc')
				->orderBy('menutype', 'asc')
				->get();

			// Create a reverse lookup and aggregate the links.
			$rlu = array();
			foreach ($menuTypes as &$type)
			{
				$rlu[$type->menutype] = &$type;
				$type->links = array();
			}

			// Loop through the list of menu links.
			foreach ($links as &$link)
			{
				if (isset($rlu[$link->menutype]))
				{
					//$rlu[$link->menutype]->links[] = &$link;
					$links = $rlu[$link->menutype]->links;
					$links[] = &$link;
					$rlu[$link->menutype]->links = $links;

					// Cleanup garbage.
					unset($link->menutype);
				}
			}

			return $menuTypes;
		}

		return $links;
	}
}
