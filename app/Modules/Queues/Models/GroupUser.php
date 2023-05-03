<?php
namespace App\Modules\Queues\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
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
 * @property int    $groupid
 * @property int    $queueuserid
 * @property int    $userrequestid
 * @property int    $membertype
 * @property Carbon|null $datetimecreated
 * @property Carbon|null $datetimeremoved
 * @property int    $notice
 */
class GroupUser extends Model
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
	protected $table = 'groupqueueusers';

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
	 * Defines a relationship to queue
	 *
	 * @return  BelongsTo
	 */
	public function queue(): BelongsTo
	{
		return $this->belongsTo(Queue::class, 'queueid');
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
	 * Defines a relationship to queue user
	 *
	 * @return  BelongsTo
	 */
	public function queueuser(): BelongsTo
	{
		return $this->belongsTo(User::class, 'queueuserid');
	}

	/**
	 * Defines a relationship to member type
	 *
	 * @return  BelongsTo
	 */
	public function type(): BelongsTo
	{
		return $this->belongsTo(MemberType::class, 'membertype');
	}

	/**
	 * Defines a relationship to user request
	 *
	 * @return  HasOne
	 */
	public function request(): HasOne
	{
		return $this->hasOne(UserRequest::class, 'id', 'userrequestid');
	}

	/**
	 * Query scope where membership is pending
	 *
	 * @param   Builder $query
	 * @return  Builder
	 */
	public function scopeWherePendingRequest(Builder $query): Builder
	{
		return $query->where($this->getTable() . '.membertype', '=', 4);
	}

	/**
	 * Query scope where is member
	 *
	 * @param   Builder $query
	 * @return  Builder
	 */
	public function scopeWhereIsMember(Builder $query): Builder
	{
		return $query->where('membertype', '=', MemberType::MEMBER);
	}

	/**
	 * Query scope where membership is manager
	 *
	 * @param   Builder $query
	 * @return  Builder
	 */
	public function scopeWhereIsManager(Builder $query): Builder
	{
		return $query->where('membertype', '=', MemberType::MANAGER);
	}

	/**
	 * Query scope where membership is viewer
	 *
	 * @param   Builder $query
	 * @return  Builder
	 */
	public function scopeWhereIsViewer(Builder $query): Builder
	{
		return $query->where('membertype', '=', MemberType::VIEWER);
	}

	/**
	 * Query scope where membership is pending
	 *
	 * @param   Builder $query
	 * @return  Builder
	 */
	public function scopeWhereIsPending(Builder $query): Builder
	{
		return $query->where('membertype', '=', MemberType::PENDING);
	}

	/**
	 * Set membership type to standard member
	 *
	 * @return  void
	 */
	public function setAsMember(): void
	{
		$this->membertype = 1;
	}

	/**
	 * Set membership type to manager
	 *
	 * @return  void
	 */
	public function setAsManager(): void
	{
		$this->membertype = 2;
	}

	/**
	 * Set membership type to viewer
	 *
	 * @return  void
	 */
	public function setAsViewer(): void
	{
		$this->membertype = 3;
	}

	/**
	 * Set membership type to pending
	 *
	 * @return  void
	 */
	public function setAsPending(): void
	{
		$this->membertype = 4;
	}
}
