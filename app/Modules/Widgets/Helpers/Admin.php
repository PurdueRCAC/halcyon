<?php

namespace App\Modules\Widgets\Helpers;

use App\Modules\Widgets\Models\Widget;
use Illuminate\Support\Facades\DB;

/**
 * Widgets helper.
 */
abstract class Admin
{
	/**
	 * Get a list of postions
	 *
	 * @param   int  $clientId
	 * @return  array    An array of option elements.
	 */
	public static function getPositions($clientId)
	{
		$positions = Widget::query()
			->select(DB::raw('DISTINCT(position)'))
			->where('client_id', (int) $clientId)
			->orderBy('position', 'asc')
			->get();

		return $positions;
	}

	/**
	 * Get a list of the unique widgets installed in the client application.
	 *
	 * @param   int  $clientId  The client id.
	 * @return  array
	 */
	public static function getWidgets($clientId)
	{
		$m = (new Widget)->getTable();

		$widgets = DB::table('extensions AS e')
			->select('e.element', 'e.name', 'e.id')
			->where('e.client_id', '=', (int)$clientId)
			->where('e.type', '=', 'widget')
			->leftJoin($m . ' AS m', function($join)
				{
					$join->on('m.widget', '=', 'e.element')
						->on('m.client_id', '=', 'e.client_id');
				})
			->whereNotNull('m.widget')
			->groupBy('e.element', 'e.name', 'e.id')
			->get();

		foreach ($widgets as $widget)
		{
			$extension = strtolower($widget->element);

			app('translator')->addNamespace(
				'widget.' . $extension,
				app_path() . '/Widgets/' . ucfirst($widget->element) . '/lang'
			);

			$widget->name = trans('widget.' . $extension . '::' . $extension . '.widget name');
			$widget->desc = trans('widget.' . $extension . '::' . $extension . '.widget desc');
		}

		return $widgets->sortBy('text');
	}

	/**
	 * Get a list of the assignment options for widgets to menus.
	 *
	 * @param   int  $clientId  The client id.
	 * @return  array
	 */
	public static function getAssignmentOptions($clientId): array
	{
		$options = array();
		$options['0'] = trans('widgets::widgets.option.all');
		$options['-'] = trans('widgets::widgets.option.none');

		if ($clientId == 0)
		{
			$options['1'] = trans('widgets::widgets.option.include');
			$options['-1'] = trans('widgets::widgets.option.exclude');
		}

		return $options;
	}
}
