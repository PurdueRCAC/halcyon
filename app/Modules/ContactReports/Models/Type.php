<?php

namespace App\Modules\ContactReports\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Modules\History\Traits\Historable;
use App\Modules\ContactReports\Events\TypeCreated;
use App\Modules\ContactReports\Events\TypeDeleted;
use App\Halcyon\Models\Timeperiod;

/**
 * Model for Contact Report type
 */
class Type extends Model
{
	use Historable;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
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
	 * Split event into plugin name and event
	 *
	 * @param   string $value the data being saved
	 * @return  void
	 **/
	public function setNameAttribute($value)
	{
		$this->attributes['name'] = Str::limit($value, 32);
	}

	/**
	 * Split event into plugin name and event
	 *
	 * @return  string
	 **/
	public function getAliasAttribute()
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
	protected static function boot()
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
	 * @return  object
	 */
	public function reports()
	{
		return $this->hasMany(Report::class, 'contactreporttypeid');
	}

	/**
	 * Defines a relationship to timeperiod
	 *
	 * @return  object
	 */
	public function timeperiod()
	{
		return $this->belongsTo(Timeperiod::class, 'timeperiodid');
	}

	/**
	 * Defines a relationship to wait timeperiod
	 *
	 * @return  object
	 */
	public function waitperiod()
	{
		return $this->belongsTo(Timeperiod::class, 'waitperiodid');
	}

	/**
	 * Find a model by its primary key.
	 *
	 * @param  string $name
	 * @param  array  $columns
	 * @return \Illuminate\Database\Eloquent\Model|null
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
	 * @return boolean False if error, True on success
	 */
	public function delete()
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
