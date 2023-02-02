<?php

namespace App\Modules\Publications\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model for publication types
 *
 * @property int    $id
 * @property string $name
 * @property string $alias
 */
class Type extends Model
{
	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'publication_types';

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
	 * The attributes that are mass assignable.
	 *
	 * @var array<int,string>
	 */
	protected $guarded = [
		'id',
	];

	/**
	 * Get a list of publications
	 *
	 * @return  HasMany
	 */
	public function publications(): HasMany
	{
		return $this->hasMany(Publication::class, 'type_id');
	}
}
