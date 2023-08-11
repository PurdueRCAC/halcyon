<?php

namespace App\Modules\Messages\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use App\Modules\Messages\Events\MessageCreating;
use App\Modules\Messages\Events\MessageCreated;
use App\Modules\Messages\Events\MessageUpdating;
use App\Modules\Messages\Events\MessageUpdated;
use App\Modules\Messages\Events\MessageDeleted;
use App\Modules\Messages\Events\MessageReading;
use App\Modules\Messages\Database\Factories\MessageFactory;
use Carbon\Carbon;

/**
 * Message queue message
 *
 * @property int    $id
 * @property int    $userid
 * @property int    $messagequeuetypeid
 * @property int    $targetobjectid
 * @property int    $messagequeueoptionsid
 * @property Carbon|null $datetimesubmitted
 * @property Carbon|null $datetimestarted
 * @property Carbon|null $datetimecompleted
 * @property int    $pid
 * @property int    $returnstatus
 * @property string $output
 */
class Message extends Model
{
	use HasFactory;

	/**
	 * The name of the "created at" column.
	 *
	 * @var string|null
	 */
	const CREATED_AT = 'datetimesubmitted';

	/**
	 * The name of the "updated at" column.
	 *
	 * @var string|null
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
	 * The attributes that should be cast to native types.
	 *
	 * @var  array<string,string>
	 */
	protected $casts = [
		'datetimesubmitted' => 'datetime:Y-m-d H:i:s',
		'datetimestarted' => 'datetime:Y-m-d H:i:s',
		'datetimecompleted' => 'datetime:Y-m-d H:i:s',
	];

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
	 * @return MessageFactory
	 */
	protected static function newFactory(): MessageFactory
	{
		return new MessageFactory;
	}

	/**
	 * Set messagequeuetypeid
	 *
	 * @param   mixed  $value
	 * @return  void
	 */
	public function setMessagequeuetypeidAttribute($value): void
	{
		$this->attributes['messagequeuetypeid'] = (int)$value;
	}

	/**
	 * Set targetobjectid
	 *
	 * @param   mixed  $value
	 * @return  void
	 */
	public function setTargetobjectidAttribute($value): void
	{
		$this->attributes['targetobjectid'] = $this->stringToInteger($value);
	}

	/**
	 * Set userid
	 *
	 * @param   mixed  $value
	 * @return  void
	 */
	public function setUseridAttribute($value): void
	{
		$this->attributes['userid'] = $this->stringToInteger($value);
	}

	/**
	 * Set messagequeueoptionsid
	 *
	 * @param   mixed  $value
	 * @return  void
	 */
	public function setMessagequeueoptionsidAttribute($value): void
	{
		$this->attributes['messagequeueoptionsid'] = $this->stringToInteger($value);
	}

	/**
	 * Convert [!] Legacy string IDs to integers
	 *
	 * @param   mixed  $value
	 * @return  int
	 */
	private function stringToInteger($value): int
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
	public function started(): bool
	{
		return !is_null($this->datetimestarted);
	}

	/**
	 * Determine if completed
	 *
	 * @return  bool
	 */
	public function completed(): bool
	{
		return !is_null($this->datetimecompleted);
	}

	/**
	 * Defines a relationship to a type
	 *
	 * @return  BelongsTo
	 */
	public function type(): BelongsTo
	{
		return $this->belongsTo(Type::class, 'messagequeuetypeid');
	}

	/**
	 * Save model data
	 *
	 * @param   array  $options
	 * @return  bool
	 * @throws  \Exception
	 */
	public function save(array $options = array()): bool
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
	public function getElapsedAttribute(): string
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
	private function diffForHumans($start, $end = null, $unit = null): string
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
	 * Get the message target object
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
	public function getStatusAttribute(): string
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
	public function getRuntimeAttribute(): string
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
	 * @param   Builder  $query
	 * @param   string  $since
	 * @return  Builder
	 */
	public function scopeWhereStarted($query, $since = null): Builder
	{
		return $query->where(function($where) use ($since)
		{
			$where->whereNotNull('datetimestarted')
				->whereNull('datetimecompleted');

			if ($since)
			{
				$where->where('datetimestarted', '>', $since);
			}
		});
	}

	/**
	 * Query for items that haven't started
	 *
	 * @param   Builder  $query
	 * @return  Builder
	 */
	public function scopeWhereNotStarted(Builder $query): Builder
	{
		return $query->whereNull('datetimestarted')
					->whereNull('datetimecompleted');
	}

	/**
	 * Query for completed items
	 *
	 * @param   Builder  $query
	 * @param   string  $since
	 * @return  Builder
	 */
	public function scopeWhereCompleted(Builder $query, $since = null): Builder
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
	 * @param   Builder  $query
	 * @return  Builder
	 */
	public function scopeWhereNotCompleted(Builder $query): Builder
	{
		return $query->whereNull('datetimecompleted');
	}

	/**
	 * Query where return status == 0
	 *
	 * @param   Builder  $query
	 * @return  Builder
	 */
	public function scopeWhereSuccessful(Builder $query): Builder
	{
		return $query->where('returnstatus', '=', 0);
	}

	/**
	 * Query where return status > 0
	 *
	 * @param   Builder  $query
	 * @return  Builder
	 */
	public function scopeWhereNotSuccessful(Builder $query): Builder
	{
		return $query->where('returnstatus', '>', 0);
	}

	/**
	 * Query where return status is ...
	 *
	 * @param   Builder  $query
	 * @param   mixed  $status
	 * @return  Builder
	 */
	public function scopeWhereStatus(Builder $query, $status): Builder
	{
		if (is_numeric($status) && $status >= 0)
		{
			$query->where('returnstatus', '=', $status);
		}
		else
		{
			if ($status == 'success')
			{
				$query->whereSuccessful();
			}
			elseif ($status == 'failure')
			{
				$query->whereNotSuccessful();
			}
		}

		return $query;
	}

	/**
	 * Query where state
	 *
	 * @param   Builder  $query
	 * @param   string   $state
	 * @return  Builder
	 */
	public function scopeWhereState(Builder $query, $state): Builder
	{
		if ($state == 'complete')
		{
			$query->whereCompleted();
		}
		elseif ($state == 'incomplete')
		{
			$query->whereNotCompleted();
		}
		elseif ($state == 'pending')
		{
			$query->whereNotStarted();
		}

		return $query;
	}
}
