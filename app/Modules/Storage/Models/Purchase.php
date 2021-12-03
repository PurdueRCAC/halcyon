<?php

namespace App\Modules\Storage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\Groups\Models\Group;
use App\Modules\Storage\Events\PurchaseCreated;
use App\Modules\History\Traits\Historable;
use App\Halcyon\Utility\Number;
use Carbon\Carbon;

/**
 * Storage model for a resource purchase
 */
class Purchase extends Model
{
	use Historable, SoftDeletes;

	/**
	 * The name of the "created at" column.
	 *
	 * @var string
	 */
	const CREATED_AT = 'datetimestart';

	/**
	 * The name of the "updated at" column.
	 *
	 * @var  string
	 */
	const UPDATED_AT = null;

	/**
	 * The name of the "updated at" column.
	 *
	 * @var  string
	 */
	const DELETED_AT = 'datetimestop';

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'storagedirpurchases';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [
		'id'
	];

	/**
	 * Automatic fields to populate every time a row is created
	 *
	 * @var  array
	 */
	protected $dates = array(
		'datetimestart',
		'datetimestop'
	);

	/**
	 * The event map for the model.
	 *
	 * @var array
	 */
	protected $dispatchesEvents = [
		'created' => PurchaseCreated::class,
	];

	/**
	 * Has a start date been set?
	 *
	 * @return  bool
	 */
	public function hasStart()
	{
		return !is_null($this->datetimestart);
	}

	/**
	 * Has the item started?
	 *
	 * @return  bool
	 */
	public function hasStarted()
	{
		return (!$this->hasStart() || $this->datetimestart->timestamp <= Carbon::now()->timestamp);
	}

	/**
	 * Get when this will start in human readable format
	 *
	 * @return  string
	 */
	public function willStart()
	{
		if (!$this->hasStart())
		{
			return trans('global.immediately');
		}
		if ($this->hasStarted())
		{
			return $this->datetimestart->toDateTimeString();
		}

		return $this->calculateTimeLeft($this->datetimestart->timestamp);
	}

	/**
	 * Has an end date been set?
	 *
	 * @return  bool
	 */
	public function hasEnd()
	{
		return !is_null($this->datetimestop);
	}

	/**
	 * Has the item ended?
	 *
	 * @return  bool
	 */
	public function hasEnded()
	{
		return ($this->hasEnd() && $this->datetimestop->timestamp <= Carbon::now()->timestamp);
	}

	/**
	 * Is the end time sane?
	 *
	 * @return  bool
	 */
	public function endsAfterStarts()
	{
		if (!$this->hasEnd())
		{
			return true;
		}
		return ($this->datetimestop->timestamp > $this->datetimestart->timestamp);
	}

	/**
	 * Get when this will end in human readable format
	 *
	 * @return  string
	 */
	public function willEnd()
	{
		if (!$this->hasEnd())
		{
			return trans('global.never');
		}
		if ($this->hasEnded())
		{
			return $this->datetimestop->toDateTimeString();
		}

		return $this->calculateTimeLeft($this->datetimestop->timestamp);
	}

	/**
	 * Calculate time left from a start time
	 *
	 * @param   integer  $start
	 * @return  string
	 */
	private function calculateTimeLeft(int $start)
	{
		$inputSeconds = $start - Carbon::now()->timestamp;

		$secondsInAMinute = 60;
		$secondsInAnHour = 60 * $secondsInAMinute;
		$secondsInADay = 24 * $secondsInAnHour;

		// Extract days
		$days = floor($inputSeconds / $secondsInADay);

		// Extract hours
		$hourSeconds = $inputSeconds % $secondsInADay;
		$hours = floor($hourSeconds / $secondsInAnHour);

		// Extract minutes
		$minuteSeconds = $hourSeconds % $secondsInAnHour;
		$minutes = floor($minuteSeconds / $secondsInAMinute);

		// Extract the remaining seconds
		$remainingSeconds = $minuteSeconds % $secondsInAMinute;
		$seconds = ceil($remainingSeconds);

		// Format and return
		$timeParts = [];
		$sections = [
			'days'    => (int)$days,
			'hours'   => (int)$hours,
			'minutes' => (int)$minutes,
			'seconds' => (int)$seconds,
		];

		foreach ($sections as $name => $value)
		{
			if ($value > 0)
			{
				$timeParts[] = $value . ' ' . trans_choice('global.time.' . $name, $value);
				break;
			}
		}

		return implode(', ', $timeParts);
	}

	/**
	 * Defines a relationship to a resource
	 *
	 * @return  object
	 */
	public function resource()
	{
		return $this->belongsTo(StorageResource::class, 'resourceid');
	}

	/**
	 * Defines a relationship to a group
	 *
	 * @return  object
	 */
	public function group()
	{
		return $this->belongsTo(Group::class, 'groupid');
	}

	/**
	 * Defines a relationship to a group
	 *
	 * @return  object
	 */
	public function seller()
	{
		return $this->belongsTo(Group::class, 'sellergroupid');
	}

	/**
	 * Set a query's WHERE clause to include published state
	 *
	 * @param   object  $query
	 * @return  object
	 */
	public function scopeWhenAvailable($query)
	{
		$now = Carbon::now()->toDateTimeString();

		return $query->where(function($where) use ($now)
			{
				$where->whereNull('datetimestop')
					->orWhere('datetimestart', '<', $now);
			})
			->where(function($where) use ($now)
			{
				$where->whereNull('datetimestop')
					->orWhere('datetimestop', '>', $now);
			});
	}

	/**
	 * Set a query's WHERE clause to include published state
	 *
	 * @param   object  $query
	 * @return  object
	 */
	public function scopeWhenNotAvailable($query)
	{
		$now = Carbon::now()->toDateTimeString();

		return $query->whereNotNull('datetimestop')
			->where('datetimestop', '<=', $now);
	}

	/**
	 * Set value in bytes
	 *
	 * @param   mixed  $value
	 * @return  void
	 */
	public function setBytesAttribute($value)
	{
		$this->attributes['bytes'] = Number::toBytes($value);
	}

	/**
	 * Get bytes in human readable format
	 *
	 * @return  string
	 */
	public function getFormattedBytesAttribute()
	{
		return Number::formatBytes($this->bytes);
	}

	/**
	 * Get a list of usage
	 *
	 * @return  object
	 */
	public function getCounterAttribute()
	{
		return self::query()
			->where('datetimestart', '=', $this->datetimestart)
			->where('datetimestop', '=', ($this->hasEnd() ? $this->datetimestop : null))
			->where('groupid', '=', $this->sellergroupid)
			->where('sellergroupid', '=', $this->groupid)
			->get()
			->first();
	}

	/**
	 * Defines a relationship to a resource
	 *
	 * @return  string
	 */
	public function getTypeAttribute()
	{
		return 'purchase';
	}
}
