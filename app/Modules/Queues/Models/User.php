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
class User extends Model
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
	protected $table = 'queueusers';

	/**
	 * Fields and their validation criteria
	 *
	 * @var array
	 */
	protected $rules = array(
		'name' => 'notempty'
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
	 * Boot
	 *
	 * @return  void
	 */
	public static function boot()
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
	public function wasLastseen()
	{
		return !is_null($this->datetimelastseen);
	}

	/**
	 * Defines a relationship to queue
	 *
	 * @return  object
	 */
	public function queue()
	{
		return $this->belongsTo(Queue::class, 'queueid');
	}

	/**
	 * Defines a relationship to creator
	 *
	 * @return  object
	 */
	public function user()
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'userid');
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
	 * Defines a relationship to group user
	 *
	 * @return  object
	 */
	public function groupUser()
	{
		return $this->belongsTo(GroupUser::class, 'queueuserid');
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
	 * Set as a member
	 *
	 * @return  void
	 */
	public function setAsMember()
	{
		$this->membertype = MemberType::MEMBER;
	}

	/**
	 * Set as a manager
	 *
	 * @return  void
	 */
	public function setAsManager()
	{
		$this->membertype = MemberType::MANAGER;
	}

	/**
	 * Set as a viewer
	 *
	 * @return  void
	 */
	public function setAsViewer()
	{
		$this->membertype = MemberType::VIEWER;
	}

	/**
	 * Set as a pending member
	 *
	 * @return  void
	 */
	public function setAsPending()
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
	public function isMember()
	{
		return ($this->membertype == MemberType::MEMBER);
	}

	/**
	 * Is manager?
	 *
	 * @return  bool
	 */
	public function isManager()
	{
		return ($this->membertype == MemberType::MANAGER);
	}

	/**
	 * Is viewer?
	 *
	 * @return  bool
	 */
	public function isViewer()
	{
		return ($this->membertype == MemberType::VIEWER);
	}

	/**
	 * Is memebership pending?
	 *
	 * @return  bool
	 */
	public function isPending()
	{
		return ($this->membertype == MemberType::PENDING);
	}
}
