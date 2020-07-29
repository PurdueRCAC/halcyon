<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Modules\Menus\Helpers;

use App\Modules\Menus\Models\Type;
use App\Modules\Menus\Models\Item;
use App\Halcyon\Access\Gate;
use App\Halcyon\Html\Builder\Select;
use App\Halcyon\Html\Builder\Grid;

/**
 * Utility class working with menu select lists
 */
class Html
{
	/**
	 * Cached array of the menus.
	 *
	 * @var  array
	 */
	protected static $menus = null;

	/**
	 * Cached array of the menus items.
	 *
	 * @var  array
	 */
	protected static $items = null;

	/**
	 * Get a list of the available menus.
	 *
	 * @return  string
	 */
	public static function menus()
	{
		if (empty(self::$menus))
		{
			$items = Type::query()
				->orderBy('title', 'asc')
				->get();

			$menus = array();
			foreach ($items as $item)
			{
				$menus[] = array(
					'value' => $item->menutype,
					'text'  => $item->title
				);
			}
			self::$menus = $menus;
		}

		return self::$menus;
	}

	/**
	 * Returns an array of menu items grouped by menu.
	 *
	 * @param   array  $config  An array of configuration options.
	 * @return  array
	 */
	public static function menuitems($config = array())
	{
		if (empty(self::$items))
		{
			$menus = Type::query()
				->select(['menutype AS value', 'title AS text'])
				->orderBy('title', 'asc')
				->get();

			$query = Item::query()
				->select(['id AS value', 'title AS text', 'level', 'menutype'])
				->where('type', '<>', 'url')
				->where('parent_id', '>', 0)
				->where('client_id', '=', 0);

			// Filter on the published state
			if (isset($config['published']))
			{
				if (is_numeric($config['published']))
				{
					$query->where('published', '=', (int) $config['published']);
				}
				elseif ($config['published'] === '')
				{
					$query->whereIn('published', array(0, 1));
				}
			}

			$query->orderBy('lft', 'asc');

			$items = $query->get();

			// Collate menu items based on menutype
			$lookup = array();
			foreach ($items as $item)
			{
				if (!isset($lookup[$item->menutype]))
				{
					$lookup[$item->menutype] = array();
				}
				$lookup[$item->menutype][] = &$item;

				$item->text = str_repeat('- ', $item->level) . $item->text;
			}
			self::$items = array();

			foreach ($menus as $menu)
			{
				// Start group:
				self::$items[] = Select::optgroup($menu->text);

				// Special "Add to this Menu" option:
				self::$items[] = Select::option($menu->value . '.1', trans('global.ADD_TO_THIS_MENU'));

				// Menu items:
				if (isset($lookup[$menu->value]))
				{
					foreach ($lookup[$menu->value] as &$item)
					{
						self::$items[] = Select::option($menu->value . '.' . $item->value, $item->text);
					}
				}

				// Finish group:
				self::$items[] = Select::optgroup($menu->text);
			}
		}

		return self::$items;
	}

	/**
	 * Displays an HTML select list of menu items.
	 *
	 * @param   string  $name      The name of the control.
	 * @param   string  $selected  The value of the selected option.
	 * @param   string  $attribs   Attributes for the control.
	 * @param   array   $config    An array of options for the control.
	 * @return  string
	 */
	public static function menuitemlist($name, $selected = null, $attribs = null, $config = array())
	{
		static $count;

		$options = self::menuitems($config);

		return Select::genericlist(
			$options,
			$name,
			array(
				'id' => isset($config['id']) ? $config['id'] : 'assetgroups_' . ++$count,
				'list.attr' => (is_null($attribs) ? 'class="inputbox" size="1"' : $attribs),
				'list.select' => (int) $selected,
				'list.translate' => false
			)
		);
	}

	/**
	 * Build the select list for Menu Ordering
	 *
	 * @param   object   &$row  The row object
	 * @param   integer  $id    The id for the row. Must exist to enable menu ordering
	 * @return  string
	 */
	public static function ordering(&$row, $id)
	{
		if ($id)
		{
			$query = Item::query()
				->select(['ordering AS value', 'title AS text'])
				->where('menutype', '=', $row->menutype)
				->where('parent_id', '=', (int) $row->parent_id)
				->where('published', '!=', '-2')
				->orderBy('ordering', 'asc');

			$order = Select::ordering($query);

			$ordering = Select::genericlist(
				$order,
				'ordering',
				array(
					'list.attr' => 'class="inputbox" size="1"',
					'list.select' => intval($row->ordering)
				)
			);
		}
		else
		{
			$ordering = '<input type="hidden" name="ordering" value="' . $row->ordering . '" />' . trans('global.NEWITEMSLAST_DESC');
		}

		return $ordering;
	}

