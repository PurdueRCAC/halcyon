<?php
namespace App\Modules\Queues\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;
use App\Halcyon\Models\Casts\Bytesize;
use App\Modules\History\Traits\Historable;
use App\Modules\Queues\Events\QueueCreating;
use App\Modules\Queues\Events\QueueCreated;
use App\Modules\Queues\Events\QueueUpdating;
use App\Modules\Queues\Events\QueueUpdated;
use App\Modules\Queues\Events\QueueDeleted;
use App\Modules\Queues\Database\Factories\QueueFactory;
use App\Modules\Resources\Models\Subresource;
use App\Modules\Resources\Models\Child;
use App\Modules\Resources\Models\Asset;
use App\Modules\Resources\Events\ResourceMemberCreated;
use App\Modules\Resources\Events\ResourceMemberStatus;
use App\Modules\Resources\Events\ResourceMemberDeleted;
use App\Modules\Groups\Models\Group;
use App\Modules\Users\Models\UserUsername;
use Carbon\Carbon;

/**
 * Queue queue
 *
 * @property int    $id
 * @property int    $schedulerid
 * @property int    $subresourceid
 * @property string $name
 * @property int    $groupid
 * @property int    $queuetype
 * @property int    $automatic
 * @property int    $free
 * @property int    $schedulerpolicyid
 * @property int    $enabled
 * @property int    $started
 * @property int    $reservation
 * @property string $cluster
 * @property int    $priority
 * @property int    $defaultwalltime
 * @property int    $maxjobsqueued
 * @property int    $maxjobsqueueduser
 * @property int    $maxjobsrun
 * @property int    $maxjobsrunuser
 * @property int    $maxjobcores
 * @property int    $nodecoresdefault
 * @property int    $nodecoresmin
 * @property int    $nodecoresmax
 * @property int    $nodememmin
 * @property int    $nodememmax
 * @property int    $aclusersenabled
 * @property string $aclgroups
 * @property Carbon|null $datetimecreated
 * @property Carbon|null $datetimeremoved
 * @property Carbon|null $datetimelastseen
 * @property int    $maxijobfactor
 * @property int    $maxijobuserfactor
 * @property int    $shared
 *
 * @property string $api
 */
class Queue extends Model
{
	use Historable, SoftDeletes, HasFactory;

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
	protected $table = 'queues';

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'name';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public static $orderDir = 'asc';

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array<string,string>
	 */
	protected $casts = [
		'nodememmin' => Bytesize::class,
		'nodememmax' => Bytesize::class,
	];

