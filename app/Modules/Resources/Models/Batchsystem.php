<?php

namespace App\Modules\Resources\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model for a batch system
 *
 * @property int    $id
 * @property string $name
 *
 * @property string $api
 * @property int    $resources_count
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
	 * Defines a relationship to resources
	 *
	 * @return  HasMany
	 */
	public function resources(): HasMany
	{
		return $this->hasMany(Asset::class, 'batchsystem');
	}
}