	/**
	 * Build the multiple select list for Menu Links/Pages
	 *
	 * @param   boolean  $all         True if all can be selected
	 * @param   boolean  $unassigned  True if unassigned can be selected
	 * @return  string
	 */
	public static function linkoptions($all = false, $unassigned = false)
	{
		$mitems = Item::query()
			->select(['id', 'parent_id', 'title', 'menutype'])
			->where('published', '=', 1)
			->orderBy('menutype', 'asc')
			->orderBy('parent_id', 'asc')
			->orderBy('ordering', 'asc')
			->get();

		if (!$mitems)
		{
			$mitems = array();
		}

		$mitems_temp = $mitems;

		// Establish the hierarchy of the menu
		$children = array();
		// First pass - collect children
		foreach ($mitems as $v)
		{
			$pt = $v->parent_id;
			$list = @$children[$pt] ? $children[$pt] : array();
			array_push($list, $v);
			$children[$pt] = $list;
		}

		// Second pass - get an indent list of the items
		$list = self::treeRecurse(intval($mitems->first()->parent_id), '', array(), $children, 9999, 0, 0);

		// Code that adds menu name to Display of Page(s)
		$mitems = array();
		if ($all | $unassigned)
		{
			$mitems[] = Select::option('<OPTGROUP>', trans('JOPTION_MENUS'));

			if ($all)
			{
				$mitems[] = Select::option(0, trans('JALL'));
			}
			if ($unassigned)
			{
				$mitems[] = Select::option(-1, trans('JOPTION_UNASSIGNED'));
			}

			$mitems[] = Select::option('</OPTGROUP>');
		}

		$lastMenuType = null;
		$tmpMenuType = null;
		foreach ($list as $list_a)
		{
			if ($list_a->menutype != $lastMenuType)
			{
				if ($tmpMenuType)
				{
					$mitems[] = Html::select('option', '</OPTGROUP>');
				}
				$mitems[] = Html::select('option', '<OPTGROUP>', $list_a->menutype);
				$lastMenuType = $list_a->menutype;
				$tmpMenuType = $list_a->menutype;
			}

			$mitems[] = Html::select('option', $list_a->id, $list_a->title);
		}
		if ($lastMenuType !== null)
		{
			$mitems[] = Html::select('option', '</OPTGROUP>');
		}

		return $mitems;
	}

	/**
	 * Build the list representing the menu tree
	 *
	 * @param   integer  $id         Id of the menu item
	 * @param   string   $indent     The indentation string
	 * @param   array    $list       The list to process
	 * @param   array    &$children  The children of the current item
	 * @param   integer  $maxlevel   The maximum number of levels in the tree
	 * @param   integer  $level      The starting level
	 * @param   string   $type       Type of link: component, URL, alias, separator
	 * @return  array
	 */
	public static function treerecurse($id, $indent, $list, &$children, $maxlevel = 9999, $level = 0, $type = 1)
	{
		if (@$children[$id] && $level <= $maxlevel)
		{
			foreach ($children[$id] as $v)
			{
				$id = $v->id;

				if ($type)
				{
					$pre = '<sup>|_</sup>&#160;';
					$spacer = '.&#160;&#160;&#160;&#160;&#160;&#160;';
				}
				else
				{
					$pre = '- ';
					$spacer = '&#160;&#160;';
				}

				if ($v->parent_id == 0)
				{
					$txt = $v->title;
				}
				else
				{
					$txt = $pre . $v->title;
				}

				$pt = $v->parent_id;

				$list[$id] = $v;
				$list[$id]->treename = "$indent$txt";
				$list[$id]->children = count(@$children[$id]);

				$list = self::treeRecurse($id, $indent . $spacer, $list, $children, $maxlevel, $level + 1, $type);
			}
		}
		return $list;
	}

	/**
	 * Returns a published state on a grid
	 *
	 * @param   integer  $value     The state value.
	 * @param   integer  $i         The row index
	 * @param   boolean  $enabled   An optional setting for access control on the action.
	 * @param   string   $checkbox  An optional prefix for checkboxes.
	 * @return  string   The Html code
	 */
	public static function state($value, $i, $enabled = true, $checkbox = 'cb')
	{
		$states = array(
			7 => array(
				'unpublish',
				'global.published',
				'menus::menus.HTML_UNPUBLISH_SEPARATOR',
				'',
				false,
				'publish',
				'publish'
			),
			6 => array(
				'publish',
				'global.unpublished',
				'menus::menus.HTML_PUBLISH_SEPARATOR',
				'',
				false,
				'unpublish',
				'unpublish'
			),
			5 => array(
				'unpublish',
				'global.published',
				'menus::menus.HTML_UNPUBLISH_ALIAS',
				'',
				false,
				'publish',
				'publish'
			),
			4 => array(
				'publish',
				'global.unpublished',
				'menus::menus.HTML_PUBLISH_ALIAS',
				'',
				false,
				'unpublish',
				'unpublish'
			),
			3 => array(
				'unpublish',
				'global.published',
				'menus::menus.HTML_UNPUBLISH_URL',
				'',
				false,
				'publish',
				'publish'
			),
			2 => array(
				'publish',
				'',
				'menus::menus.HTML_PUBLISH_URL',
				'',
				false,
				'unpublish',
				'unpublish'
			),
			1 => array(
				'unpublish',

				'global.published',
				'menus::menus.HTML_UNPUBLISH_ENABLED',
				'menus::menus.EXTENSION_PUBLISHED_ENABLED',
				true,
				'publish',
				'publish'
			),
			0 => array(
				'publish',
				'global.unpublished',
				'menus::menus.HTML_PUBLISH_ENABLED',
				'menus::menus.EXTENSION_UNPUBLISHED_ENABLED',
				true,
				'unpublish',
				'unpublish'
			),
			-1 => array(
				'unpublish',
				'menus::menus.EXTENSION_PUBLISHED_DISABLED',
				'menus::menus.HTML_UNPUBLISH_DISABLED',
				'menus::menus.EXTENSION_PUBLISHED_DISABLED',
				true,
				'warning',
				'warning'
			),
			-2 => array(
				'publish',
				'menus::menus.EXTENSION_UNPUBLISHED_DISABLED',
				'menus::menus.HTML_PUBLISH_DISABLED',
				'menus::menus.EXTENSION_UNPUBLISHED_DISABLED',
				true,
				'unpublish',
				'unpublish'
			),
		);

		return Grid::state($states, $value, $i, 'items.', $enabled, true, $checkbox);
	}
}
