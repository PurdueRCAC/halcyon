<?php

namespace App\Modules\Messages\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Halcyon\Traits\ErrorBag;
use App\Halcyon\Traits\Validatable;
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
	use ErrorBag, Validatable, Historable, HasFactory;

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
	 * @var array
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
	 * @var array
	 */
	protected $dates = array(
		'datetimesubmitted',
		'datetimestarted',
		'datetimecompleted'
	);

	/**
	 * Fields and their validation criteria
	 *
	 * @var array
	 */
	protected $rules = array(
		'messagequeuetypeid' => 'required|integer',
		'targetobjectid' => 'required|integer',
	);

	/**
	 * The event map for the model.
	 *
	 * @var array
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
	 * Defines a relationship to a subresource
	 *
	 * @return  object
	 */
	public function type()
	{
		return $this->belongsTo(Type::class, 'messagequeuetypeid');
	}

	/**
	 * Defines a relationship to a subresource
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
				$this->addError('Invalid messagequeuetypeid');
				return false;
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
	 * Defines a relationship to a subresource
	 *
	 * @return  object
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
	 * @param   string  $unit   Time unit
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
			return $diff . ' second' . ($diff == 1 ? '' : 's');
		}

		// Round to minutes
		$diff = round($diff / 60);

		// 1 to 59 minutes
		if ($diff < 60 || $unit == 'minute')
		{
			return $diff . ' minute' . ($diff == 1 ? '' : 's');
		}

		// Round to hours
		$diff = round($diff / 60);

		// 1 to 23 hours
		if ($diff < 24 || $unit == 'hour')
		{
			return $diff . ' hour' . ($diff == 1 ? '' : 's');
		}

		// Round to days
		$diff = round($diff / 24);

		// 1 to 6 days
		if ($diff < 7 || $unit == 'day')
		{
			return $diff . ' day' . ($diff == 1 ? '' : 's');
		}

		// Round to weeks
		$diff = round($diff / 7);

		// 1 to 4 weeks
		if ($diff <= 4 || $unit == 'week')
		{
			return $diff . ' week' . ($diff == 1 ? '' : 's');
		}

		// Round to months
		$diff = round($diff / 4);

		// 1 to 12 months
		if ($diff <= 12 || $unit == 'month')
		{
			return $diff . ' month' . ($diff == 1 ? '' : 's');
		}

		// 1 to 12 months
		return $diff . ' year' . ($diff == 1 ? '' : 's');
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
			return $diff . ' ' . trans_choice('global.time.seconds', $diff);// $diff . ' second' . ($diff == 1 ? '' : 's');
		}

		// Round to minutes
		$diff = round($diff / 60);

		// 1 to 59 minutes
		if ($diff < 60)
		{
			return $diff . ' ' . trans_choice('global.time.minutes', $diff);//$diff . ' minute' . ($diff == 1 ? '' : 's');
		}

		// Round to hours
		$diff = round($diff / 60);

		// 1 to 23 hours
		if ($diff < 24)
		{
			return $diff . ' ' . trans_choice('global.time.hours', $diff);//$diff . ' hour' . ($diff == 1 ? '' : 's');
		}

		// Round to days
		$diff = round($diff / 24);

		// 1 to 6 days
		if ($diff < 7)
		{
			return $diff . ' ' . trans_choice('global.time.days', $diff);//$diff . ' day' . ($diff == 1 ? '' : 's');
		}

		// Round to weeks
		$diff = round($diff / 7);

		// 1 to 4 weeks
		if ($diff <= 4)
		{
			return $diff . ' ' . trans_choice('global.time.weeks', $diff);//$diff . ' week' . ($diff == 1 ? '' : 's');
		}

		// Round to months
		$diff = round($diff / 4);

		// 1 to 12 months
		if ($diff <= 12)
		{
			return $diff . ' ' . trans_choice('global.time.months', $diff);//$diff . ' month' . ($diff == 1 ? '' : 's');
		}
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
