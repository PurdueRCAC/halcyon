<?php
namespace App\Modules\Queues\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use App\Modules\History\Traits\Historable;
use App\Modules\Queues\Events\UserCreating;
use App\Modules\Queues\Events\UserCreated;
use App\Modules\Queues\Events\UserUpdating;
use App\Modules\Queues\Events\UserUpdated;
use App\Modules\Queues\Events\UserDeleted;
use App\Modules\Groups\Models\Group;
use Carbon\Carbon;

/**
 * Model for a queue/user association
 *
 * @property int    $id
 * @property int    $queueid
 * @property int    $userid
 * @property int    $userrequestid
 * @property int    $membertype
 * @property Carbon|null $datetimecreated
 * @property Carbon|null $datetimeremoved
 * @property Carbon|null $datetimelastseen
 * @property int    $notice
 */
class User extends Model
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
	 * Notice values
	 *
	 * @var int
	 */
	const NOTICE_NONE = 0;
	const NOTICE_REQUEST_GRANTED = 2;
	const NOTICE_REMOVED = 3;
	const NOTICE_REQUESTED = 6;
	const NOTICE_REQUEST_DENIED = 12;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'queueusers';

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
		'creating' => UserCreating::class,
		'created'  => UserCreated::class,
		'updating' => UserUpdating::class,
		'updated'  => UserUpdated::class,
		'deleted'  => UserDeleted::class,
	];

	/**
	 * Boot
	 *
	 * @return  void
	 */
	public static function boot(): void
	{
		parent::boot();

		self::deleting(function($model)
		{
			// Determine notice level
			if ($model->notice == self::NOTICE_REQUEST_GRANTED)
			{
				$model->notice = self::NOTICE_NONE;
			}
			elseif ($model->notice == 10)
			{
				$model->notice = 17;
			}
			else
			{
				$model->notice = self::NOTICE_REMOVED;
			}
		});
	}

	/**
	 * Determine if user was last seen
	 *
	 * @return  bool
	 */
	public function wasLastseen(): bool
	{
		return !is_null($this->datetimelastseen);
	}

	/**
	 * Defines a relationship to queue
	 *
	 * @return  BelongsTo
	 */
	public function queue(): BelongsTo
	{
		return $this->belongsTo(Queue::class, 'queueid');
	}

	/**
	 * Defines a relationship to creator
	 *
	 * @return  BelongsTo
	 */
	public function user(): BelongsTo
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'userid');
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
	 * Defines a relationship to group user
	 *
	 * @return  BelongsTo
	 */
	public function groupUser(): BelongsTo
	{
		return $this->belongsTo(GroupUser::class, 'queueuserid');
	}

	/**
	 * Defines a relationship to membertype
	 *
	 * @return  BelongsTo
	 */
	public function type(): BelongsTo
	{
		return $this->belongsTo(MemberType::class, 'membertype');
	}

	/**
	 * Defines a relationship to userrequest
	 *
	 * @return  HasOne
	 */
	public function request(): HasOne
	{
		return $this->hasOne(UserRequest::class, 'id', 'userrequestid');
	}

	/**
	 * Query scope for where is pending member
	 *
	 * @param   Builder  $query
	 * @return  Builder
	 */
	public function scopeWherePendingRequest(Builder $query): Builder
	{
		return $query->where($this->getTable() . '.membertype', '=', MemberType::PENDING);
	}

	/**
	 * Get user that created this record
	 *
	 * @return  \App\Modules\Users\Models\User|null
	 */
	public function addedBy()
	{
		$log = $this->history()
			->where('action', '=', 'created')
			->orderBy('id', 'desc')
			->first();

		return $log->user;
	}

	/**
	 * Get user that deleted this record
	 *
	 * @return  \App\Modules\Users\Models\User|null
	 */
	public function removedBy()
	{
		$log = $this->history()
			->where('action', '=', 'deleted')
			->orderBy('id', 'desc')
			->first();

		return $log->user;
	}

	/**
	 * Set as a member
	 *
	 * @return  void
	 */
	public function setAsMember(): void
	{
		$this->membertype = MemberType::MEMBER;
	}

	/**
	 * Set as a manager
	 *
	 * @return  void
	 */
	public function setAsManager(): void
	{
		$this->membertype = MemberType::MANAGER;
	}

	/**
	 * Set as a viewer
	 *
	 * @return  void
	 */
	public function setAsViewer(): void
	{
		$this->membertype = MemberType::VIEWER;
	}

	/**
	 * Set as a pending member
	 *
	 * @return  void
	 */
	public function setAsPending(): void
	{
		$this->membertype = MemberType::PENDING;
	}

	/**
	 * Query scope for where is member
	 *
	 * @param   Builder  $query
	 * @return  Builder
	 */
	public function scopeWhereIsMember(Builder $query): Builder
	{
		return $query->where($this->getTable() . '.membertype', '=', MemberType::MEMBER);
	}

	/**
	 * Query scope for where is manager
	 *
	 * @param   Builder  $query
	 * @return  Builder
	 */
	public function scopeWhereIsManager(Builder $query): Builder
	{
		return $query->where($this->getTable() . '.membertype', '=', MemberType::MANAGER);
	}

	/**
	 * Query scope for where is viewer
	 *
	 * @param   Builder  $query
	 * @return  Builder
	 */
	public function scopeWhereIsViewer(Builder $query): Builder
	{
		return $query->where($this->getTable() . '.membertype', '=', MemberType::VIEWER);
	}

	/**
	 * Query scope for where is pending
	 *
	 * @param   Builder  $query
	 * @return  Builder
	 */
	public function scopeWhereIsPending(Builder $query): Builder
	{
		return $query->where($this->getTable() . '.membertype', '=', MemberType::PENDING);
	}

	/**
	 * Is regular member?
	 *
	 * @return  bool
	 */
	public function isMember(): bool
	{
		return ($this->membertype == MemberType::MEMBER);
	}

	/**
	 * Is manager?
	 *
	 * @return  bool
	 */
	public function isManager(): bool
	{
		return ($this->membertype == MemberType::MANAGER);
	}

	/**
	 * Is viewer?
	 *
	 * @return  bool
	 */
	public function isViewer(): bool
	{
		return ($this->membertype == MemberType::VIEWER);
	}

	/**
	 * Is memebership pending?
	 *
	 * @return  bool
	 */
	public function isPending(): bool
	{
		return ($this->membertype == MemberType::PENDING);
	}
}
