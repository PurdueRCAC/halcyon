<?php
namespace App\Modules\Queues\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * Model for a queue loan
 */
class Loan extends Model
{
	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'queueloans';

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var  array
	 */
	protected $dates = [
		'datetimestart',
		'datetimestop',
	];

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [
		'id'
	];

	/**
	 * The event map for the model.
	 *
	 * @var array
	 */
	/*protected $dispatchesEvents = [
		'created'  => LoanCreated::class,
		'updated'  => LoanUpdated::class,
		'deleted'  => LoanDeleted::class,
	];*/

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
	public function hasEnd()
	{
		return !is_null($this->datetimestop);
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

		$inputSeconds = $this->datetimestart->timestamp - Carbon::now()->timestamp;

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

		$inputSeconds = $this->datetimestop->timestamp - Carbon::now()->timestamp;

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
	 * Defines a relationship to a queue
	 *
	 * @return  object
	 */
	public function queue()
	{
		return $this->belongsTo(Queue::class, 'queueid');
	}

	/**
	 * Defines a relationship to a lender
	 *
	 * @return  object
	 */
	public function lender()
	{
		return $this->belongsTo(Queue::class, 'lenderqueueid');
	}

	/**
	 * Defines a relationship to a lender
	 *
	 * @return  object
	 */
	public function source()
	{
		return $this->belongsTo(Queue::class, 'lenderqueueid');
	}

	/**
	 * Get type
	 *
	 * @return  integer
	 */
	public function getTypeAttribute()
	{
		return 1;
	}
}
