<?php

namespace App\Modules\Software\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Resources\Models\Asset;

/**
 * Model for version/resource association
 *
 * @property int    $id
 * @property int    $version_id
 * @property int    $resource_id
 */
class VersionResource extends Model
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
	protected $table = 'application_version_resources';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int,string>
	 */
	protected $guarded = [
		'id',
	];

	/**
	 * Get associated version
	 *
	 * @return  BelongsTo
	 */
	public function version(): BelongsTo
	{
		return $this->belongsTo(Version::class, 'version_id');
	}

	/**
	 * Get associated asset
	 *
	 * @return  BelongsTo
	 */
	public function asset(): BelongsTo
	{
		return $this->belongsTo(Asset::class, 'resource_id');
	}

	/**
	 * Find a record by association IDs or create a new one
	 *
	 * @param int $version_id
	 * @param int $resource_id
	 * @return VersionResource
	 */
	public static function findByVersionResourceOrNew(int $version_id, int $resource_id): VersionResource
	{
		$row = self::query()
			->where('version_id', '=', $version_id)
			->where('resource_id', '=', $resource_id)
			->first();

		if (!$row)
		{
			$row = new VersionResource;
			$row->version_id = $version_id;
			$row->resource_id = $resource_id;
		}

		return $row;
	}
}
