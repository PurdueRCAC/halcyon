<?php

namespace App\Modules\Messages\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Modules\History\Traits\Historable;
use App\Modules\Messages\Events\MessageCreating;
use App\Modules\Messages\Events\MessageCreated;
use App\Modules\Messages\Events\MessageUpdating;
use App\Modules\Messages\Events\MessageUpdated;
use App\Modules\Messages\Events\MessageDeleted;
use App\Modules\Messages\Events\MessageReading;
use App\Modules\Messages\Database\Factories\MessageFactory;
use Carbon\Carbon;

class Message extends Model
{
	use Historable, HasFactory;

	/**
	 * The name of the "created at" column.
	 *
	 * @var string
	 */
	const CREATED_AT = 'datetimesubmitted';

	/**
	 * The name of the "updated at" column.
	 *
	 * @var string
	 */
	const UPDATED_AT = null;

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'messagequeue';

	/**
	 * Fillable
	 *
	 * @var array<int,string>
	 */
	protected $guarded = array(
		'id',
		'datetimesubmitted'
	);

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'datetimesubmitted';

	/**
	 * Default order direction for select queries
	 *
	 * @var string
	 */
	public static $orderDir = 'desc';

	/**
	 * Automatic fields to populate every time a row is created
	 *
	 * @var array<int,string>
	 */
	protected $dates = array(
		'datetimesubmitted',
		'datetimestarted',
		'datetimecompleted'
	);

	/**
	 * Fields and their validation criteria
	 *
	 * @var array<string,string>
	 */
	protected $rules = array(
		'messagequeuetypeid' => 'required|integer',
		'targetobjectid' => 'required|integer',
	);

	/**
	 * The event map for the model.
	 *
	 * @var array<string,string>
	 */
	protected $dispatchesEvents = [
		'creating' => MessageCreating::class,
		'created'  => MessageCreated::class,
		'updating' => MessageUpdating::class,
		'updated'  => MessageUpdated::class,
		'deleted'  => MessageDeleted::class,
	];

	/**
	 * Create a new factory instance for the model.
	 *
	 * @return \Illuminate\Database\Eloquent\Factories\Factory
	 */
	protected static function newFactory()
	{
		return new MessageFactory;
	}

	/**
	 * Set messagequeuetypeid
	 *
	 * @param   mixed  $value
	 * @return  void
	 */
	public function setMessagequeuetypeidAttribute($value)
	{
		$this->attributes['messagequeuetypeid'] = (int)$value;
	}

	/**
	 * Set targetobjectid
	 *
	 * @param   mixed  $value
	 * @return  void
	 */
	public function setTargetobjectidAttribute($value)
	{
		$this->attributes['targetobjectid'] = $this->stringToInteger($value);
	}

	/**
	 * Set userid
	 *
	 * @param   mixed  $value
	 * @return  void
	 */
	public function setUseridAttribute($value)
	{
		$this->attributes['userid'] = $this->stringToInteger($value);
	}

	/**
	 * Set messagequeueoptionsid
	 *
	 * @param   mixed  $value
	 * @return  void
	 */
	public function setMessagequeueoptionsidAttribute($value)
	{
		$this->attributes['messagequeueoptionsid'] = $this->stringToInteger($value);
	}

	/**
	 * Convert [!] Legacy string IDs to integers
	 *
	 * @param   mixed  $value
	 * @return  integer
	 */
	private function stringToInteger($value)
	{
		if (is_string($value))
		{
			$value = preg_replace('/[a-zA-Z\/]+\/(\d+)/', "$1", $value);
		}

		return (int)$value;
	}

	/**
	 * Determine if started
	 *
	 * @return  bool
	 */
	public function started()
	{
		return !is_null($this->datetimestarted);
	}

	/**
	 * Determine if completed
	 *
	 * @return  bool
	 */
	public function completed()
	{
		return !is_null($this->datetimecompleted);
	}

	/**
	 * Defines a relationship to a type
	 *
	 * @return  object
	 */
	public function type()
	{
		return $this->belongsTo(Type::class, 'messagequeuetypeid');
	}

	/**
	 * Save model data
	 *
	 * @param   array  $options
	 * @return  bool
	 */
	public function save(array $options = array())
	{
		if ($this->messagequeuetypeid != $this->getOriginal('messagequeuetypeid'))
		{
			if (!$this->type)
			{
				throw new \Exception('Invalid messagequeuetypeid');
			}
		}

		if (!$this->id)
		{
			if (!$this->userid)
			{
				$this->userid = auth()->user() ? auth()->user()->id : 0;
			}

			$this->datetimesubmitted = Carbon::now()->toDateTimeString();
		}

		return parent::save($options);
	}

	/**
	 * Get elapsed time in a human readable format
	 *
	 * @return  string
	 */
	public function getElapsedAttribute()
	{
		return $this->diffForHumans($this->datetimestarted, $this->datetimecompleted);
	}

