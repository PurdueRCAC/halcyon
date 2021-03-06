<?php
namespace App\Modules\Groups\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Halcyon\Traits\ErrorBag;
use App\Halcyon\Traits\Validatable;
use App\Modules\History\Traits\Historable;
use App\Modules\Groups\Events\UnixGroupMemberCreating;
use App\Modules\Groups\Events\UnixGroupMemberCreated;
use App\Modules\Groups\Events\UnixGroupMemberDeleted;

/**
 * Unix Group member model
 */
class UnixGroupMember extends Model
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
	 * @var  string
	 */
	const DELETED_AT = 'datetimeremoved';

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'unixgroupusers';

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'id';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public static $orderDir = 'asc';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [
		'id'
	];

	/**
	 * Fields and their validation criteria
	 *
	 * @var  array
	 */
	protected $rules = array(
		'unixgroupid' => 'required|integer',
		'userid' => 'required|integer'
	);

	/**
	 * The event map for the model.
	 *
	 * @var array
	 */
	protected $dispatchesEvents = [
		'creating' => UnixGroupMemberCreating::class,
		'created' => UnixGroupMemberCreated::class,
		'deleted' => UnixGroupMemberDeleted::class,
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
			// Set sequence value for new entries
			if ($model->notice == 2)
			{
				$model->notice = 0;
			}
			else
			{
				$model->notice = 3;
			}
		});

		self::deleted(function($model)
		{
			// Check to see if another unix group by the same name exists
			//
			// This is a catch for a loophole condition that allowed for multiple
			// unix groups by the same name. In such a case, only ONE should have
			// a unixgid.
			$altunixgroup = UnixGroup::query()
				->where('longname', '=', $model->unixgroup->longname)
				->where('id', '!=', $model->unixgroupid)
				->first();

			if ($altunixgroup && (!$unixgroup->unixgid || !$altunixgroup->unixgid))
			{
				$altrow = self::query()
					->withTrashed()
					->where('unixgroupid', '=', $altunixgroup->id)
					->where('userid', '=', $model->userid)
					->get()
					->first();

				if ($altrow)
				{
					$altrow->delete();
				}
			}
		});
	}

	/**
	 * Get parent unix group
	 *
	 * @return  object
	 */
	public function unixgroup()
	{
		return $this->belongsTo(UnixGroup::class, 'unixgroupid');
	}

	/**
	 * Get associated user
	 *
	 * @return  object
	 */
	public function user()
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'userid');
	}

	/**
	 * Get member type
	 *
	 * @return  object
	 */
	public function getTypeAttribute()
	{
		return Type::find(Type::MEMBER);
	}

	/**
	 * Is regular member?
	 *
	 * @return  bool
	 */
	public function isMember()
	{
		return true;
	}

	/**
	 * Is manager?
	 *
	 * @return  bool
	 */
	public function isManager()
	{
		return false;
	}

	/**
	 * Is viewer?
	 *
	 * @return  bool
	 */
	public function isViewer()
	{
		return false;
	}

	/**
	 * Is memebership pending?
	 *
	 * @return  bool
	 */
	public function isPending()
	{
		return false;
	}

	/**
	 * Get a record by unixgroup/user
	 *
	 * @param   integer  $unixgroupid
	 * @param   integer  $userid
	 * @return  object
	 */
	public static function findByGroupAndUser(int $unixgroupid, int $userid)
	{
		return self::query()
			->where('unixgroupid', '=', $unixgroupid)
			->where('userid', '=', $userid)
			->first();
	}
}
