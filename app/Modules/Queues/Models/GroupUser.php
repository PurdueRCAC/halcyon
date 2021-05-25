<?php
namespace App\Modules\Queues\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Halcyon\Traits\ErrorBag;
use App\Halcyon\Traits\Validatable;
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
 */
class GroupUser extends Model
{
	use ErrorBag, Validatable, Historable, SoftDeletes;

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
	 * @var string
	 */
	const DELETED_AT = 'datetimeremoved';

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'groupqueueusers';

	/**
	 * Fields and their validation criteria
	 *
	 * @var array
	 */
	protected $rules = array(
		'queueuserid' => 'required|integer|min:1',
		'groupid' => 'required|integer|min:1',
		'userrequestid' => 'nullable|integer',
		'membertype' => 'nullable|integer',
		'notice' => 'nullable|integer'
	);

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
		'creating' => UserCreating::class,
		'created'  => UserCreated::class,
		'updating' => UserUpdating::class,
		'updated'  => UserUpdated::class,
		'deleted'  => UserDeleted::class,
	];

	/**
	 * If entry is trashed
	 *
	 * @return  bool
	 **/
	public function isTrashed()
	{
		return ($this->datetimeremoved
			&& $this->datetimeremoved != '0000-00-00 00:00:00'
			&& $this->datetimeremoved != '-0001-11-30 00:00:00'
			&& $this->datetimeremoved < Carbon::now()->toDateTimeString());
	}

	/**
	 * Defines a relationship to notification type
	 *
	 * @return  object
	 */
	public function queue()
	{
		return $this->belongsTo(Queue::class, 'queueid');
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
	 * Defines a relationship to user
	 *
	 * @return  object
	 */
	public function queueuser()
	{
		return $this->belongsTo(User::class, 'queueuserid');
	}

	/**
	 * Defines a relationship to membertype
	 *
	 * @return  object
	 */
	public function type()
	{
		return $this->belongsTo(MemberType::class, 'membertype');
	}

	/**
	 * Defines a relationship to userrequest
	 *
	 * @return  object
	 */
	public function request()
	{
		return $this->hasOne(UserRequest::class, 'id', 'userrequestid');
	}

	/**
	 * Defines a relationship to creator
	 *
	 * @return  object
	 */
	public function scopeWherePendingRequest($query)
	{
		return $query->where($this->getTable() . '.membertype', '=', 4);
	}

	/**
	 * Query scope where is member
	 *
	 * @return  object
	 */
	public function scopeWhereIsMember($query)
	{
		return $query->where('membertype', '=', MemberType::MEMBER);
	}

	/**
	 * Query scope where membership is manager
	 *
	 * @return  object
	 */
	public function scopeWhereIsManager($query)
	{
		return $query->where('membertype', '=', MemberType::MANAGER);
	}

	/**
	 * Query scope where membership is viewer
	 *
	 * @return  object
	 */
	public function scopeWhereIsViewer($query)
	{
		return $query->where('membertype', '=', MemberType::VIEWER);
	}

	/**
	 * Query scope where membership is pending
	 *
	 * @return  object
	 */
	public function scopeWhereIsPending($query)
	{
		return $query->where('membertype', '=', MemberType::PENDING);
	}

	/**
	 * Defines a relationship to userrequest
	 *
	 * @return  void
	 */
	public function setAsMember()
	{
		$this->membertype = 1;
	}

	/**
	 * Defines a relationship to userrequest
	 *
	 * @return  void
	 */
	public function setAsManager()
	{
		$this->membertype = 2;
	}

	/**
	 * Defines a relationship to userrequest
	 *
	 * @return  void
	 */
	public function setAsViewer()
	{
		$this->membertype = 3;
	}

	/**
	 * Defines a relationship to userrequest
	 *
	 * @return  void
	 */
	public function setAsPending()
	{
		$this->membertype = 4;
	}
}
