<?php

namespace App\Halcyon\Html\Builder;

use App\Halcyon\Error\Exception\Exception;
use App\Halcyon\Access\Access as Gate;
use App\Halcyon\Access\Role;
use App\Halcyon\Access\Viewlevel;
use Illuminate\Support\Facades\DB;

/**
 * Extended Utility class for all HTML drawing classes.
 */
class Access
{
	/**
	 * A cached array of the asset groups
	 *
	 * @var  array
	 */
	protected static $asset_groups = null;

	/**
	 * Displays a list of the available access view levels
	 *
	 * @param   string  $name      The form field name.
	 * @param   string  $selected  The name of the selected section.
	 * @param   string  $attribs   Additional attributes to add to the select field.
	 * @param   mixed   $params    True to add "All Sections" option or and array of options
	 * @param   string  $id        The form field id
	 * @return  string  The required HTML for the SELECT tag.
	 */
	public static function level($name, $selected, $attribs = '', $params = true, $id = false)
	{
		/*$db = App::get('db');

		$query = $db->getQuery()
			->select('a.id', 'value')
			->select('a.title', 'text')
			->from('#__viewlevels', 'a')
			->group('a.id')
			->group('a.title')
			->group('a.ordering')
			->order('a.ordering', 'asc')
			->order('title', 'asc');

		// Get the options.
		$db->setQuery($query->toString());
		$options = $db->loadObjectList();

		// Check for a database error.
		if ($db->getErrorNum())
		{
			throw new Exception($db->getErrorMsg(), 500, E_WARNING);
			return null;
		}*/
		$options = Viewlevel::query()
			->select(['id AS value', 'title AS text'])
			->groupBy(['id', 'title', 'ordering'])
			->orderBy('ordering', 'asc')
			->get();

		// If params is an array, push these options to the array
		if (is_array($params))
		{
			$options = array_merge($params, $options->toArray());
		}
		// If all levels is allowed, push it into the array.
		elseif ($params)
		{
			array_unshift($options, Select::option('', trans('JOPTION_ACCESS_SHOW_ALL_LEVELS')));
		}

		if (!$attribs)
		{
			$attribs = 'class="form-control"';
		}

		return Select::genericlist(
			$options,
			$name,
			array(
				'list.attr' => $attribs,
				'list.select' => $selected,
				'id' => $id
			)
		);
	}

	/**
	 * Displays a list of the available user groups.
	 *
	 * @param   string   $name      The form field name.
	 * @param   string   $selected  The name of the selected section.
	 * @param   string   $attribs   Additional attributes to add to the select field.
	 * @param   boolean  $allowAll  True to add "All Groups" option.
	 * @return  string   The required HTML for the SELECT tag.
	 */
	public static function usergroup($name, $selected, $attribs = '', $allowAll = true)
	{
		/*$db = App::get('db');
		$query = $db->getQuery()
			->select('a.id', 'value')
			->select('a.title', 'text')
			->select('COUNT(DISTINCT b.id)', 'level')
			->from('#__usergroups', 'a')
			->joinRaw('#__usergroups AS b', 'a.lft > b.lft AND a.rgt < b.rgt', 'left')
			->group('a.id')
			->group('a.title')
			->group('a.lft')
			->group('a.rgt')
			->order('a.lft', 'asc');
		$db->setQuery($query->toString());
		$options = $db->loadObjectList();

		// Check for a database error.
		if ($db->getErrorNum())
		{
			throw new Exception($db->getErrorMsg(), 500, E_WARNING);
			return null;
		}*/
		$ug = new Role;

		$options = Role::query()
			->select(['a.id AS value', 'a.title AS text', DB::raw('COUNT(DISTINCT b.id) AS level')])
			->from($ug->getTable() . ' AS a')
			->leftJoin($ug->getTable() . ' AS b', function($join)
				{
					$join->on('a.lft', '>', 'b.lft')
						->on('a.rgt', '<', 'b.rgt');
				})
			->groupBy(['a.id', 'a.title', 'a.lft', 'a.rgt'])
			->orderBy('a.lft', 'asc')
			->get();

		/*foreach ($options as $i => $option)
		{
			$options[$i]->text = str_repeat('- ', $options[$i]->level) . $options[$i]->text;
		}*/

		$options->transform(function ($item, $key)
		{
			$item->text = str_repeat('- ', $item->level) . $item->text;
			return $item;
		});

		// If all usergroups is allowed, push it into the array.
		if ($allowAll)
		{
			//array_unshift($options, Select::option('', trans('JOPTION_ACCESS_SHOW_ALL_GROUPS')));
			$options->prepend(Select::option('', trans('JOPTION_ACCESS_SHOW_ALL_GROUPS')));
		}

		return Select::genericlist($options, $name, array(
			'list.attr'   => $attribs,
			'list.select' => $selected
		));
	}

