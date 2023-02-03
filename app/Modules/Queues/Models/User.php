<?php
namespace App\Modules\Queues\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
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
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'queueusers';

	/**
	 * Fields and their validation criteria
	 *
	 * @var array<string,string>
	 */
	protected $rules = array(
		'name' => 'notempty'
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
			if ($model->notice == 2)
			{
				$model->notice = 0;
			}
			elseif ($model->notice == 10)
			{
				$model->notice = 17;
			}
			else
			{
				$model->notice = 3;
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
	 * @param   object  $query
	 * @return  object
	 */
	public function scopeWherePendingRequest($query)
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
	 * @param   object  $query
	 * @return  object
	 */
	public function scopeWhereIsMember($query)
	{
		return $query->where($this->getTable() . '.membertype', '=', MemberType::MEMBER);
	}

	/**
	 * Query scope for where is manager
	 *
	 * @param   object  $query
	 * @return  object
	 */
	public function scopeWhereIsManager($query)
	{
		return $query->where($this->getTable() . '.membertype', '=', MemberType::MANAGER);
	}

	/**
	 * Query scope for where is viewer
	 *
	 * @param   object  $query
	 * @return  object
	 */
	public function scopeWhereIsViewer($query)
	{
		return $query->where($this->getTable() . '.membertype', '=', MemberType::VIEWER);
	}

	/**
	 * Query scope for where is pending
	 *
	 * @param   object  $query
	 * @return  object
	 */
	public function scopeWhereIsPending($query)
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
