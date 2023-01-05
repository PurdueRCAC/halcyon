<?php

namespace App\Modules\Menus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Halcyon\Models\Casts\Params;

/**
 * News model mapping to resources
 */
class Widget extends Model
{
	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'widgets';

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public $orderBy = 'id';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public $orderDir = 'asc';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int,string>
	 */
	protected $guarded = [
		'id'
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array<string,string>
	 */
	protected $casts = [
		'published' => 'integer',
		'access' => 'integer',
		'params' => Params::class,
	];

	/**
	 * Get the list of widgets
	 *
	 * @param   integer  $pk
	 * @return  array    An array of module records (id, title, position)
	 */
	public static function forMenuId($pk = 0)
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
