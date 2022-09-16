<?php

namespace App\Modules\Storage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\History\Traits\Historable;
use App\Halcyon\Models\Timeperiod;
use App\Modules\Storage\Models\Notification\Type;
use App\Halcyon\Utility\Number;
use Carbon\Carbon;

/**
 * Storage model for a notification
 */
class Notification extends Model
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
	 * The name of the "updated at" column.
	 *
	 * @var  string
	 */
	const DELETED_AT = 'datetimeremoved';

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'storagedirquotanotifications';

	/**
	 * Automatic fields to populate every time a row is created
	 *
	 * @var  array
	 */
	protected $dates = array(
		'datetimecreated',
		'datetimeremoved',
		'datetimelastnotify'
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
	 * Set value in bytes
	 *
	 * @param   mixed  $value
	 * @return  void
	 */
	public function setValueAttribute($value)
	{
		$this->attributes['value'] = Number::toBytes($value);
	}

	/**
	 * Get bytes in human readable format
	 *
	 * @return  string
	 */
	public function getFormattedValueAttribute()
	{
		return Number::formatBytes($this->value);
	}

	/**
	 * Defines a relationship to creator
	 *
	 * @return  object
	 */
	public function creator()
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'userid')->withDefault();
	}

	/**
	 * Defines a relationship to timeperiod
	 *
	 * @return  object
	 */
	public function timeperiod()
	{
		return $this->belongsTo(Timeperiod::class, 'timeperiodid')->withDefault();
	}

	/**
	 * Defines a relationship to directory
	 *
	 * @return  object
	 */
	public function directory()
	{
		return $this->belongsTo(Directory::class, 'storagedirid');
	}

	/**
	 * Defines a relationship to notification type
	 *
	 * @return  object
	 */
	public function type()
	{
		return $this->belongsTo(Type::class, 'storagedirquotanotificationtypeid');
	}

	/**
	 * Get next notify datetime
	 *
	 * @return  object
	 */
	public function getNextnotifyAttribute()
	{
		$months  = $this->periods * $this->timeperiod->months;
		$seconds = $this->periods * $this->timeperiod->unixtime;

		if ($this->wasNotified())
		{
			$dt = Carbon::parse($this->datetimelastnotify);
		}
		else
		{
			$dt = Carbon::parse($this->datetimecreated);
		}
		
		if ($months)
		{
			$dt->modify('+ ' . $months . ' months');
		}
		if ($seconds)
		{
			$dt->modify('+ ' . $seconds . ' seconds');
		}

		return $dt;//->toDateTimeString();
	}

	/**
	 * Determine if was notified
	 *
	 * @return  bool
	 */
	public function wasNotified()
	{
		return !is_null($this->datetimelastnotify);
	}
}
