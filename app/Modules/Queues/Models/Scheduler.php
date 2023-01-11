<?php
namespace App\Modules\Queues\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\History\Traits\Historable;
use App\Modules\Resources\Models\Batchsystem;
use App\Modules\Resources\Models\Subresource;
use App\Modules\Resources\Models\Asset;
use App\Modules\Resources\Models\Child;
use Carbon\Carbon;

/**
 * Model for a scheduler
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
	 * Fields and their validation criteria
	 *
	 * @var array<string,string>
	 */
	protected $rules = array(
		'hostname' => 'required|string|max:64',
		'batchsystem' => 'integer',
		'schedulerpolicyid' => 'integer',
		'queuesubresourceid' => 'required|integer',
		'defaultmaxwalltime' => 'required|integer',
	);

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
	public function hasDraindownTime()
	{
		return !is_null($this->datetimedraindown);
	}

	/**
	 * Determine if entry has an end time
	 *
	 * @return  bool
	 */
	public function hasLastImportStartTime()
	{
		return !is_null($this->datetimelastimportstart);
	}

	/**
	 * Determine if entry has an end time
	 *
	 * @return  bool
	 */
	public function hasLastImportStopTime()
	{
		return !is_null($this->datetimelastimportstop);
	}

	/**
	 * Defines a relationship to queues
	 *
	 * @return  object
	 */
	public function queues()
	{
		return $this->hasMany(Queue::class, 'schedulerid');
	}

	/**
	 * Defines a relationship to policy
	 *
	 * @return  object
	 */
	public function policy()
	{
		return $this->belongsTo(SchedulerPolicy::class, 'schedulerpolicyid');
	}

	/**
	 * Defines a relationship to bath systems
	 *
	 * @return  object
	 */
	public function batchsystm()
	{
		return $this->belongsTo(Batchsystem::class, 'batchsystem');
	}

	/**
	 * Defines a relationship to reservations
	 *
	 * @return  object
	 */
	public function reservations()
	{
		return $this->hasMany(SchedulerReservation::class, 'schedulerid');
	}

	/**
	 * Defines a relationship to subresource
	 *
	 * @return  object
	 */
	public function subresource()
	{
		return $this->belongsTo(Child::class, 'queuesubresourceid', 'subresourceid');
	}

	/**
	 * Get the resource
	 *
	 * @return object
	 */
	public function resource()
	{
		return $this->hasOneThrough(Asset::class, Child::class, 'subresourceid', 'id', 'queuesubresourceid', 'resourceid')->withTrashed();
	}

	/**
	 * Get default max walltime in human readable format
	 *
	 * @return  string
	 */
	public function humanDefaultmaxwalltime()
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
	 * @return  object
	 */
	public static function findByHostname($hostname)
	{
		return self::query()
			->where('hostname', '=', $hostname)
			->get()
			->first();
	}
}
