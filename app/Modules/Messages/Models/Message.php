<?php

namespace App\Modules\Messages\Models;

use Illuminate\Database\Eloquent\Model;
use App\Halcyon\Traits\ErrorBag;
use App\Halcyon\Traits\Validatable;
use App\Modules\History\Traits\Historable;
use App\Modules\Messages\Events\MessageCreating;
use App\Modules\Messages\Events\MessageCreated;
use App\Modules\Messages\Events\MessageUpdating;
use App\Modules\Messages\Events\MessageUpdated;
use App\Modules\Messages\Events\MessageDeleted;
use App\Modules\Messages\Events\MessageReading;
use Carbon\Carbon;

class Message extends Model
{
	use ErrorBag, Validatable, Historable;

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
		'messagequeuetypeid' => 'required',
		'targetobjectid' => 'required',
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
		return ($this->datetimestarted && $this->datetimestarted != '0000-00-00 00:00:00' && $this->datetimestarted != '-0001-11-30 00:00:00');
	}

	/**
	 * Determine if completed
	 *
	 * @return  bool
	 */
	public function completed()
	{
		return ($this->datetimecompleted && $this->datetimecompleted != '0000-00-00 00:00:00' && $this->datetimecompleted != '-0001-11-30 00:00:00');
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
		if (!$start || $start == '0000-00-00 00:00:00' || $start == '-0001-11-30 00:00:00')
		{
			return trans('messages::messages.na');
		}

		if (!$end || $end == '0000-00-00 00:00:00' || $end == '-0001-11-30 00:00:00')
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
		/*$name = null;

		if ($this->type)
		{
			$cls = $this->type->classname;

			if ($cls == 'storagedir')
			{
				$cls = '\App\Modules\Storage\Models\Directory';
			}

			$item = $cls::find($this->targetobjectid);

			if ($item)
			{
				$name = $item->storageResource->path . '/' . $item->path;
			}
		}

		return $name;*/
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

	public function getRuntimeAttribute()
	{
		if (!$this->completed())
		{
			// Get now
			$end = Carbon::now();
			$end = $end->format('Y-m-d H:i:s');
		}

		$end = strtotime($this->datetimecompleted);
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
	 * Define a query scope
	 *
	 * @return  object
	 */
	public function scopeWhereStarted($query, $since = null)
	{
		return $query->where(function($where) use ($since)
		{
			$where->whereNotNull('datetimestarted')
				->where('datetimestarted', '!=', '0000-00-00 00:00:00');

			if ($since)
			{
				$where->where('datetimestarted', '>', $since);
			}
		});
	}

	/**
	 * Define a query scope
	 *
	 * @return  object
	 */
	public function scopeWhereNotStarted($query)
	{
		return $query->where(function($where) use ($since)
		{
			$where->whereNull('datetimestarted')
				->orWhere('datetimestarted', '=', '0000-00-00 00:00:00');
		});
	}

	/**
	 * Define a query scope
	 *
	 * @return  object
	 */
	public function scopeWhereCompleted($query, $since = null)
	{
		return $query->where(function($where) use ($since)
		{
			$where->whereNotNull('datetimecompleted')
				->where('datetimecompleted', '!=', '0000-00-00 00:00:00');

			if ($since)
			{
				$where->where('datetimecompleted', '>', $since);
			}
		});
	}

	/**
	 * Define a query scope
	 *
	 * @return  object
	 */
	public function scopeWhereNotCompleted($query)
	{
		return $query->where(function($where) use ($since)
		{
			$where->whereNull('datetimecompleted')
				->orWhere('datetimecompleted', '=', '0000-00-00 00:00:00');
		});
	}

	/**
	 * Define a query scope
	 *
	 * @return  object
	 */
	public function scopeWhereSuccessful($query)
	{
		return $query->where('returnstatus', '=', 0);
	}

	/**
	 * Define a query scope
	 *
	 * @return  object
	 */
	public function scopeWhereNotSuccessful($query)
	{
		return $query->where('returnstatus', '>', 0);
	}

	/**
	 * Forcefully reset a timestamp
	 *
	 * @return  void
	 */
	public function forceRestore($fields = array('datetimesubmitted'))
	{
		$fields = (array)$fields;

		// [!] Hackish workaround for resetting date fields
		//     that don't have a `null` default value.
		//     TODO: Change the table schema!
		try
		{
			ini_set('mysql.connect_timeout', '3');
			ini_set('output_buffering', '8192');

			$db = mysqli_init();
			$db->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
			$db->real_connect(
				config('database.connections.mysql.host'),
				config('database.connections.mysql.username'),
				config('database.connections.mysql.password'),
				config('database.connections.mysql.database')
			);

			foreach ($fields as $k => $field)
			{
				$fields[$k] = "`$field`='0000-00-00 00:00:00'";
			}
			$fields = implode(', ', $fields);

			$sql = "UPDATE " . $this->getTable() . " SET $fields WHERE `id`=" . $this->id;

			mysqli_query($db, $sql);
		}
		catch (\Exception $e)
		{
			// Do nothing
		}
	}
}
