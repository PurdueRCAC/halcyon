<?php

namespace App\Modules\Software\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Builder;
use App\Modules\Resources\Models\Asset;
use App\Modules\Software\Events\VersionCreated;
use App\Modules\Software\Events\VersionDeleted;

/**
 * Application version
 *
 * @property int    $id
 * @property int    $application_id
 * @property string $title
 *
 * @property string $api
 */
class Version extends Model
{
	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'application_versions';

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'title';

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
	 * The event map for the model.
	 *
	 * @var array<string,string>
	 */
	protected $dispatchesEvents = [
		'created' => VersionCreated::class,
		'deleted' => VersionDeleted::class,
	];

	/**
	 * Boot
	 *
	 * @return  void
	 */
	public static function boot(): void
	{
		parent::boot();

		self::deleted(function($model)
		{
			foreach ($model->associations as $association)
			{
				$association->delete();
			}
		});
	}

	/**
	 * Get parent application
	 *
	 * @return  BelongsTo
	 */
	public function application(): BelongsTo
	{
		return $this->belongsTo(Application::class, 'application_id');
	}

	/**
	 * Get associated resources
	 *
	 * @return  HasMany
	 */
	public function associations(): HasMany
	{
		return $this->hasMany(VersionResource::class, 'version_id');
	}

	/**
	 * Defines a direct relationship to resources
	 *
	 * @return  HasManyThrough
	 */
	public function resources(): HasManyThrough
	{
		return $this->hasManyThrough(Asset::class, VersionResource::class, 'version_id', 'id', 'id', 'resource_id');
	}

	/**
	 * Query scope with search
	 *
	 * @param   Builder  $query
	 * @param   string|int   $search
	 * @return  Builder
	 */
	public function scopeWhereSearch(Builder $query, $search): Builder
	{
		if (is_numeric($search))
		{
			$query->where('id', '=', $search);
		}
		else
		{
			$filters['search'] = strtolower((string)$search);

			$query->where('title', 'like', '%' . $search . '%');
		}

		return $query;
	}

	/**
	 * Set the list of version/resource associations
	 *
	 * @param array<int,int> $resources
	 * @return void
	 */
	public function setResources(array $resources): void
	{
		$prev = $this->associations;
		$current = array();

		foreach ($resources as $resource)
		{
			$v = VersionResource::findByVersionResourceOrNew($this->id, $resource);
			$v->version_id = $this->id;
			$v->resource_id = $resource;
			$v->save();

			$current[] = $v->id;
		}

		foreach ($prev as $v)
		{
			if (!in_array($v->id, $current))
			{
				$v->delete();
			}
		}
	}
}
