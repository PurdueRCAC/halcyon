<?php
namespace App\Modules\Users\Helpers;

use Illuminate\Support\Fluent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use App\Halcyon\Facades\Submenu;
use App\Halcyon\Access\Role;

/**
 * Helper for some admin tasks
 */
class Admin
{
	/**
	 * A cache for the available actions.
	 *
	 * @var  Fluent
	 */
	protected static $actions;

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return  Fluent
	 */
	public static function getActions(): Fluent
	{
		if (empty(self::$actions))
		{
			self::$actions = new Fluent;

			$path = dirname(__DIR__) . '/Config/access.php';

			$actions = include $path;//App\Halcyon\Access\Gate::getActionsFromFile($path);
			$actions ? $actions['module'] : array();

			foreach ($actions as $action)
			{
				self::$actions->{$action->name} = auth()->user()->can($action->name . ' users');
			}
		}

		return self::$actions;
	}

	/**
	 * Get a list of filter options for the blocked state of a user.
	 *
	 * @return  array<int,string>  An array of Option elements.
	 */
	public static function getStateOptions(): array
	{
		$options = array(
			0 => trans('users::users.enabled'),
			1 => trans('users::users.disbled'),
		);

		return $options;
	}

	/**
	 * Get a list of filter options for the activated state of a user.
	 *
	 * @return  array<int,string>  An array of Option elements.
	 */
	public static function getActiveOptions(): array
	{
		$options = array(
			0 => trans('users::users.activated'),
			1 => trans('users::users.unactivated'),
		);

		return $options;
	}

	/**
	 * Get a list of filter options for the approved state of a user.
	 *
	 * @return  array<int,string>  An array of Option elements.
	 */
	public static function getApprovedOptions(): array
	{
		$options = array(
			0 => trans('users::users.unapproved'),
			1 => trans('users::users.manually approved'),
			2 => trans('users::users.automatically approved'),
		);

		return $options;
	}

	/**
	 * Get a list of the user groups for filtering.
	 *
	 * @return  Collection
	 */
	public static function getAccessRoles(): Collection
	{
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

		foreach ($options as &$option)
		{
			$option->text = str_repeat('- ', $option->level) . $option->text;
		}

		return $options;
	}

	/**
	 * Creates a list of range options used in filter select list
	 * used in com_users on users view
	 *
	 * @return  array<string,string>
	 */
	public static function getRangeOptions(): array
	{
		$options = array(
			'today'       => trans('users::users.range today'),
			'past_week'   => trans('users::users.range past week'),
			'past_1month' => trans('users::users.range past month'),
			'past_3month' => trans('users::users.range past 3 months'),
			'past_6month' => trans('users::users.range past 6 months'),
			'past_year'   => trans('users::users.range past year'),
			'post_year'   => trans('users::users.range post year'),
		);

		return $options;
	}
}
