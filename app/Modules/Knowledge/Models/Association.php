<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Modules\Knowledge\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

/**
 * Model for a subresource mapping
 */
class Association extends Model
{
	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'kb_page_associations';

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * The "booted" method of the model.
	 *
	 * @return void
	 */
	protected static function booted()
	{
		static::creating(function ($model)
		{
			$result = self::query()
				->select(DB::raw('MAX(ordering) + 1 AS seq'))
				->where('parent_id', '=', $model->parent_id)
				->get()
				->first()
				->seq;

			$model->setAttribute('ordering', (int)$result);
		});
	}

	/**
	 * Defines a relationship to a parent page
	 *
	 * @return  object
	 */
	public function parent()
	{
		return $this->belongsTo(Page::class, 'parent_id')->withTrashed();
	}

	/**
	 * Defines a relationship to a child page
	 *
	 * @return  object
	 */
	public function child()
	{
		return $this->belongsTo(Page::class, 'child_id')->withTrashed();
	}
}
