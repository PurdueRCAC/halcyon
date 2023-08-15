<?php
namespace App\Modules\Queues\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\Queues\Events\QosCreated;
use App\Modules\Queues\Events\QosUpdated;
use App\Modules\Queues\Events\QosDeleted;
use App\Modules\History\Traits\Historable;

/**
 * Model for a queue Quality of Service
 *
 * @property int $scheduler_id
 * @property string $name
 * @property string $description
 * @property string $flags
 * @property int $grace_time
 * @property int $max_jobs_pa
 * @property int $max_jobs_per_user
 * @property int $max_jobs_accrue_pa
 * @property int $max_jobs_accrue_pu
 * @property int $min_prio_thresh
 * @property int $max_submit_jobs_pa
 * @property int $max_submit_jobs_per_user
 * @property string $max_tres_pa
 * @property string $max_tres_pj
 * @property string $max_tres_pn
 * @property string $max_tres_pu
 * @property string $max_tres_mins_pj
 * @property string $max_tres_run_mins_pa
 * @property string $max_tres_run_mins_pu
 * @property string $min_tres_pj
 * @property int $max_wall_duration_per_job
 * @property int $grp_jobs
 * @property int $grp_jobs_accrue
 * @property int $grp_submit_jobs
 * @property string $grp_tres
 * @property string $grp_tres_mins
 * @property string $grp_tres_run_mins
 * @property int $grp_wall
 * @property string $preempt
 * @property string $preempt_mode
 * @property int $preempt_exempt_time
 * @property int $priority
 * @property float $usage_factor
 * @property float $usage_thres
 * @property float $limit_factor
 * @property \Carbon\Carbon|null $datetimecreated
 * @property \Carbon\Carbon|null $datetimeremoved
 * @property \Carbon\Carbon|null $datetimeedited
 */
class Qos extends Model
{
	use Historable, SoftDeletes;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'queueqos';

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
	const UPDATED_AT = 'datetimeedited';

	/**
	 * The name of the "deleted at" column.
	 *
	 * @var string|null
	 */
	const DELETED_AT = 'datetimeremoved';

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
		'created' => QosCreated::class,
		'updated' => QosUpdated::class,
		'deleted' => QosDeleted::class,
	];

	/**
	 * Boot
	 *
	 * @return  void
	 */
	public static function boot(): void
	{
		parent::boot();

		self::deleted(function($model)
		{
			foreach ($model->queueqoses as $queueqos)
			{
				$queueqos->delete();
			}
		});
	}

	/**
	 * Defines a relationship to schedulers
	 *
	 * @return  BelongsTo
	 */
	public function scheduler(): BelongsTo
	{
		return $this->belongsTo(Scheduler::class, 'scheduler_id');
	}

	/**
	 * Defines a direct relationship to queues
	 *
	 * @return HasManyThrough
	 */
	public function queues(): HasManyThrough
	{
		return $this->hasManyThrough(Queue::class, QueueQos::class, 'qosid', 'id', 'id', 'queueid');
	}

	/**
	 * Defines a relationship to queue qos map
	 *
	 * @return  HasMany
	 */
	public function queueqoses(): HasMany
	{
		return $this->hasMany(QueueQos::class, 'qosid');
	}

	/**
	 * Get the list of preempts
	 *
	 * @return  array<int,string>
	 */
	public function getPreemptListAttribute(): array
	{
		return $this->preempt ? explode("\n", $this->preempt) : [];
	}

	/**
	 * Get the list of preempt modes
	 *
	 * @return  array<int,string>
	 */
	public function getPreemptModeListAttribute(): array
	{
		return $this->preempt_mode ? explode(',', $this->preempt_mode) : [];
	}

	/**
	 * Get the list of flags
	 *
	 * @return  array<int,string>
	 */
	public function getFlagsListAttribute(): array
	{
		return $this->flags ? explode(',', $this->flags) : [];
	}
}