	/**
	 * The model's default values for attributes.
	 *
	 * @var array<string,int>
	 */
	protected $attributes = [
		'groupid' => 0,
		'nodecoresmin' => 0,
		'nodecoresmax' => 0,
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
	 * The event map for the model.
	 *
	 * @var array<string,string>
	 */
	protected $dispatchesEvents = [
		'creating' => QueueCreating::class,
		'created'  => QueueCreated::class,
		'updating' => QueueUpdating::class,
		'updated'  => QueueUpdated::class,
		'deleted'  => QueueDeleted::class,
	];

	/**
	 * Create a new factory instance for the model.
	 *
	 * @return \Illuminate\Database\Eloquent\Factories\Factory
	 */
	protected static function newFactory()
	{
		return new QueueFactory;
	}

	/**
	 * The "booted" method of the model.
	 *
	 * @return void
	 */
	protected static function booted(): void
	{
		// Clean up allocations
		static::deleted(function ($model)
		{
			$now = Carbon::now()->toDateTimeString();

			// End any purchases
			$model->sizes()
				->whereNull('datetimestop')
				->update(['datetimestop' => $now]);

			// Only stop counter entries associated with purchases. If this queue
			// sold something to another queue and it's still active, the sale should
			// persist.
			$model->sold()
				->whereNull('datetimestop')
				->where('corecount', '<', 0)
				->update(['datetimestop' => $now]);

			// End any loans
			$model->loans()
				->whereNull('datetimestop')
				->update(['datetimestop' => $now]);

			$model->loaned()
				->whereNull('datetimestop')
				->update(['datetimestop' => $now]);
		});
	}

	/**
	 * Determine if entry has an end time
	 *
	 * @return  bool
	 */
	public function hasLastSeenTime(): bool
	{
		return !is_null($this->datetimelastseen);
	}

	/**
	 * Set defaultwalltime. Incoming value is expected to be # hours.
	 *
	 * @param   int  $value
	 * @return  void
	 */
	public function setDefaultwalltimeAttribute($value): void
	{
		$this->attributes['defaultwalltime'] = $value * 60 * 60;
	}

	/**
	 * Set maxwalltime. Incoming value is expected to be # hours.
	 *
	 * @param   int  $value
	 * @return  void
	 */
	public function setMaxwalltimeAttribute($value): void
	{
		$this->attributes['maxwalltime'] = $value * 60 * 60;
	}

	/**
	 * Set nodecoresmin.
	 *
	 * @param   int  $value
	 * @return  void
	 */
	public function setNodecoresminAttribute($value): void
	{
		if (!is_numeric($value) || $value < 0)
		{
			$value = 0;
		}

		$this->attributes['nodecoresmin'] = $value;
	}

	/**
	 * Set nodecoresmax.
	 *
	 * @param   int  $value
	 * @return  void
	 */
	public function setNodecoresmaxAttribute($value): void
	{
		if (!is_numeric($value) || $value < 0)
		{
			$value = 0;
		}

		$this->attributes['nodecoresmax'] = $value;
	}

	/**
	 * Set nodememmin. Value is expected to be numeric followed by letter. Ex: 64G
	 *
	 * @param   string  $value
	 * @return  void
	 */
	/*public function setNodememminAttribute($value): void
	{
		if (!preg_match('/^[0-9]+[BKMGTP]$/', $value))
		{
			$value = 0;
		}

		$this->attributes['nodememmin'] = $value;
	}*/

	/**
	 * Set nodememmax. Value is expected to be numeric followed by letter. Ex: 64G
	 *
	 * @param   string  $value
	 * @return  void
	 */
	/*public function setNodememmaxAttribute($value): void
	{
		if (!preg_match('/^[0-9]+[BKMGTP]$/', $value))
		{
			$value = 0;
		}

		$this->attributes['nodememmax'] = $value;
	}*/

	/**
	 * Defines a relationship to type
	 *
	 * @return  BelongsTo
	 */
	public function type(): BelongsTo
	{
		return $this->belongsTo(Type::class, 'queuetype')->withDefault(['id' => 0, 'name' => trans('global.none')]);
	}

	/**
	 * Defines a relationship to group
	 *
	 * @return  BelongsTo
	 */
	public function group(): BelongsTo
	{
		return $this->belongsTo(Group::class, 'groupid');
	}

	/**
	 * Defines a relationship to schedulers
	 *
	 * @return  BelongsTo
	 */
	public function scheduler(): BelongsTo
	{
		return $this->belongsTo(Scheduler::class, 'schedulerid');
	}

	/**
	 * Defines a relationship to subqueues
	 *
	 * @return  BelongsTo
	 */
	public function schedulerPolicy(): BelongsTo
	{
		return $this->belongsTo(SchedulerPolicy::class, 'schedulerpolicyid');
	}

	/**
	 * Defines a relationship to subresource
	 *
	 * @return  BelongsTo
	 */
	public function subresource(): BelongsTo
	{
		return $this->belongsTo(Subresource::class, 'subresourceid')->withTrashed();
		//return $this->hasOneThrough(Subresource::class, Child::class, 'subresourceid', 'id', 'subresourceid', 'subresourceid');
	}

	/**
	 * Get the resource
	 *
	 * @return  HasOneThrough
	 */
	public function resource(): HasOneThrough
	{
		return $this->hasOneThrough(Asset::class, Child::class, 'subresourceid', 'id', 'subresourceid', 'resourceid')->withTrashed();
	}

	/**
	 * Defines a direct relationship to queues
	 *
	 * @return  HasManyThrough
	 */
	public function qos(): HasManyThrough
	{
		return $this->hasManyThrough(Qos::class, QueueQos::class, 'queueid', 'id', 'id', 'qosid');
	}

	/**
	 * Defines a relationship to queue qos map
	 *
	 * @return  HasMany
	 */
	public function queueqoses(): HasMany
	{
		return $this->hasMany(QueueQos::class, 'queueid');
	}

	/**
	 * Defines a relationship to sizes (purchases)
	 *
	 * @return  HasMany
	 */
	public function sizes(): HasMany
	{
		return $this->hasMany(Size::class, 'queueid');
	}

	/**
	 * Defines a relationship to sizes where the queue is the seller
	 *
	 * @return  HasMany
	 */
	public function sold(): HasMany
	{
		return $this->hasMany(Size::class, 'sellerqueueid');
	}

	/**
	 * Defines a relationship to loans
	 *
	 * @return  HasMany
	 */
	public function loans(): HasMany
	{
		return $this->hasMany(Loan::class, 'queueid');
	}

	/**
	 * Defines a relationship to loans where the queue is the lender
	 *
	 * @return  HasMany
	 */
	public function loaned(): HasMany
	{
		return $this->hasMany(Loan::class, 'lenderqueueid');
	}

	/**
	 * Defines a relationship to users
	 *
	 * @return  HasMany
	 */
	public function users(): HasMany
	{
		return $this->hasMany(User::class, 'queueid');
	}

	/**
	 * Defines a relationship to users
	 *
	 * @return  Collection
	 */
	public function getActiveUsersAttribute(): Collection
	{
		$now = Carbon::now();
		$u = (new UserUsername)->getTable();
		$qu = (new User)->getTable();

		return $this->users()
			->select($qu . '.*')
			->join($u, $u . '.userid', $qu . '.userid')
			->whereNull($u . '.dateremoved')
			->where($qu . '.datetimecreated', '<', $now->toDateTimeString())
			->orderBy($qu . '.datetimecreated', 'desc')
			->get();
	}

	/**
	 * Defines a relationship to walltimes
	 *
	 * @return  HasMany
	 */
	public function walltimes(): HasMany
	{
		return $this->hasMany(Walltime::class, 'queueid');
	}

	/**
	 * Get active
	 *
	 * @return  int
	 */
	public function getActiveAttribute(): int
	{
		if (!array_key_exists('active', $this->attributes))
		{
			$this->sumCoresAndNodes();
		}

		return $this->attributes['active'];
	}

	/**
	 * Get the next upcoming purchase or loan
	 *
	 * @return  mixed
	 */
	public function getUpcomingLoanOrPurchase()
	{
		$now = Carbon::now();

		$purchase = $this->sizes()
			->where(function($where) use ($now)
			{
				$where->whereNull('datetimestop')
					->orWhere('datetimestop', '>', $now->toDateTimeString());
			})
			->where('datetimestart', '>', $now->toDateTimeString())
			->orderBy('datetimestart', 'asc')
			->first();

		$loan = $this->loans()
			->where(function($where) use ($now)
			{
				$where->whereNull('datetimestop')
					->orWhere('datetimestop', '>', $now->toDateTimeString());
			})
			->where('datetimestart', '>', $now->toDateTimeString())
			->orderBy('datetimestart', 'asc')
			->first();

		if ($purchase)
		{
			if (!$loan || $purchase->datetimestart < $loan->datetimestart)
			{
				return $purchase;
			}
		}

		if ($loan)
		{
			if (!$purchase || $loan->datetimestart < $purchase->datetimestart)
			{
				return $loan;
			}
		}

		return null;
	}

	/**
	 * Get the next upcoming purchase or loan
	 *
	 * @return  Carbon|null
	 */
	public function getAllocationStart()
	{
		$now = Carbon::now();

		$purchase = $this->sizes()
			->where(function($where) use ($now)
			{
				$where->whereNull('datetimestop')
					->orWhere('datetimestop', '>', $now->toDateTimeString());
			})
			//->where('datetimestart', '>', $now->toDateTimeString())
			->orderBy('datetimestart', 'asc')
			->first();

		$loan = $this->loans()
			->where(function($where) use ($now)
			{
				$where->whereNull('datetimestop')
					->orWhere('datetimestop', '>', $now->toDateTimeString());
			})
			//->where('datetimestart', '>', $now->toDateTimeString())
			->orderBy('datetimestart', 'asc')
			->first();

		if ($purchase)
		{
			if (!$loan || $purchase->datetimestart < $loan->datetimestart)
			{
				return $purchase->datetimestart;
			}
		}

		if ($loan)
		{
			if (!$purchase || $loan->datetimestart < $purchase->datetimestart)
			{
				return $loan->datetimestart;
			}
		}

		return null;
	}

	/**
	 * Calculate total cores and nodes
	 *
	 * @return  void
	 */
	private function sumCoresAndNodes()
	{
		$nodecores = $this->subresource ? $this->subresource->nodecores : 0;
		$active = 0;

		$totalcores  = 0;
		$totalnodes  = 0;
		$soldcores   = 0;
		$soldnodes   = 0;
		$loanedcores = 0;
		$loanednodes = 0;
		$serviceunits = 0.00;

		$now = Carbon::now();
		$soon = $now->modify('+5 minutes');

		$purchases = $this->sizes()
			->where(function($where) use ($now)
			{
				$where->whereNull('datetimestop')
					->orWhere('datetimestop', '>', $now->toDateTimeString());
			})
			->where('datetimestart', '<=', $soon->toDateTimeString())
			->get();

		foreach ($purchases as $size)
		{
			if ($size->corecount < 0)
			{
				if ($size->serviceunits)
				{
					$size->serviceunits = -abs($size->serviceunits);
				}
				if ($size->nodecount)
				{
					$size->nodecount = -abs($size->nodecount);
				}
			}
			$serviceunits += (float)$size->serviceunits;

			$soldcores += (int) $size->corecount;

			if ($nodecores != 0)
			{
				$soldnodes += round($size->corecount / $nodecores, 1);
			}
			else
			{
				$soldnodes += $size->nodecount;
			}

			/*if ($size->corecount == 0)
			{
				$active = 1;
			}*/
		}

		$loans = $this->loans()
			->where(function($where) use ($now)
			{
				$where->whereNull('datetimestop')
					->orWhere('datetimestop', '>', $now->toDateTimeString());
			})
			->where('datetimestart', '<=', $soon->toDateTimeString())
			->get();

		foreach ($loans as $loan)
		{
			if ($loan->corecount < 0)
			{
				if ($loan->serviceunits)
				{
					$loan->serviceunits = -abs($loan->serviceunits);
				}
				if ($loan->nodecount)
				{
					$loan->nodecount = -abs($loan->nodecount);
				}
			}
			$serviceunits += (float)$loan->serviceunits;

			$loanedcores += (int) $loan->corecount;

			if ($nodecores != 0)
			{
				$loanednodes += round($loan->corecount / $nodecores, 1);
			}
			else
			{
				$loanednodes += $loan->nodecount;
			}

			/*if ($loan->corecount == 0)
			{
				$active = 1;
			}*/
		}

		$totalcores = $soldcores + $loanedcores;
		$totalnodes = $soldnodes + $loanednodes;

		// If we didn't get marked active by zero-core entry, set active if we have active noses
		//if ($active == 0 && ($totalcores > 0 || $serviceunits > 0))
		if ($totalcores > 0 || $serviceunits > 0)
		{
			$active = 1;
		}

		$this->setAttribute('active', $active);

		$this->setAttribute('totalcores', $totalcores);
		$this->setAttribute('totalnodes', $totalnodes);
		$this->setAttribute('soldcores', $soldcores);
		$this->setAttribute('soldnodes', $soldnodes);
		$this->setAttribute('loanedcores', $loanedcores);
		$this->setAttribute('loanednodes', $loanednodes);
		$this->setAttribute('serviceunits', $serviceunits);
	}

	/**
	 * Get total cores
	 *
	 * @return  int
	 */
	public function getTotalcoresAttribute(): int
	{
		if (!array_key_exists('totalcores', $this->attributes))
		{
			$this->sumCoresAndNodes();
		}

		return $this->attributes['totalcores'];
	}

	/**
	 * Get total nodes
	 *
	 * @return  int
	 */
	public function getTotalnodesAttribute(): int
	{
		if (!array_key_exists('totalnodes', $this->attributes))
		{
			$this->sumCoresAndNodes();
		}

		return $this->attributes['totalnodes'];
	}

	/**
	 * Get sold cores
	 *
	 * @return  int
	 */
	public function getSoldcoresAttribute(): int
	{
		if (!array_key_exists('soldcores', $this->attributes))
		{
			$this->sumCoresAndNodes();
		}

		return $this->attributes['soldcores'];
	}

	/**
	 * Get sold nodes
	 *
	 * @return  int
	 */
	public function getSoldnodesAttribute(): int
	{
		if (!array_key_exists('soldnodes', $this->attributes))
		{
			$this->sumCoresAndNodes();
		}

		return $this->attributes['soldnodes'];
	}

	/**
	 * Get loaned cores
	 *
	 * @return  int
	 */
	public function getLoanedcoresAttribute(): int
	{
		if (!array_key_exists('loanedcores', $this->attributes))
		{
			$this->sumCoresAndNodes();
		}

		return $this->attributes['loanedcores'];
	}

	/**
	 * Get loaned nodes
	 *
	 * @return  int
	 */
	public function getLoanednodesAttribute(): int
	{
		if (!array_key_exists('loanednodes', $this->attributes))
		{
			$this->sumCoresAndNodes();
		}

		return $this->attributes['loanednodes'];
	}

	/**
	 * Get service units
	 *
	 * @return  int
	 */
	public function getServiceunitsAttribute(): int
	{
		if (!array_key_exists('serviceunits', $this->attributes))
		{
			$this->sumCoresAndNodes();
		}

		return $this->attributes['serviceunits'];
	}

	/**
	 * Get walltime
	 *
	 * @return  int
	 */
	public function getWalltimeAttribute(): int
	{
		$walltime = 0;
		$now = Carbon::now();

		/*foreach ($this->walltimes as $w)
		{
			$walltime += $w->walltime;
		}*/
		
		$w = $this->walltimes()
			->where('datetimestart', '<=', $now->toDateTimeString())
			->where(function($where) use ($now)
			{
				$where->whereNull('datetimestop')
					->orWhere('datetimestop', '>', $now->toDateTimeString());
			})
			->orderBy('walltime', 'desc')
			->first();

		if ($w)
		{
			$walltime = (int)$w->walltime;
		}

		return $walltime;
	}

	/**
	 * Get default walltime in human readable format
	 *
	 * @return  string
	 */
	public function getDefaultHumanWalltimeAttribute(): string
	{
		$inputSeconds = $this->defaultwalltime;

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
	 * Get max walltime in human readable format
	 *
	 * @return  string
	 */
	public function getHumanWalltimeAttribute(): string
	{
		$inputSeconds = $this->walltime;

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
	 * Get the name appended with the subcluster
	 *
	 * @return  string
	 */
	public function getNameWithSubclusterAttribute(): string
	{
		$name = $this->name;

		if ($this->cluster
		 && $this->cluster != $this->name
		 && substr($name, -strlen('-' . $this->cluster)) != '-' . $this->cluster)
		{
			$name .= '-' . $this->cluster;
		}

		return $name;
	}

	/**
	 * Get the default QoS name
	 *
	 * @return  string
	 */
	public function getDefaultQosNameAttribute(): string
	{
		return ($this->isSystem() ? $this->name : $this->nameWithSubcluster) . '-default';
	}

	/**
	 * Is this a system queue?
	 *
	 * @return  bool
	 */
	public function isSystem(): bool
	{
		return ($this->groupid <= 0);
	}

	/**
	 * Is this a shared queue?
	 *
	 * @return  bool
	 */
	public function isShared(): bool
	{
		return ($this->shared > 0);
	}

	/**
	 * Is this an owner queue?
	 *
	 * @return  bool
	 */
	public function isOwner(): bool
	{
		return ($this->groupid > 0);
	}

	/**
	 * Stop scheduling
	 *
	 * @return  bool
	 */
	public function stop(): bool
	{
		return $this->update(['started' => 0]);
	}

	/**
	 * Stop scheduling
	 *
	 * @return  bool
	 */
	public function start(): bool
	{
		return $this->update(['started' => 1]);
	}

	/**
	 * Add a user
	 *
	 * @param   int  $userid
	 * @param   int  $membertype
	 * @param   int  $notice
	 * @return  bool
	 */
	public function addUser($userid, $membertype = 1, $notice = null): bool
	{
		$row = $this->users()
			->withTrashed()
			->where('userid', '=', $userid)
			->first();

		if ($row)
		{
			if ($row->trashed())
			{
				$row->restore();
			}
			// Nothing to do, we are cancelling a removal
			$row->notice = User::NO_NOTICE;
		}
		else
		{
			$row = new User;
			$row->queueid = $this->id;
			$row->userid = $userid;
			$row->membertype = $membertype;
			$row->notice = is_null($notice) ? User::NOTICE_REQUEST_GRANTED : $notice;
		}

		/*event($resourcemember = new ResourceMemberStatus($row->queue->scheduler->resource, $row->user));

		if ($resourcemember->noStatus() || $resourcemember->isPendingRemoval())
		{
			event($resourcemember = new ResourceMemberCreated($row->queue->scheduler->resource, $row->user));
		}*/

		return $row->save();
	}

	/**
	 * Remove a user
	 *
	 * @param   int  $userid
	 * @return  bool
	 */
	public function removeUser($userid): bool
	{
		$row = $this->users()
			->where('userid', '=', $userid)
			->first();

		if (!$row || !$row->id)
		{
			return true;
		}

		$res = $row->delete();

		event($resourcemember = new ResourceMemberStatus($this->scheduler->resource, $row->user));

		if ($resourcemember->isPending() || $resourcemember->isReady())
		{
			$rows = 0;

			$subresources = $this->scheduler->resource->subresources;

			foreach ($subresources as $sub)
			{
				$queues = $sub->queues()
					->get();

				foreach ($queues as $queue)
				{
					$rows += $queue->users()
						->whereIsMember()
						->where('userid', '=', $row->userid)
						->count();

					if ($queue->group)
					{
						$rows += $queue->group->members()
							->whereIsManager()
							->where('userid', '=', $row->userid)
							->count();
					}
				}
			}

			if ($rows == 0)
			{
				// No other active memberships found, remove resource access
				event(new ResourceMemberDeleted($this->scheduler->resource, $row->user));
			}
		}

		return $res;
	}


	/**
	 * Add loan
	 *
	 * @param   int $lenderqueueid
	 * @param   string  $start
	 * @param   string  $stop
	 * @param   int $nodecount
	 * @param   int $corecount
	 * @param   int $serviceunits
	 * @param   string  $comment
	 * @return  bool
	 */
	public function addLoan($lenderqueueid, $start, $stop = null, $nodecount = 0, $corecount = 0, $serviceunits = 0, $comment = null): bool
	{
		$row = new Loan;
		$row->queueid = $this->id;
		$row->lenderqueueid = $lenderqueueid;

		$row->datetimestart = Carbon::now();
		if ($start)
		{
			$row->datetimestart = Carbon::parse($start);
		}

		if ($stop)
		{
			$row->datetimestop = Carbon::parse($stop);
		}

		$row->nodecount = $nodecount;
		$row->corecount = $corecount;
		$row->serviceunits = (float)$serviceunits;

		if ($comment)
		{
			$row->comment = $comment;
		}

		return $row->save();
	}

	/**
	 * Add purchase
	 *
	 * @param   int $sellerqueueid
	 * @param   string  $start
	 * @param   string  $stop
	 * @param   int $nodecount
	 * @param   int $corecount
	 * @param   int $serviceunits
	 * @param   string  $comment
	 * @return  bool
	 */
	public function addPurchase($sellerqueueid, $start, $stop = null, $nodecount = 0, $corecount = 0, $serviceunits = 0, $comment = null): bool
	{
		$row = new Size;
		$row->queueid = $this->id;
		$row->sellerqueueid = $sellerqueueid;

		$row->datetimestart = Carbon::now();
		if ($start)
		{
			$row->datetimestart = Carbon::parse($start);
		}

		if ($stop)
		{
			$row->datetimestop = Carbon::parse($stop);
		}

		$row->nodecount = $nodecount;
		$row->corecount = $corecount;
		$row->serviceunits = (float)$serviceunits;

		if ($comment)
		{
			$row->comment = $comment;
		}

		// Does the queue have any cores yet?
		$count = Size::query()
			->where('queueid', '=', (int)$row->queueid)
			->orderBy('datetimestart', 'asc')
			->first();

		if (!$count)
		{
			// Have not been sold anything and never will have anything
			return false;
		}
		elseif ($count->datetimestart > $row->datetimestart)
		{
			// Have not been sold anything before this would start
			return false;
		}

		// Look for an existing entry in the same time frame and same queues to update instead
		$exist = Size::query()
			->where('queueid', '=', (int)$row->queueid)
			->where('sellerqueueid', '=', $row->sellerqueueid)
			->where('datetimestart', '=', $row->datetimestart)
			->where('datetimestop', '=', $row->datetimestop)
			->orderBy('datetimestart', 'asc')
			->first();

		if ($exist)
		{
			$exist->nodecount = $row->nodecount;
			$exist->corecount = $row->corecount;

			return $exist->save();
		}

		return $row->save();
	}

	/**
	 * Delete entry and associated data
	 *
	 * @return  bool
	 */
	public function delete(): bool
	{
		foreach ($this->users as $row)
		{
			$row->update(['notice' => User::NO_NOTICE]);
			$row->delete();

			// Look up the current username of the user being removed
			$user = $row->user;

			if (!$user)
			{
				continue;
			}

			// Look up the role name of the resource to which access is being granted.
			$resource = $this->resource;

			// Ensure the client is authorized to manage a group with queues on the resource in question.
			if (!auth()->user()->can('manage resources')
			&& $user->id != auth()->user()->id)
			{
				$owned = auth()->user()->groups->pluck('id')->toArray();

				$queues = array();
				$subresources = $resource->subresources;
				foreach ($subresources as $sub)
				{
					$queues += $sub->queues()
						->whereIn('groupid', $owned)
						->pluck('queuid')
						->toArray();
				}
				array_filter($queues);

				// If no queues found
				if (count($queues) < 1) // && !in_array($resource->id, array(48, 2, 12, 66)))
				{
					return false;
				}
			}
			else
			{
				$owned = $user->groups->pluck('id')->toArray();
			}

			// Check for other queue memberships on this resource that might conflict with removing the role
			$rows = 0;

			/*$resources = Asset::query()
				->where('rolename', '!=', '')
				->where('listname', '!=', '')
				->get();

			foreach ($resources as $res)
			{
				$subresources = $res->subresources;*/

				foreach ($resource->subresources as $sub)
				{
					$queues = $sub->queues()
						//->whereIn('groupid', $owned)
						->get();

					foreach ($queues as $queue)
					{
						$rows += $queue->users()
							->whereIsMember()
							->where('userid', '=', $user->id)
							->count();

						if (!$queue->group)
						{
							continue;
						}

						$rows += $queue->group->members()
							->whereIsManager()
							->where('userid', '=', $user->id)
							->count();
					}
				}
			//}

			if ($rows <= 0)
			{
				// Call to remove role from this user's account.
				event(new ResourceMemberDeleted($resource, $user));
			}
		}

		return parent::delete();
	}
}
