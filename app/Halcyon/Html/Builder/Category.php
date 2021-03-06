<?php

namespace App\Halcyon\Html\Builder;

/**
 * Utility class for categories
 */
class Category
{
	/**
	 * Cached array of the category items.
	 *
	 * @var    array
	 */
	protected static $items = array();

	/**
	 * Returns an array of categories for the given extension.
	 *
	 * @param   string  $extension  The extension option e.g. com_something.
	 * @param   array   $config     An array of configuration options. By default, only
	 *                              published and unpublished categories are returned.
	 * @return  array
	 */
	public static function options($extension, $config = array('filter.published' => array(0, 1)))
	{
		$hash = md5($extension . '.' . serialize($config));

		if (!isset(self::$items[$hash]))
		{
			$config = (array) $config;
			$db = app('db');

			$query = $db->table('categories AS a')
				->select(['a.id', 'a.title', 'a.level'])
				->where('a.parent_id', '>', '0');

			// Filter on extension.
			$query->where('extension', '=', $extension);

			// Filter on the published state
			if (isset($config['filter.published']))
			{
				if (is_numeric($config['filter.published']))
				{
					$query->where('a.published', '=', (int) $config['filter.published']);
				}
				elseif (is_array($config['filter.published']))
				{
					Arr::toInteger($config['filter.published']);
					$query->whereIn('a.published', $config['filter.published']);
				}
			}

			$query->orderBy('a.lft', 'asc');

			$items = $query->get();

			// Assemble the list options.
			self::$items[$hash] = array();

			foreach ($items as &$item)
			{
				$repeat = ($item->level - 1 >= 0) ? $item->level - 1 : 0;
				$item->title = str_repeat('- ', $repeat) . $item->title;
				self::$items[$hash][] = Select::option($item->id, $item->title);
			}
		}

		return self::$items[$hash];
	}

	/**
	 * Returns an array of categories for the given extension.
	 *
	 * @param   string  $extension  The extension option.
	 * @param   array   $config     An array of configuration options. By default, only published and unpublished categories are returned.
	 * @return  array   Categories for the extension
	 */
	public static function categories($extension, $config = array('filter.published' => array(0, 1)))
	{
		$hash = md5($extension . '.' . serialize($config));

		if (!isset(self::$items[$hash]))
		{
			$config = (array) $config;
			$db = app('db');

			$query = $db->table('categories AS a')
				->select(['a.id', 'a.title', 'a.level', 'a.parent_id', 'a.title', 'a.level', 'a.parent_id'])
				->where('a.parent_id', '>', '0');

			// Filter on extension.
			$query->where('extension', '=', $extension);

			// Filter on the published state
			if (isset($config['filter.published']))
			{
				if (is_numeric($config['filter.published']))
				{
					$query->where('a.published', '=', (int) $config['filter.published']);
				}
				elseif (is_array($config['filter.published']))
				{
					$config['filter.published'] = array_map('intval', $config['filter.published']);
					$query->whereIn('a.published', $config['filter.published']);
				}
			}

			$query->orderBy('a.lft', 'asc');

			$items = $query->get();

			// Assemble the list options.
			self::$items[$hash] = array();

			foreach ($items as &$item)
			{
				$repeat = ($item->level - 1 >= 0) ? $item->level - 1 : 0;
				$item->title = str_repeat('- ', $repeat) . $item->title;
				self::$items[$hash][] = Select::option($item->id, $item->title);
			}
			// Special "Add to root" option:
			self::$items[$hash][] = Select::option('1', trans('global.add to root'));
		}

		return self::$items[$hash];
	}
}
