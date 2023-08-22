<?php

namespace App\Halcyon\Html\Builder;

use Illuminate\Support\Fluent;
use Illuminate\Support\Collection;

/**
 * Utility class working with content language select lists
 */
class Contentlanguage
{
	/**
	 * Cached array of the content language items.
	 *
	 * @var  Collection|null
	 */
	protected static $items = null;

	/**
	 * Get a list of the available content language items.
	 *
	 * @param   bool  $all        True to include All (*)
	 * @param   bool  $translate  True to translate All
	 * @return  array<int,object>
	 */
	public static function existing($all = false, $translate = false)
	{
		if (empty(self::$items))
		{
			// Get the database object and a new query object.
			$db = app('db');

			// Build the query.
			self::$items = $db->table('languages AS a')
				->select(['a.lang_code AS value', 'a.title AS text', 'a.title_native'])
				->where('a.published', '>=', '0')
				->orderBy('a.title', 'asc')
				->get();

			// Set the query and load the options.
			if ($all)
			{
				$first = new Fluent(array('value' => '*', 'text' => $translate ? trans('global.all') : trans('global.all language')));

				self::$items->prepend($first);
			}
		}

		return self::$items->toArray();
	}
}