	/**
	 * Return time difference in human readable format
	 *
	 * @param   string  $start  Start time
	 * @param   string  $end    End time
	 * @param   string  $unit   If a specific time unit is desired (e.g., seconds), name the unit [seconds, minutes, hours, days, weeks, months]
	 * @return  string
	 */
	private function diffForHumans($start, $end = null, $unit = null)
	{
		if (!$start)
		{
			return trans('messages::messages.na');
		}

		if (!$end)
		{
			// Get now
			$end = Carbon::now();
		}

		// Get the difference in seconds between now and the time
		$diff = strtotime($end) - strtotime($start);
		$diff = abs($diff);

		// Less than a minute
		if ($diff < 60 || $unit == 'seconds')
		{
			return trans_choice('messages::messages.time.seconds', $diff, ['value' => $diff]);
		}

		// Round to minutes
		$diff = round($diff / 60);

		// 1 to 59 minutes
		if ($diff < 60 || $unit == 'minutes')
		{
			return trans_choice('messages::messages.time.minutes', $diff, ['value' => $diff]);
		}

		// Round to hours
		$diff = round($diff / 60);

		// 1 to 23 hours
		if ($diff < 24 || $unit == 'hours')
		{
			return trans_choice('messages::messages.time.hours', $diff, ['value' => $diff]);
		}

		// Round to days
		$diff = round($diff / 24);

		// 1 to 6 days
		if ($diff < 7 || $unit == 'days')
		{
			return trans_choice('messages::messages.time.days', $diff, ['value' => $diff]);
		}

		// Round to weeks
		$diff = round($diff / 7);

		// 1 to 4 weeks
		if ($diff <= 4 || $unit == 'weeks')
		{
			return trans_choice('messages::messages.time.weeks', $diff, ['value' => $diff]);
		}

		// Round to months
		$diff = round($diff / 4);

		// 1 to 12 months
		if ($diff <= 12 || $unit == 'months')
		{
			return trans_choice('messages::messages.time.months', $diff, ['value' => $diff]);
		}

		// > 12 months
		return trans_choice('messages::messages.time.years', $diff, ['value' => $diff]);
	}

	/**
	 * Defines a relationship to a subresource
	 *
	 * @return  object
	 */
	public function getTargetAttribute()
	{
		event($event = new MessageReading($this));

		return $event->target;
	}

	/**
	 * Get message status
	 *
	 * @return  string
	 */
	public function getStatusAttribute()
	{
		$now = Carbon::now();

		$status = trans('global.unknown');

		if ($this->datetimesubmitted <= $now)
		{
			if (!$this->started() && !$this->completed())
			{
				$status = 'queued';
			}
			else
			{
				if (!$this->completed())
				{
					$status = 'running';
				}
				else
				{
					$status = 'completed';

					if ($this->returnstatus && date("U") - $this->datetimecompleted->timestamp < 1209600)
					{
						$status = 'error';
					}
					elseif ($this->returnstatus)
					{
						$status = 'error';
					}
				}
			}
		}
		else
		{
			$status = 'deferred';
		}

		return $status;
	}

	/**
	 * Get runtime
	 *
	 * @return  string
	 */
	public function getRuntimeAttribute()
	{
		if (!$this->completed())
		{
			// Get now
			$end = Carbon::now()->timestamp;
		}
		else
		{
			$end = strtotime($this->datetimecompleted);
		}

		$start = strtotime($this->datetimestarted);

		// Get the difference in seconds between now and the time
		$diff = $end - $start;

		// Less than a minute
		if ($diff < 60)
		{
			return trans_choice('messages::messages.time.seconds', $diff, ['value' => $diff]);
		}

		// Round to minutes
		$diff = round($diff / 60);

		// 1 to 59 minutes
		if ($diff < 60)
		{
			return trans_choice('messages::messages.time.minutes', $diff, ['value' => $diff]);
		}

		// Round to hours
		$diff = round($diff / 60);

		// 1 to 23 hours
		if ($diff < 24)
		{
			return trans_choice('messages::messages.time.hours', $diff, ['value' => $diff]);
		}

		// Round to days
		$diff = round($diff / 24);

		// 1 to 6 days
		if ($diff < 7)
		{
			return trans_choice('messages::messages.time.days', $diff, ['value' => $diff]);
		}

		// Round to weeks
		$diff = round($diff / 7);

		// 1 to 4 weeks
		if ($diff <= 4)
		{
			return trans_choice('messages::messages.time.weeks', $diff, ['value' => $diff]);
		}

		// Round to months
		$diff = round($diff / 4);

		// 1 to 12 months
		if ($diff <= 12)
		{
			return trans_choice('messages::messages.time.months', $diff, ['value' => $diff]);
		}

		// > 12 months
		return trans_choice('messages::messages.time.years', $diff, ['value' => $diff]);
	}

	/**
	 * Query for started items
	 *
	 * @param   object  $query
	 * @param   string  $since
	 * @return  object
	 */
	public function scopeWhereStarted($query, $since = null)
	{
		return $query->where(function($where) use ($since)
		{
			$where->whereNotNull('datetimestarted');

			if ($since)
			{
				$where->where('datetimestarted', '>', $since);
			}
		});
	}

	/**
	 * Query for items that haven't started
	 *
	 * @param   object  $query
	 * @return  object
	 */
	public function scopeWhereNotStarted($query)
	{
		return $query->whereNull('datetimestarted');
	}

	/**
	 * Query for completed items
	 *
	 * @param   object  $query
	 * @param   string  $since
	 * @return  object
	 */
	public function scopeWhereCompleted($query, $since = null)
	{
		return $query->where(function($where) use ($since)
		{
			$where->whereNotNull('datetimecompleted');

			if ($since)
			{
				$where->where('datetimecompleted', '>', $since);
			}
		});
	}

	/**
	 * Query for incomplete items
	 *
	 * @param   object  $query
	 * @return  object
	 */
	public function scopeWhereNotCompleted($query)
	{
		return $query->whereNull('datetimecompleted');
	}

	/**
	 * Query where return status == 0
	 *
	 * @param   object  $query
	 * @return  object
	 */
	public function scopeWhereSuccessful($query)
	{
		return $query->where('returnstatus', '=', 0);
	}

	/**
	 * Query where resturn status > 0
	 *
	 * @param   object  $query
	 * @return  object
	 */
	public function scopeWhereNotSuccessful($query)
	{
		return $query->where('returnstatus', '>', 0);
	}
}
