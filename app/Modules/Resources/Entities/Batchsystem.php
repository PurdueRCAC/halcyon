<?php

namespace App\Modules\Resources\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Model for a batch system
 */
class Batchsystem extends Model
{
	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'batchsystems';

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * Fields and their validation criteria
	 *
	 * @var array
	 */
	protected $rules = array(
		'name' => 'required|string|max:16'
	);

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'name';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public static $orderDir = 'asc';

	/**
	 * Defines a relationship to notification type
	 *
	 * @return  object
	 */
	public function resources()
	{
		return $this->hasMany(Asset::class, 'batchsystem');
	}
}
