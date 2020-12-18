<?php

namespace Ballast\Menu\Type;

/**
 * Site Menu class
 */
class Site extends Base
{
	/**
	 * Loads the entire menu table into memory.
	 *
	 * @return  array
	 */
	public function load()
	{
		if (!($this->get('db') instanceof \Ballast\Database\Driver))
		{
			return;
		}

		// Initialise variables.
		$db = $this->get('db');

		$query = $db->getQuery()
			->select('m.id', 'm.menutype', 'm.title', 'm.alias', 'm.note', 'm.path AS route', 'm.link', 'm.type', 'm.level', 'm.language', 'm.browserNav', 'm.access', 'm.params', 'm.home', 'm.img', 'm.template_style_id', 'm.module_id', 'm.parent_id', 'e.element AS component')
			->from('#__menu', 'm')
			->join('#__extensions AS e', 'e.id', 'm.module_id', 'left')
			->whereEquals('m.published', 1)
			->where('m.parent_id', '>', 0)
			->whereEquals('m.client_id', 0)
			->order('m.lft', 'asc');

		// Set the query
		$db->setQuery($query->toString());

		$this->_items = $db->loadObjectList('id');

		foreach ($this->_items as &$item)
		{
			// Get parent information.
			$parent_tree = array();
			if (isset($this->_items[$item->parent_id]))
			{
				$parent_tree  = $this->_items[$item->parent_id]->tree;
			}

			// Create tree.
			$parent_tree[] = $item->id;
			$item->tree = $parent_tree;

			// Create the query array.
			$url = str_replace('index.php?', '', $item->link);
			$url = str_replace('&amp;', '&', $url);

			parse_str($url, $item->query);
		}
	}

	/**
	 * Gets menu items by attribute
	 *
	 * @param   string   $attributes  The field name
	 * @param   string   $values      The value of the field
	 * @param   boolean  $firstonly   If true, only returns the first item found
	 * @return  array
	 */
	public function getItems($attributes, $values, $firstonly = false)
	{
		$attributes = (array) $attributes;
		$values     = (array) $values;

		// Filter by language if not set
		if (($key = array_search('language', $attributes)) === false)
		{
			if ($this->get('language_filter'))
			{
				$attributes[] = 'language';
				$values[]     = array($this->get('language'), '*');
			}
		}
		elseif ($values[$key] === null)
		{
			unset($attributes[$key]);
			unset($values[$key]);
		}

		// Filter by access level if not set
		if (($key = array_search('access', $attributes)) === false)
		{
			$attributes[] = 'access';
			$values[]     = $this->get('access');
		}
		elseif ($values[$key] === null)
		{
			unset($attributes[$key]);
			unset($values[$key]);
		}

		return parent::getItems($attributes, $values, $firstonly);
	}

	/**
	 * Get menu item by id
	 *
	 * @param   string  $language  The language code.
	 * @return  mixed   The item object
	 */
	public function getDefault($language = '*')
	{
		if (array_key_exists($language, $this->_default) && $this->get('language_filter'))
		{
			return $this->_items[$this->_default[$language]];
		}

		if (array_key_exists('*', $this->_default))
		{
			return $this->_items[$this->_default['*']];
		}

		return 0;
	}
}
