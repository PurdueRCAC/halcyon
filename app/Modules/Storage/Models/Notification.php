<?php

namespace App\Modules\Storage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\History\Traits\Historable;
use App\Modules\Storage\Models\Notification\Type;
use App\Halcyon\Models\Timeperiod;
use App\Halcyon\Utility\Number;
use Carbon\Carbon;

/**
 * Storage model for a notification
 *
 * @property int    $id
 * @property int    $storagedirid
 * @property int    $userid
 * @property int    $storagedirquotanotificationtypeid
 * @property int    $value
 * @property int    $timeperiodid
 * @property int    $periods
 * @property int    $notice
 * @property Carbon|null $datetimelastnotify
 * @property Carbon|null $datetimecreated
 * @property Carbon|null $datetimeremoved
 * @property int    $enabled
 *
 * @property string $api
 * @property string $threshold
 */
class Notification extends Model
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
	 * The name of the "updated at" column.
	 *
	 * @var  string|null
	 */
	const DELETED_AT = 'datetimeremoved';

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'storagedirquotanotifications';

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var  array<string,string>
	 */
	protected $casts = array(
		'datetimelastnotify' => 'datetime:Y-m-d H:i:s',
	);

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
	 * Set value in bytes
	 *
	 * @param   mixed  $value
	 * @return  void
	 */
	public function setValueAttribute($value): void
	{
		$this->attributes['value'] = Number::toBytes($value);
	}

	/**
	 * Get bytes in human readable format
	 *
	 * @return  string
	 */
	public function getFormattedValueAttribute(): string
	{
		return Number::formatBytes($this->value);
	}

	/**
	 * Defines a relationship to creator
	 *
	 * @return  BelongsTo
	 */
	public function creator(): BelongsTo
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'userid')->withDefault();
	}

	/**
	 * Defines a relationship to timeperiod
	 *
	 * @return  BelongsTo
	 */
	public function timeperiod(): BelongsTo
	{
		return $this->belongsTo(Timeperiod::class, 'timeperiodid')->withDefault();
	}

	/**
	 * Defines a relationship to directory
	 *
	 * @return  BelongsTo
	 */
	public function directory(): BelongsTo
	{
		return $this->belongsTo(Directory::class, 'storagedirid');
	}

	/**
	 * Defines a relationship to notification type
	 *
	 * @return  BelongsTo
	 */
	public function type(): BelongsTo
	{
		return $this->belongsTo(Type::class, 'storagedirquotanotificationtypeid');
	}

	/**
	 * Get next notify datetime
	 *
	 * @return  Carbon
	 */
	public function getNextnotifyAttribute(): Carbon
	{
		$months  = $this->periods * ($this->timeperiod ? $this->timeperiod->months : 1);
		$seconds = $this->periods * ($this->timeperiod ? $this->timeperiod->unixtime : 1);

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

		return $dt;
	}

	/**
	 * Determine if was notified
	 *
	 * @return  bool
	 */
	public function wasNotified(): bool
	{
		return !is_null($this->datetimelastnotify);
	}
}
