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
	 * Defines the valid request variables for the reverse lookup.
	 *
	 * @var  array<int,string>
	 */
	protected static $_filter = array('option', 'view', 'layout');

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @param   int  $parentId  The menu ID.
	 * @return  object
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
			$result->set($action->name, auth()->user()->can($action->name . ' ' . $assetName));
		}

		return $result;
	}

	/**
	 * Gets a standard form of a link for lookups.
	 *
	 * @param   mixed  $request  A link string or array of request variables.
	 * @return  false|string  A link in standard option-view-layout form, or false if the supplied response is invalid.
	 */
	public static function getLinkKey($request)
	{
		if (empty($request))
		{
			return false;
		}

		// Check if the link is in the form of index.php?...
		if (is_string($request))
		{
			$args = array();
			if (strpos($request, 'index.php') === 0)
			{
				parse_str(parse_url(htmlspecialchars_decode($request), PHP_URL_QUERY), $args);
			}
			else
			{
				parse_str($request, $args);
			}
			$request = $args;
		}

		// Only take the option, view and layout parts.
		foreach ($request as $name => $value)
		{
			if ((!in_array($name, self::$_filter)) && (!($name == 'task' && !array_key_exists('view', $request))))
			{
				// Remove the variables we want to ignore.
				unset($request[$name]);
			}
		}

		ksort($request);

		return 'index.php?' . http_build_query($request, '', '&');
	}

	/**
	 * Get the menu list for create a menu module
	 *
	 * @return  array<int,string>  The menu array list
	 */
	public static function getMenuTypes()
	{
		$rows = Type::all()->pluck('menutype')->toArray();

		return $rows;
	}

	/**
	 * Get a list of menu links for one or all menus.
	 *
	 * @param   string  $menuType   An option menu to filter the list on, otherwise all menu links are returned as a grouped array.
	 * @param   int     $parentId   An optional parent ID to pivot results around.
	 * @param   int     $mode       An optional mode. If parent ID is set and mode=2, the parent and children are excluded from the list.
	 * @param   array   $published  An optional array of states
	 * @param   array   $languages
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
				$query->leftJoin($table . ' AS p', 'p.id', (int) $parentId);
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

	/**
	 * Get associations
	 *
	 * @param   int  $pk
	 * @return  array<string,int>
	 */
	public static function getAssociations($pk)
	{
		$associations = array();

		$db = app('db');
		$table = (new Type)->getTable();

		$query = $db->table($table . ' AS m')
			->join('associations as a', 'a.id', 'm.id')
			->where('a.context', '=', 'com_menus.item')
			->join('associations as a2', 'a.key', 'a2.key')
			->join('menu as m2', 'a2.id', 'm2.id')
			->where('m.id', '=', (int)$pk)
			->select(['m2.language', 'm2.id']);

		$menuitems = $query->get();

		foreach ($menuitems as $item)
		{
			$associations[$item->language] = $item->id;
		}

		return $associations;
	}
}