	/**
	 * Returns a UL list of user groups with check boxes
	 *
	 * @param   string   $name             The name of the checkbox controls array
	 * @param   array    $selected         An array of the checked boxes
	 * @param   boolean  $checkSuperAdmin  If false only super admins can add to super admin groups
	 * @return  string
	 */
	public static function roles($name, $selected, $checkSuperAdmin = false)
	{
		static $count;

		$count++;

		$isSuperAdmin = auth()->user()->can('admin');

		$ug = new Role;

		$options = Role::query()
			->select(['a.id', 'a.title', 'a.parent_id', DB::raw('COUNT(DISTINCT b.id) AS level')])
			->from($ug->getTable() . ' AS a')
			->leftJoin($ug->getTable() . ' AS b', function($join)
				{
					$join->on('a.lft', '>', 'b.lft')
						->on('a.rgt', '<', 'b.rgt');
				})
			->groupBy(['a.id', 'a.title', 'a.lft', 'a.rgt', 'a.parent_id'])
			->orderBy('a.lft', 'asc')
			->get();

		$html = array();

		$html[] = '<ul class="checklist usergroups">';

		foreach ($options as $i => $item)
		{
			// If checkSuperAdmin is true, only add item if the user is superadmin or the group is not super admin
			if ((!$checkSuperAdmin) || $isSuperAdmin || (!Gate::checkRole($item->id, 'admin')))
			{
				// Setup  the variable attributes.
				$eid = $count . 'role_' . $item->id;
				// Don't call in_array unless something is selected
				$checked = '';
				if ($selected)
				{
					$checked = in_array($item->id, $selected) ? ' checked="checked"' : '';
				}
				$rel = ($item->parent_id > 0) ? ' rel="' . $count . 'role_' . $item->parent_id . '"' : '';

				// Build the HTML for the item.
				$html[] = '	<li>';
				$html[] = '		<div class="form-check">';
				$html[] = '		<input type="checkbox" class="form-check-input" name="' . $name . '[]" value="' . $item->id . '" id="' . $eid . '"' . $checked . $rel . ' />';
				$html[] = '		<label for="' . $eid . '" class="form-check-label">';
				$html[] = '		' . str_repeat('<span class="gi">|&mdash;</span>', $item->level) . $item->title;
				$html[] = '		</label>';
				$html[] = '		</div>';
				$html[] = '	</li>';
			}
		}
		$html[] = '</ul>';

		return implode("\n", $html);
	}

	/**
	 * Returns a UL list of actions with check boxes
	 *
	 * @param   string  $name       The name of the checkbox controls array
	 * @param   array   $selected   An array of the checked boxes
	 * @param   string  $component  The component the permissions apply to
	 * @param   string  $section    The section (within a component) the permissions apply to
	 * @return  string
	 */
	public static function actions($name, $selected, $component, $section = 'global')
	{
		static $count;

		$count++;

		$path = app_path() . '/Modules/' . ucfirst($component) . '/Config/access.xml';

		$actions = Gate::getActionsFromFile(
			$path,
			"/access/section[@name='" . $section . "']/"
		);

		if (empty($actions))
		{
			$actions = array();
		}

		$html = array();
		$html[] = '<ul class="checklist access-actions">';

		for ($i = 0, $n = count($actions); $i < $n; $i++)
		{
			$item = &$actions[$i];

			// Setup  the variable attributes.
			$eid = $count . 'action_' . $item->id;
			$checked = in_array($item->id, $selected) ? ' checked="checked"' : '';

			// Build the HTML for the item.
			$html[] = '	<li>';
			$html[] = '		<input type="checkbox" name="' . $name . '[]" value="' . $item->id . '" id="' . $eid . '"' . $checked . ' />';
			$html[] = '		<label for="' . $eid . '">' . trans($item->title) . '</label>';
			$html[] = '	</li>';
		}
		$html[] = '</ul>';

		return implode("\n", $html);
	}

	/**
	 * Gets a list of the asset groups as an array of options.
	 *
	 * @param   array  $config  An array of options for the options
	 * @return  mixed  An array or false if an error occurs
	 */
	public static function assetgroups($config = array())
	{
		if (empty(self::$asset_groups))
		{
			/*$db = App::get('db');

			$query = $db->getQuery()
				->select('a.id', 'value')
				->select('a.title', 'text')
				->from('#__viewlevels', 'a')
				->group('a.id')
				->group('a.title')
				->group('a.ordering')
				->order('a.ordering', 'asc');

			$db->setQuery($query->toString());
			self::$asset_groups = $db->loadObjectList();

			// Check for a database error.
			if ($db->getErrorNum())
			{
				throw new Exception($db->getErrorMsg(), 500, E_WARNING);
				return false;
			}*/

			self::$asset_groups = Viewlevel::query()
				->select(['id AS value', 'title AS text'])
				->groupBy(['id', 'title', 'ordering'])
				->orderBy('ordering', 'asc')
				->get();
		}

		return self::$asset_groups;
	}

	/**
	 * Displays a Select list of the available asset groups
	 *
	 * @param   string  $name      The name of the select element
	 * @param   mixed   $selected  The selected asset group id
	 * @param   string  $attribs   Optional attributes for the select field
	 * @param   array   $config    An array of options for the control
	 * @return  mixed   An HTML string or null if an error occurs
	 */
	public static function assetgrouplist($name, $selected, $attribs = null, $config = array())
	{
		static $count;

		$options = self::assetgroups();
		if (isset($config['title']))
		{
			array_unshift($options, Select::option('', $config['title']));
		}

		return Select::genericlist(
			$options,
			$name,
			array(
				'id' => isset($config['id']) ? $config['id'] : 'assetgroups_' . ++$count,
				'list.attr' => (is_null($attribs) ? 'class="form-control" size="3"' : $attribs),
				'list.select' => (int) $selected
			)
		);
	}
}
