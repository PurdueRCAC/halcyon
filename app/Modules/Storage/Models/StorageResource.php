<?php

namespace App\Modules\Storage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\History\Traits\Historable;
use App\Halcyon\Utility\Number;

/**
 * Storage resource model
 */
class StorageResource extends Model
{
	use Historable, SoftDeletes;

	/**
	 * The name of the "created at" column.
	 *
	 * @var string
	 */
	const CREATED_AT = 'datetimecreated';

	/**
	 * The name of the "updated at" column.
	 *
	 * @var  string
	 */
	const UPDATED_AT = null;

	/**
	 * The name of the "deleted at" column.
	 *
	 * @var  string
	 */
	const DELETED_AT = 'datetimeremoved';

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'storageresources';

	/**
	 * Automatic fields to populate every time a row is created
	 *
	 * @var  array
	 */
	protected $dates = array(
		'datetimecreated',
		'datetimeremoved'
	);

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
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
	protected static function booted()
	{
		static::deleted(function ($model)
		{
			foreach ($model->directories as $directory)
			{
				$directory->delete();
			}
		});
	}

	/**
	 * Defines a relationship to directories
	 *
	 * @return  object
	 */
	public function directories()
	{
		return $this->hasMany(Directory::class, 'storageresourceid');
	}

	/**
	 * Defines a relationship to loans
	 *
	 * @return  object
	 */
	public function resource()
	{
		return $this->belongsTo('App\Modules\Resources\Models\Asset', 'parentresourceid')->withTrashed();
	}

	/**
	 * Defines a relationship to loans
	 *
	 * @return  object
	 */
	public function quotaType()
	{
		return $this->belongsTo('App\Modules\Messages\Models\Type', 'getquotatypeid');
	}

	/**
	 * Defines a relationship to loans
	 *
	 * @return  object
	 */
	public function createType()
	{
		return $this->belongsTo('App\Modules\Messages\Models\Type', 'createtypeid');
	}

	/**
	 * Find a record by name
	 *
	 * @param   string  $name
	 * @return  StorageResource|null
	 */
	public static function findByName($name)
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
	public function setDefaultquotaspaceAttribute($value)
	{
		$this->attributes['defaultquotaspace'] = Number::toBytes($value);
	}

	/**
	 * Get defaultquotaspace in human readable format
	 *
	 * @return  string
	 */
	public function getFormattedDefaultquotaspaceAttribute()
	{
		return Number::formatBytes($this->defaultquotaspace);
	}

	/**
	 * Set value in bytes
	 *
	 * @param   mixed  $value
	 * @return  void
	 */
	public function setDefaultquotafileAttribute($value)
	{
		$value = str_replace(',', '', $value);

		$this->attributes['defaultquotafile'] = (int)$value;
	}

	/**
	 * Can this be self-serve managed?
	 *
	 * @return  bool
	 */
	public function isGroupManaged()
	{
		return ($this->groupmanaged > 0);
	}
}
