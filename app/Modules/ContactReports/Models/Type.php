<?php

namespace App\Modules\ContactReports\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use App\Modules\History\Traits\Historable;
use App\Modules\ContactReports\Events\TypeCreated;
use App\Modules\ContactReports\Events\TypeDeleted;
use App\Halcyon\Models\Timeperiod;

/**
 * Model for Contact Report type
 *
 * @property int    $id
 * @property string $name
 * @property int    $timeperiodid
 * @property int    $timeperiodcount
 * @property int    $timeperiodlimit
 * @property int    $waitperiodid
 * @property int    $waitperiodcount
 */
class Type extends Model
{
	use Historable;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 */
	protected $table = 'contactreporttypes';

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
	 * The attributes that are mass assignable.
	 *
	 * @var array<int,string>
	 */
	protected $guarded = [
		'id',
	];

	/**
	 * Fields and their validation criteria
	 *
	 * @var array<string,string>
	 */
	protected $rules = array(
		'name' => 'required|string|max:32'
	);

	/**
	 * The event map for the model.
	 *
	 * @var array<string,string>
	 */
	protected $dispatchesEvents = [
		'created' => TypeCreated::class,
		'deleted' => TypeDeleted::class,
	];

	/**
	 * Ensure name doesn't go past the length limit
	 *
	 * @param   string $value
	 * @return  void
	 */
	public function setNameAttribute($value): void
	{
		$this->attributes['name'] = Str::limit($value, 32);
	}

	/**
	 * Get a generated URL slug from the name
	 *
	 * @return  string
	 */
	public function getAliasAttribute(): string
	{
		$alias = trim($this->name);

		// Remove any '-' from the string since they will be used as concatenaters
		$alias = str_replace('-', ' ', $alias);
		$alias = preg_replace('/(\s|[^A-Za-z0-9\-])+/', '-', strtolower($alias));
		$alias = trim($alias, '-');

		return $alias;
	}

	/**
	 * Runs extra setup code when creating a new model
	 *
	 * @return  void
	 */
	protected static function boot(): void
	{
		parent::boot();

		static::saving(function ($model)
		{
			$exist = self::query()
				->where('name', '=', $model->name)
				->where('id', '!=', $model->id)
				->first();

			if ($exist && $exist->id)
			{
				throw new \Exception(trans('An entry with the name ":name" already exists.', ['name' => $model->name]));
			}

			return true;
		});
	}

	/**
	 * Defines a relationship to reports
	 *
	 * @return  HasMany
	 */
	public function reports(): HasMany
	{
		return $this->hasMany(Report::class, 'contactreporttypeid');
	}

	/**
	 * Defines a relationship to timeperiod
	 *
	 * @return  BelongsTo
	 */
	public function timeperiod(): BelongsTo
	{
		return $this->belongsTo(Timeperiod::class, 'timeperiodid');
	}

	/**
	 * Defines a relationship to wait timeperiod
	 *
	 * @return  BelongsTo
	 */
	public function waitperiod(): BelongsTo
	{
		return $this->belongsTo(Timeperiod::class, 'waitperiodid');
	}

	/**
	 * Find a model by its primary key.
	 *
	 * @param  string $name
	 * @param  array<int,string>  $columns
	 * @return Type|null
	 */
	public static function findByName($name, $columns = ['*'])
	{
		$name = str_replace('-', ' ', $name);

		return static::query()
			->where('name', '=', $name)
			->orWhere('name', 'like', '%' . $name . '%')
			->first($columns);
	}

	/**
	 * Delete the record and all associated data
	 *
	 * @return bool False if error, True on success
	 */
	public function delete(): bool
	{
		// Remove children
		foreach ($this->reports as $report)
		{
			if (!$report->update(['contactreporttypeid' => 0]))
			{
				return false;
			}
		}

		// Attempt to delete the record
		return parent::delete();
	}
}
