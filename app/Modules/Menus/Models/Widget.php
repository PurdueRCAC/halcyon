<?php

namespace App\Modules\Menus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Config\Repository;
use App\Halcyon\Models\Casts\Params;
use App\Modules\Widgets\Models\Widget as BaseWidget;

/**
 * Menu widget
 *
 * @property int    $id
 * @property string $title
 * @property string $note
 * @property string $content
 * @property int    $ordering
 * @property int    $position
 * @property int    $checked_out
 * @property Carbon|null $checked_out_time
 * @property Carbon|null $publish_up
 * @property Carbon|null $publish_down
 * @property int    $published
 * @property string $widget
 * @property int    $access
 * @property int    $showtitle
 * @property Repository $params
 * @property int    $client_id
 * @property string $language
 */
class Widget extends BaseWidget
{
	/**
	 * Get the list of widgets
	 *
	 * @param   int  $pk
	 * @return  Collection  An list of widget records (id, title, position)
	 */
	public static function forMenuId($pk = 0): Collection
	{
		$query = DB::table((new self)->getTable() . ' AS a')
			->select([
				'a.id',
				'a.title',
				'a.position',
				'a.published',
				'map.menuid',
				'ag.title AS access_title',
				DB::raw('(SELECT COUNT(*) FROM widgets_menu WHERE widgetid = a.id AND menuid < 0) AS `except`')
			])
			->leftJoin('widgets_menu AS map', 'map.widgetid', '=', DB::raw(sprintf('a.id AND map.menuid IN (0, %1$d, -%1$d)', $pk)))
			->leftJoin('viewlevels AS ag', 'ag.id', 'a.access')
			->where('a.published', '>=', 0)
			->where('a.client_id', '=', 0)
			->orderBy('a.position', 'asc')
			->orderBy('a.ordering', 'asc');

		return $query->get();
	}
}
