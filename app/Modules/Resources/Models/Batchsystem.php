<?php

namespace App\Modules\Resources\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model for a batch system
 *
 * @property int    $id
 * @property string $name
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
	 * @var array<string,string>
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
	 * Defines a relationship to resources
	 *
	 * @return  HasMany
	 */
	public function resources(): HasMany
	{
		return $this->hasMany(Asset::class, 'batchsystem');
	}
}
