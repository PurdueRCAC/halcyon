<?php
namespace App\Modules\Groups\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\History\Traits\Historable;
use App\Modules\Groups\Events\UnixGroupMemberCreating;
use App\Modules\Groups\Events\UnixGroupMemberCreated;
use App\Modules\Groups\Events\UnixGroupMemberDeleted;
use App\Modules\Users\Models\User;
use Carbon\Carbon;

/**
 * Unix Group member model
 *
 * @property int    $id
 * @property int    $unixgroupid
 * @property int    $userid
 * @property Carbon|null $datetimecreated
 * @property Carbon|null $datetimeremoved
 * @property int    $notice
 *
 * @property string $api
 */
class UnixGroupMember extends Model
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
	 * @var  string|null
	 */
	const UPDATED_AT = null;

	/**
	 * The name of the "deleted at" column.
	 *
	 * @var  string|null
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
		'creating' => UnixGroupMemberCreating::class,
		'created' => UnixGroupMemberCreated::class,
		'deleted' => UnixGroupMemberDeleted::class,
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
			// Set sequence value for new entries
			if ($model->notice == 2)
			{
				$model->notice = 0;
			}
			else
			{
				$model->notice = 3;
			}
			$model->save();
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

			if ($altunixgroup && (!$model->unixgroup->unixgid || !$altunixgroup->unixgid))
			{
				$altrow = self::query()
					->withTrashed()
					->where('unixgroupid', '=', $altunixgroup->id)
					->where('userid', '=', $model->userid)
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
	 * @return  BelongsTo
	 */
	public function unixgroup(): BelongsTo
	{
		return $this->belongsTo(UnixGroup::class, 'unixgroupid');
	}

	/**
	 * Get associated user
	 *
	 * @return  BelongsTo
	 */
	public function user(): BelongsTo
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'userid');
	}

	/**
	 * Get user that created this record
	 *
	 * @return  User|null
	 */
	public function addedBy(): ?User
	{
		$log = $this->history()
			->where('action', '=', 'created')
			->orderBy('id', 'desc')
			->first();

		return $log ? $log->user : null;
	}

	/**
	 * Get user that deleted this record
	 *
	 * @return  User|null
	 */
	public function removedBy(): ?User
	{
		$log = $this->history()
			->where('action', '=', 'deleted')
			->orderBy('id', 'desc')
			->first();

		return $log ? $log->user : null;
	}

	/**
	 * Get member type
	 *
	 * @return  Type|null
	 */
	public function getTypeAttribute(): ?Type
	{
		return Type::find(Type::MEMBER);
	}

	/**
	 * Is regular member?
	 *
	 * @return  bool
	 */
	public function isMember(): bool
	{
		return true;
	}

	/**
	 * Is manager?
	 *
	 * @return  bool
	 */
	public function isManager(): bool
	{
		return false;
	}

	/**
	 * Is viewer?
	 *
	 * @return  bool
	 */
	public function isViewer(): bool
	{
		return false;
	}

	/**
	 * Is memebership pending?
	 *
	 * @return  bool
	 */
	public function isPending(): bool
	{
		return false;
	}

	/**
	 * Get a record by unixgroup/user
	 *
	 * @param   int  $unixgroupid
	 * @param   int  $userid
	 * @return  UnixGroupMember|null
	 */
	public static function findByGroupAndUser(int $unixgroupid, int $userid): ?UnixGroupMember
	{
		return self::query()
			->where('unixgroupid', '=', $unixgroupid)
			->where('userid', '=', $userid)
			->first();
	}
}
