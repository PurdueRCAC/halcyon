<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Modules\Resources\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Resources\Events\TypeCreating;
use App\Modules\Resources\Events\TypeCreated;
use App\Modules\Resources\Events\TypeUpdating;
use App\Modules\Resources\Events\TypeUpdated;
use App\Modules\Resources\Events\TypeDeleted;

/**
 * Model for a resource type
 */
class Type extends Model
{
	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'resourcetypes';

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [
		'id'
	];

	/**
	 * Fields and their validation criteria
	 *
	 * @var array
	 */
	protected $rules = array(
		'name' => 'required|string|max:20'
	);

	/**
	 * The event map for the model.
	 *
	 * @var array
	 */
	protected $dispatchesEvents = [
		'creating' => TypeCreating::class,
		'created'  => TypeCreated::class,
		'updating' => TypeUpdating::class,
		'updated'  => TypeUpdated::class,
		'deleted'  => TypeDeleted::class,
	];

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
	public static $orderDir = 'desc';

	/**
	 * Defines a relationship to notification type
	 *
	 * @return  object
	 */
	public function resources()
	{
		return $this->hasMany(Asset::class, 'resourcetype');
	}

	/**
	 * Defines a relationship to notification type
	 *
	 * @return  object
	 */
	public function getAliasAttribute()
	{
		$name = strtolower($this->name);
		$name = str_replace(' ', '-', $name);

		return $name;
	}

	/**
	 * Defines a relationship to notification type
	 *
	 * @return  object
	 */
	public static function findByName($name)
	{
		$name = str_replace('-', ' ', $name);

		return self::query()
			->where('name', 'like', '%' . $name . '%')
			->first();
	}
}
