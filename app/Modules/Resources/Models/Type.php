<?php

namespace App\Modules\Resources\Models;

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
	 * @var array<int,string>
	 */
	protected $guarded = [
		'id'
	];

	/**
	 * Fields and their validation criteria
	 *
	 * @var array<string,string>
	 */
	protected $rules = array(
		'name' => 'required|string|max:20'
	);

	/**
	 * The event map for the model.
	 *
	 * @var array<string,string>
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
	 * Defines a relationship to resources
	 *
	 * @return  object
	 */
	public function resources()
	{
		return $this->hasMany(Asset::class, 'resourcetype');
	}

	/**
	 * Defines a relationship to facet types
	 *
	 * @return  object
	 */
	public function facetTypes()
	{
		return $this->hasMany(FacetType::class, 'type_id');
	}

	/**
	 * Get an alias
	 *
	 * @return  string
	 */
	public function getAliasAttribute()
	{
		$name = strtolower($this->getOriginal('name'));
		$name = str_replace(' ', '-', $name);

		return $name;
	}

	/**
	 * Find a record by name
	 *
	 * @param  string  $name
	 * @return Type|null
	 */
	public static function findByName($name)
	{
		$name = str_replace('-', ' ', $name);

		return self::query()
			->where('name', 'like', '%' . $name . '%')
			->first();
	}
}
