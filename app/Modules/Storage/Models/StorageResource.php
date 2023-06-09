<?php

namespace App\Modules\Storage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Modules\History\Traits\Historable;
use App\Halcyon\Utility\Number;

/**
 * Storage resource model
 *
 * @property int    $id
 * @property string $name
 * @property \Carbon\Carbon|null $datetimecreated
 * @property \Carbon\Carbon|null $datetimeremoved
 * @property string $path
 * @property int    $parentresourceid
 * @property int    $import
 * @property int    $autouserdir
 * @property int    $defaultquotaspace
 * @property int    $defaultquotafile
 * @property string $importhostname
 * @property int    $getquotatypeid
 * @property int    $createtypeid
 * @property int    $groupmanaged
 */
class StorageResource extends Model
{
	use Historable, SoftDeletes;

	/**
	 * The name of the "created at" column.
	 *
	 * @var string|null
	 */
	const CREATED_AT = 'datetimecreated';

	/**
	 * The name of the "updated at" column.
	 *
	 * @var  string|null
	 */
	const UPDATED_AT = null;

	/**
	 * The name of the "deleted at" column.
	 *
	 * @var  string|null
	 */
	const DELETED_AT = 'datetimeremoved';

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'storageresources';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int,string>
	 */
	protected $guarded = [
		'id',
		'datetimecreated',
		'datetimeremoved',
	];

	/**
	 * The "booted" method of the model.
	 *
	 * @return void
	 */
	protected static function booted(): void
	{
		static::deleted(function ($model)
		{
			// Clean up any associated directories
			foreach ($model->directories as $directory)
			{
				$directory->delete();
			}
		});
	}

	/**
	 * Defines a relationship to directories
	 *
	 * @return  HasMany
	 */
	public function directories(): HasMany
	{
		return $this->hasMany(Directory::class, 'storageresourceid');
	}

	/**
	 * Defines a relationship to a parent resource
	 *
	 * @return  BelongsTo
	 */
	public function resource(): BelongsTo
	{
		return $this->belongsTo('App\Modules\Resources\Models\Asset', 'parentresourceid')->withTrashed();
	}

	/**
	 * Defines a relationship to a message queue type for retrieving quota info
	 *
	 * @return  BelongsTo
	 */
	public function quotaType(): BelongsTo
	{
		return $this->belongsTo('App\Modules\Messages\Models\Type', 'getquotatypeid');
	}

	/**
	 * Defines a relationship to a message queue type for creating a directory
	 *
	 * @return  BelongsTo
	 */
	public function createType(): BelongsTo
	{
		return $this->belongsTo('App\Modules\Messages\Models\Type', 'createtypeid');
	}

	/**
	 * Find a record by name
	 *
	 * @param   string  $name
	 * @return  StorageResource|null
	 */
	public static function findByName(string $name): ?StorageResource
	{
		return self::query()
			->where('name', '=', $name)
			->orWhere('name', 'like', $name . '%')
			->orWhere('name', 'like', '%' . $name)
			->orderBy('name', 'asc')
			->limit(1)
			->get()
			->first();
	}

	/**
	 * Set value in bytes
	 *
	 * @param   string|int  $value
	 * @return  void
	 */
	public function setDefaultquotaspaceAttribute($value): void
	{
		$this->attributes['defaultquotaspace'] = Number::toBytes($value);
	}

	/**
	 * Get defaultquotaspace in human readable format
	 *
	 * @return  string
	 */
	public function getFormattedDefaultquotaspaceAttribute(): string
	{
		return Number::formatBytes($this->defaultquotaspace);
	}

	/**
	 * Set file quota
	 *
	 * @param   mixed  $value
	 * @return  void
	 */
	public function setDefaultquotafileAttribute($value): void
	{
		// Convert 9,000 -> 9000
		$value = str_replace(',', '', $value);

		$this->attributes['defaultquotafile'] = (int)$value;
	}

	/**
	 * Can this be self-serve managed?
	 *
	 * @return  bool
	 */
	public function isGroupManaged(): bool
	{
		return ($this->groupmanaged > 0);
	}
}
