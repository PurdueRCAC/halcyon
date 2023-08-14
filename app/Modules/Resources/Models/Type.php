<?php

namespace App\Modules\Resources\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Modules\Resources\Events\TypeCreating;
use App\Modules\Resources\Events\TypeCreated;
use App\Modules\Resources\Events\TypeUpdating;
use App\Modules\Resources\Events\TypeUpdated;
use App\Modules\Resources\Events\TypeDeleted;

/**
 * Model for a resource type
 *
 * @property int    $id
 * @property string $name
 * @property string $description
 *
 * @property string $api
 * @property int    $resources_count
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
	 * @return  HasMany
	 */
	public function resources(): HasMany
	{
		return $this->hasMany(Asset::class, 'resourcetype');
	}

	/**
	 * Defines a relationship to facet types
	 *
	 * @return  HasMany
	 */
	public function facetTypes(): HasMany
	{
		return $this->hasMany(FacetType::class, 'type_id');
	}

	/**
	 * Get an alias
	 *
	 * @return  string
	 */
	public function getAliasAttribute(): string
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
	public static function findByName(string $name): ?Type
	{
		$name = str_replace('-', ' ', $name);

		return self::query()
			->where('name', 'like', '%' . $name . '%')
			->first();
	}
}
