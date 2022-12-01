<?php
namespace App\Modules\Queues\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Halcyon\Traits\ErrorBag;
use App\Halcyon\Traits\Validatable;
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
 */
class Queue extends Model
{
	use ErrorBag, Validatable, Historable, SoftDeletes, HasFactory;

	/**
	 * The name of the "created at" column.
	 *
	 * @var string
	 */
	const CREATED_AT = 'datetimecreated';

	/**
	 * The name of the "updated at" column.
	 *
	 * @var  string
	 */
	const UPDATED_AT = null;

	/**
	 * The name of the "deleted at" column.
	 *
	 * @var  string
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
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $casts = [
		'nodememmin' => Bytesize::class,
		'nodememmax' => Bytesize::class,
	];

	/**
	 * The model's default values for attributes.
	 *
	 * @var array
	 */
	protected $attributes = [
		'groupid' => 0,
		'nodecoresmin' => 0,
		'nodecoresmax' => 0,
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
	 * Determine if entry has an end time
	 *
	 * @return  bool
	 */
	public function hasLastSeenTime()
	{
		return !is_null($this->datetimelastseen);
	}

	/**
	 * Set defaultwalltime. Incoming value is expected to be # hours.
	 *
	 * @param   integer  $value
	 * @return  void
	 */
	public function setDefaultwalltimeAttribute($value)
	{
		$this->attributes['defaultwalltime'] = $value * 60 * 60;
	}

	/**
	 * Set maxwalltime. Incoming value is expected to be # hours.
	 *
	 * @param   integer  $value
	 * @return  void
	 */
	public function setMaxwalltimeAttribute($value)
	{
		$this->attributes['maxwalltime'] = $value * 60 * 60;
	}

	/**
	 * Set nodecoresmin.
	 *
	 * @param   integer  $value
	 * @return  void
	 */
	public function setNodecoresminAttribute($value)
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
	 * @param   integer  $value
	 * @return  void
	 */
	public function setNodecoresmaxAttribute($value)
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
	/*public function setNodememminAttribute($value)
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
	/*public function setNodememmaxAttribute($value)
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
	 * @return  object
	 */
	public function type()
	{
		return $this->belongsTo(Type::class, 'queuetype')->withDefault(['id' => 0, 'name' => trans('global.none')]);
	}

	/**
	 * Defines a relationship to group
	 *
	 * @return  object
	 */
	public function group()
	{
		return $this->belongsTo(Group::class, 'groupid');
	}

	/**
	 * Defines a relationship to schedulers
	 *
	 * @return  object
	 */
	public function scheduler()
	{
		return $this->belongsTo(Scheduler::class, 'schedulerid');
	}

	/**
	 * Defines a relationship to subqueues
	 *
	 * @return  object
	 */
	public function schedulerPolicy()
	{
		return $this->belongsTo(SchedulerPolicy::class, 'schedulerpolicyid');
	}

	/**
	 * Defines a relationship to subresource
	 *
	 * @return  object
	 */
	public function subresource()
	{
		return $this->belongsTo(Subresource::class, 'subresourceid')->withTrashed();
		//return $this->hasOneThrough(Subresource::class, Child::class, 'subresourceid', 'id', 'subresourceid', 'subresourceid');
	}

	/**
	 * Get the resource
	 */
	public function resource()
	{
		return $this->hasOneThrough(Asset::class, Child::class, 'subresourceid', 'id', 'subresourceid', 'resourceid')->withTrashed();
	}

	/**
	 * Defines a direct relationship to queues
	 *
	 * @return object
	 */
	public function qos()
	{
		return $this->hasManyThrough(Qos::class, QueueQos::class, 'queueid', 'id', 'id', 'qosid');
	}

	/**
	 * Defines a relationship to queue qos map
	 *
	 * @return  object
	 */
	public function queueqoses()
	{
		return $this->hasMany(QueueQos::class, 'queueid');
	}

	/**
	 * Defines a relationship to sizes (purchases)
	 *
	 * @return  object
	 */
	public function sizes()
	{
		return $this->hasMany(Size::class, 'queueid');
	}

	/**
	 * Defines a relationship to sizes where the queue is the seller
	 *
	 * @return  object
	 */
	public function sold()
	{
		return $this->hasMany(Size::class, 'sellerqueueid');
	}

	/**
	 * Defines a relationship to loans
	 *
	 * @return  object
	 */
	public function loans()
	{
		return $this->hasMany(Loan::class, 'queueid');
	}

	/**
	 * Defines a relationship to loans where the queue is the lender
	 *
	 * @return  object
	 */
	public function loaned()
	{
		return $this->hasMany(Loan::class, 'lenderqueueid');
	}

	/**
	 * Defines a relationship to users
	 *
	 * @return  object
	 */
	public function users()
	{
		return $this->hasMany(User::class, 'queueid');
	}

	/**
	 * Defines a relationship to users
	 *
	 * @return  object
	 */
	public function getActiveUsersAttribute()
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
	 * @return  object
	 */
	public function walltimes()
	{
		return $this->hasMany(Walltime::class, 'queueid');
	}

	/**
	 * Get active
	 *
	 * @return  integer
	 */
	public function getActiveAttribute()
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
	 * @return  mixed
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

		$purchases = $this->sizes()
			->where(function($where) use ($now)
			{
				$where->whereNull('datetimestop')
					->orWhere('datetimestop', '>', $now->toDateTimeString());
			})
			->where('datetimestart', '<=', $now->toDateTimeString())
			->get();

		foreach ($purchases as $size)
		{
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
			->where('datetimestart', '<=', $now->toDateTimeString())
			->get();

		foreach ($loans as $loan)
		{
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
	 * @return  integer
	 */
	public function getTotalcoresAttribute()
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
	 * @return  integer
	 */
	public function getTotalnodesAttribute()
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
	 * @return  integer
	 */
	public function getSoldcoresAttribute()
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
	 * @return  integer
	 */
	public function getSoldnodesAttribute()
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
	 * @return  integer
	 */
	public function getLoanedcoresAttribute()
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
	 * @return  integer
	 */
	public function getLoanednodesAttribute()
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
	 * @return  integer
	 */
	public function getServiceunitsAttribute()
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
	 * @return  integer
	 */
	public function getWalltimeAttribute()
	{
		$walltime = 0;
		$now = Carbon::now();

		/*foreach ($this->walltimes as $w)
		{
			$walltime += $w->walltime;
		}*/
		
		$w = $this->walltimes()
			->where('datetimestart', '<', $now->toDateTimeString())
			->where(function($where) use ($now)
			{
				$where->whereNull('datetimestop')
					->orWhere('datetimestop', '>', $now->toDateTimeString());
			})
			->orderBy('walltime', 'desc')
			->first();

		if ($w)
		{
			$walltime = $w->walltime;
		}

		return $walltime;
	}

	/**
	 * Get default max walltime in human readable format
	 *
	 * @return  string
	 */
	public function getHumanWalltimeAttribute()
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
	public function getNameWithSubclusterAttribute()
	{
		$name = $this->name;

		if ($this->cluster && substr($name, -strlen('-' . $this->cluster)) != '-' . $this->cluster)
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
	public function getDefaultQosNameAttribute()
	{
		return ($this->isSystem() ? $this->name : $this->nameWithSubcluster) . '-default';
	}

	/**
	 * Is this a system queue?
	 *
	 * @return  bool
	 */
	public function isSystem()
	{
		return ($this->groupid <= 0);
	}

	/**
	 * Is this an owner queue?
	 *
	 * @return  bool
	 */
	public function isOwner()
	{
		return ($this->groupid > 0);
	}

	/**
	 * Stop scheduling
	 *
	 * @return  bool
	 */
	public function stop()
	{
		return $this->update(['started' => 0]);
	}

	/**
	 * Stop scheduling
	 *
	 * @return  bool
	 */
	public function start()
	{
		return $this->update(['started' => 1]);
	}

	/**
	 * Add a user
	 *
	 * @param   integer  $userid
	 * @param   integer  $membertype
	 * @return  bool
	 */
	public function addUser($userid, $membertype = 1)
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
			$row->notice = 0;
		}
		else
		{
			$row = new User;
			$row->queueid = $this->id;
			$row->userid = $userid;
			$row->membertype = $membertype;
			$row->notice = 2;
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
	 * @param   integer  $userid
	 * @return  bool
	 */
	public function removeUser($userid)
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
	 * @param   integer $lenderqueueid
	 * @param   string  $start
	 * @param   string  $stop
	 * @param   integer $nodecount
	 * @param   integer $corecount
	 * @param   integer $serviceunits
	 * @param   string  $comment
	 * @return  bool
	 */
	public function addLoan($lenderqueueid, $start, $stop = null, $nodecount = 0, $corecount = 0, $serviceunits = 0, $comment = null)
	{
		$row = new Loan;
		$row->queueid = $this->id;
		$row->lenderqueueid = $lenderqueueid;

		$row->datetimestart = Carbon::now()->toDateTimeString();
		if ($start)
		{
			$row->datetimestart = Carbon::parse($start)->toDateTimeString();
		}

		if ($stop)
		{
			$row->datetimestop = Carbon::parse($stop)->toDateTimeString();
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
	 * @param   integer $sellerqueueid
	 * @param   string  $start
	 * @param   string  $stop
	 * @param   integer $nodecount
	 * @param   integer $corecount
	 * @param   integer $serviceunits
	 * @param   string  $comment
	 * @return  bool
	 */
	public function addPurchase($sellerqueueid, $start, $stop = null, $nodecount = 0, $corecount = 0, $serviceunits = 0, $comment = null)
	{
		$row = new Size;
		$row->queueid = $this->id;
		$row->sellerqueueid = $sellerqueueid;

		$row->datetimestart = Carbon::now()->toDateTimeString();
		if ($start)
		{
			$row->datetimestart = Carbon::parse($start)->toDateTimeString();
		}

		if ($stop)
		{
			$row->datetimestop = Carbon::parse($stop)->toDateTimeString();
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
			->get()
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
			->get()
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
	 * @param   array  $options
	 * @return  bool
	 */
	public function delete(array $options = [])
	{
		foreach ($this->users as $row)
		{
			$row->update(['notice' => 0]);
			$row->delete();

			// Look up the current username of the user being removed
			$user = $row->user;

			// Look up the role name of the resource to which access is being granted.
			$resource = $this->resource;

			if (!$user)
			{
				continue;
			}

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
					return response()->json(null, 403);
				}
			}
			else
			{
				$owned = $user->groups->pluck('id')->toArray();
			}

			// Check for other queue memberships on this resource that might conflict with removing the role
			$rows = 0;

			$resources = Asset::query()
				->where('rolename', '!=', '')
				->where('listname', '!=', '')
				->get();

			foreach ($resources as $res)
			{
				$subresources = $res->subresources;

				foreach ($subresources as $sub)
				{
					$queues = $sub->queues()
						->whereIn('groupid', $owned)
						->get();

					foreach ($queues as $queue)
					{
						$rows += $queue->users()
							->whereIsMember()
							->where('userid', '=', $user->id)
							->count();

						$rows += $queue->group->members()
							->whereIsManager()
							->where('userid', '=', $user->id)
							->count();
					}
				}
			}

			if ($rows <= 0)
			{
				// Call to remove role from this user's account.
				event(new ResourceMemberDeleted($resource, $user));
			}
		}

		return parent::delete($options);
	}
}
