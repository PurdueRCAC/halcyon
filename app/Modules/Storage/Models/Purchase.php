<?php

namespace App\Modules\Storage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use App\Modules\Groups\Models\Group;
use App\Modules\Storage\Events\PurchaseCreated;
use App\Modules\History\Traits\Historable;
use App\Halcyon\Utility\Number;
use Carbon\Carbon;

/**
 * Storage model for a resource purchase
 *
 * @property int    $id
 * @property int    $resourceid
 * @property int    $groupid
 * @property Carbon|null $datetimestart
 * @property Carbon|null $datetimestop
 * @property int    $bytes
 * @property int    $sellergroupid
 * @property string $comment
 *
 * @property string $api
 */
class Purchase extends Model
{
	use Historable, SoftDeletes;

	/**
	 * The name of the "created at" column.
	 *
	 * @var string|null
	 */
	const CREATED_AT = 'datetimestart';

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
		'created' => PurchaseCreated::class,
	];

	/**
	 * Has a start date been set?
	 *
	 * @return  bool
	 */
	public function hasStart(): bool
	{
		return !is_null($this->datetimestart);
	}

	/**
	 * Has the item started?
	 *
	 * @return  bool
	 */
	public function hasStarted(): bool
	{
		return (!$this->hasStart() || $this->datetimestart->timestamp <= Carbon::now()->timestamp);
	}

	/**
	 * Get when this will start in human readable format
	 *
	 * @return  string
	 */
	public function willStart(): string
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
	public function hasEnd(): bool
	{
		return !is_null($this->datetimestop);
	}

	/**
	 * Has the item ended?
	 *
	 * @return  bool
	 */
	public function hasEnded(): bool
	{
		return ($this->hasEnd() && $this->datetimestop->timestamp <= Carbon::now()->timestamp);
	}

	/**
	 * Is the end time sane?
	 *
	 * @return  bool
	 */
	public function endsAfterStarts(): bool
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
	public function willEnd(): string
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
	 * @param   int  $start
	 * @return  string
	 */
	private function calculateTimeLeft(int $start): string
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
	 * @return  BelongsTo
	 */
	public function resource(): BelongsTo
	{
		return $this->belongsTo(StorageResource::class, 'resourceid');
	}

	/**
	 * Defines a relationship to an owner group
	 *
	 * @return  BelongsTo
	 */
	public function group(): BelongsTo
	{
		return $this->belongsTo(Group::class, 'groupid');
	}

	/**
	 * Defines a relationship to a gseller roup
	 *
	 * @return  BelongsTo
	 */
	public function seller(): BelongsTo
	{
		return $this->belongsTo(Group::class, 'sellergroupid');
	}

	/**
	 * Set a query's WHERE clause to include published state
	 *
	 * @param   Builder  $query
	 * @return  Builder
	 */
	public function scopeWhenAvailable(Builder $query): Builder
	{
		$now = Carbon::now()->toDateTimeString();

		return $query->withTrashed()->where(function($where) use ($now)
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
	 * @param   Builder  $query
	 * @return  Builder
	 */
	public function scopeWhenNotAvailable(Builder $query): Builder
	{
		$now = Carbon::now()->toDateTimeString();

		return $query->withTrashed()->whereNotNull('datetimestop')
			->where('datetimestop', '<=', $now);
	}

	/**
	 * Set value in bytes
	 *
	 * @param   mixed  $value
	 * @return  void
	 */
	public function setBytesAttribute($value): void
	{
		$this->attributes['bytes'] = Number::toBytes($value);
	}

	/**
	 * Get bytes in human readable format
	 *
	 * @return  string
	 */
	public function getFormattedBytesAttribute(): string
	{
		return Number::formatBytes($this->bytes);
	}

	/**
	 * Get counter entry
	 *
	 * @return  Purchase|null
	 */
	public function getCounterAttribute(): ?Purchase
	{
		return self::query()
			->where('datetimestart', '=', $this->datetimestart)
			->where('datetimestop', '=', ($this->hasEnd() ? $this->datetimestop : null))
			->where('groupid', '=', $this->sellergroupid)
			->where('sellergroupid', '=', $this->groupid)
			->first();
	}

	/**
	 * Get the transaction type (loan|purchase)
	 *
	 * @return  string
	 */
	public function getTypeAttribute(): string
	{
		return 'purchase';
	}
}
