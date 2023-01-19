<?php
namespace App\Modules\Queues\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Queues\Events\QueueSizeCreated;
use App\Modules\Queues\Events\QueueSizeUpdated;
use App\Modules\Queues\Events\QueueSizeDeleted;
use App\Modules\History\Traits\Historable;
use App\Modules\Users\Models\User;
use Carbon\Carbon;

/**
 * Model for a queue purchase
 */
class Size extends Model
{
	use Historable;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'queuesizes';

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
	public static $orderBy = 'datetimestart';

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
		'queueid' => 'required|integer|min:1'
	);

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int,string>
	 */
	protected $guarded = [
		'id'
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var  array<string,string>
	 */
	protected $casts = [
		'datetimestart' => 'datetime:Y-m-d H:i:s',
		'datetimestop' => 'datetime:Y-m-d H:i:s',
	];

	/**
	 * The event map for the model.
	 *
	 * @var array<string,string>
	 */
	protected $dispatchesEvents = [
		'created' => QueueSizeCreated::class,
		'updated' => QueueSizeUpdated::class,
		'deleted' => QueueSizeDeleted::class,
	];

	/**
	 * Determine if in a trashed state
	 *
	 * @return  bool
	 */
	public function hasStart()
	{
		return !is_null($this->datetimestart);
	}

	/**
	 * Determine if in a trashed state
	 *
	 * @return  bool
	 */
	public function hasStarted()
	{
		// No start time means start immediately
		if (!$this->hasStart())
		{
			return true;
		}
		return ($this->datetimestart->timestamp < Carbon::now()->timestamp);
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
	 * Determine if in a trashed state
	 *
	 * @return  bool
	 */
	public function hasEnd()
	{
		return !is_null($this->datetimestop);
	}

	/**
	 * Determine if in a trashed state
	 *
	 * @return  bool
	 */
	public function hasEnded()
	{
		return ($this->hasEnd() && $this->datetimestop->timestamp < Carbon::now()->timestamp);
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
	private function calculateTimeLeft($start)
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
	 * Defines a relationship to queue
	 *
	 * @return  object
	 */
	public function queue()
	{
		return $this->belongsTo(Queue::class, 'queueid');
	}

	/**
	 * Defines a relationship to seller queue
	 *
	 * @return  object
	 */
	public function seller()
	{
		return $this->belongsTo(Queue::class, 'sellerqueueid');
	}

	/**
	 * Defines a relationship to seller
	 *
	 * @return  object
	 */
	public function source()
	{
		return $this->belongsTo(Queue::class, 'sellerqueueid');
	}

	/**
	 * Get type
	 *
	 * @return  integer
	 */
	public function getTypeAttribute()
	{
		return 0;
	}
}
