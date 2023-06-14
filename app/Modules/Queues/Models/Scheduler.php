<?php
namespace App\Modules\Queues\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\History\Traits\Historable;
use App\Modules\Resources\Models\Batchsystem;
use App\Modules\Resources\Models\Subresource;
use App\Modules\Resources\Models\Asset;
use App\Modules\Resources\Models\Child;
use Carbon\Carbon;

/**
 * Model for a scheduler
 *
 * @property int    $id
 * @property int    $queuesubresourceid
 * @property string $hostname
 * @property int    $batchsystem
 * @property int    $schedulerpolicyid
 * @property int    $defaultmaxwalltime
 * @property int    $teragridresource
 * @property int    $teragridaggregate
 * @property Carbon|null $datetimedraindown
 * @property Carbon|null $datetimecreated
 * @property Carbon|null $datetimeremoved
 * @property Carbon|null $datetimelastimportstart
 * @property Carbon|null $datetimelastimportstop
 */
class Scheduler extends Model
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
	 * @var string|null
	 */
	const UPDATED_AT = null;

	/**
	 * The name of the "deleted at" column.
	 *
	 * @var string|null
	 */
	const DELETED_AT = 'datetimeremoved';

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'schedulers';

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'hostname';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public static $orderDir = 'asc';

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var  array<string,string>
	 */
	protected $casts = [
		'datetimedraindown' => 'datetime:Y-m-d H:i:s',
		'datetimelastimportstart' => 'datetime:Y-m-d H:i:s',
		'datetimelastimportstop' => 'datetime:Y-m-d H:i:s',
	];

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int,string>
	 */
	protected $guarded = [
		'id'
	];

	/**
	 * Determine if entry has an end time
	 *
	 * @return  bool
	 */
	public function hasDraindownTime(): bool
	{
		return !is_null($this->datetimedraindown);
	}

	/**
	 * Determine if entry has an end time
	 *
	 * @return  bool
	 */
	public function hasLastImportStartTime(): bool
	{
		return !is_null($this->datetimelastimportstart);
	}

	/**
	 * Determine if entry has an end time
	 *
	 * @return  bool
	 */
	public function hasLastImportStopTime(): bool
	{
		return !is_null($this->datetimelastimportstop);
	}

	/**
	 * Defines a relationship to queues
	 *
	 * @return  HasMany
	 */
	public function queues(): HasMany
	{
		return $this->hasMany(Queue::class, 'schedulerid');
	}

	/**
	 * Defines a relationship to policy
	 *
	 * @return  BelongsTo
	 */
	public function policy(): BelongsTo
	{
		return $this->belongsTo(SchedulerPolicy::class, 'schedulerpolicyid');
	}

	/**
	 * Defines a relationship to bath systems
	 *
	 * @return  BelongsTo
	 */
	public function batchsystm(): BelongsTo
	{
		return $this->belongsTo(Batchsystem::class, 'batchsystem');
	}

	/**
	 * Defines a relationship to reservations
	 *
	 * @return  HasMany
	 */
	public function reservations(): HasMany
	{
		return $this->hasMany(SchedulerReservation::class, 'schedulerid');
	}

	/**
	 * Defines a relationship to QoS
	 *
	 * @return  HasMany
	 */
	public function qoses(): HasMany
	{
		return $this->hasMany(Qos::class, 'scheduler_id');
	}

	/**
	 * Defines a relationship to subresource
	 *
	 * @return  BelongsTo
	 */
	public function subresource(): BelongsTo
	{
		return $this->belongsTo(Child::class, 'queuesubresourceid', 'subresourceid');
	}

	/**
	 * Get the resource
	 *
	 * @return  HasOneThrough
	 */
	public function resource(): HasOneThrough
	{
		return $this->hasOneThrough(Asset::class, Child::class, 'subresourceid', 'id', 'queuesubresourceid', 'resourceid')->withTrashed();
	}

	/**
	 * Get default max walltime in human readable format
	 *
	 * @return  string
	 */
	public function humanDefaultmaxwalltime(): string
	{
		$inputSeconds = $this->defaultmaxwalltime;

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
			}
		}

		return implode(', ', $timeParts);
	}

	/**
	 * Find a record by hostname
	 *
	 * @param   string  $hostname
	 * @return  Scheduler|null
	 */
	public static function findByHostname(string $hostname)
	{
		return self::query()
			->where('hostname', '=', $hostname)
			->first();
	}
}
