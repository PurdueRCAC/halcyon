<?php
namespace App\Modules\Groups\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\History\Traits\Historable;
use App\Modules\Groups\Events\UnixGroupCreating;
use App\Modules\Groups\Events\UnixGroupCreated;
use App\Modules\Groups\Events\UnixGroupDeleting;
use App\Modules\Groups\Events\UnixGroupDeleted;
use App\Modules\Groups\Events\UnixGroupMemberCreated;
use Carbon\Carbon;

/**
 * Unix Group model
 *
 * @property int    $id
 * @property int    $groupid
 * @property int    $unixgid
 * @property string $shortname
 * @property string $longname
 * @property Carbon|null $datetimecreated
 * @property Carbon|null $datetimeremoved
 *
 * @property string $api
 */
class UnixGroup extends Model
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
	protected $table = 'unixgroups';

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'longname';

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
		'creating' => UnixGroupCreating::class,
		'created'  => UnixGroupCreated::class,
		//'updating' => UnixGroupUpdating::class,
		//'updated'  => UnixGroupUpdated::class,
		'deleting' => UnixGroupDeleting::class,
		'deleted'  => UnixGroupDeleted::class,
	];

	/**
	 * Group
	 *
	 * @return  BelongsTo
	 */
	public function group(): BelongsTo
	{
		return $this->belongsTo(Group::class, 'groupid');
	}

	/**
	 * Get a list of users
	 *
	 * @return  HasMany
	 */
	public function members(): HasMany
	{
		return $this->hasMany(UnixGroupMember::class, 'unixgroupid');
	}

	/**
	 * Generate a shortname from a longname
	 *
	 * @param   string  $name
	 * @return  string
	 */
	public function generateShortname(string $name): string
	{
		$lastchar = '0';
		if (preg_match('/^$/', $name))
		{
			$lastchar = '0';
		}
		elseif (preg_match('/^data$/', $name))
		{
			$lastchar = '1';
		}
		elseif (preg_match('/^apps$/', $name))
		{
			$lastchar = '2';
		}
		elseif (preg_match('/^web$/', $name))
		{
			$lastchar = '3';
		}
		elseif (preg_match('/^repo$/', $name))
		{
			$lastchar = '4';
		}
		elseif (preg_match('/^mgr$/', $name))
		{
			$lastchar = '5';
		}
		elseif (preg_match('/^archive$/', $name))
		{
			$lastchar = '6';
		}
		elseif (preg_match('/^sudo$/', $name))
		{
			$lastchar = '9';
		}
		else
		{
			$data = self::query()
				->where('groupid', '=', $this->groupid)
				->orderBy('shortname', 'asc')
				->get();

			$lastchar = 'a';

			foreach ($data as $row)
			{
				if (preg_match('/^rcs\d{4}[a-z]$/', $row->shortname))
				{
					$rowchar = preg_replace('/^rcs\d{4}/', '', $row->shortname);

					if ($rowchar == $lastchar)
					{
						$lastchar++;
					}
					else
					{
						break;
					}
				}
			}
		}

		return 'rcs' . str_pad("{$this->groupid}", 4, '0', STR_PAD_LEFT) . $lastchar;
	}

	/**
	 * Delete entry and associated data
	 *
	 * @return  bool
	 */
	public function delete(): bool
	{
		foreach ($this->members as $row)
		{
			$row->delete();
		}

		return parent::delete();
	}

	/**
	 * Add user as a member
	 *
	 * @param   int  $userid
	 * @param   int|null  $notice
	 * @return  bool
	 */
	public function addMember(int $userid, $notice = null): bool
	{
		$member = $this->members()
			->withTrashed()
			->where('userid', '=', $userid)
			->first();

		if ($member)
		{
			if ($member->trashed())
			{
				$member->restore();

				event(new UnixGroupMemberCreated($member));
			}

			// Nothing to do, we are cancelling a removal
			$member->notice = UnixGroupMember::NO_NOTICE;
		}
		else
		{
			$member = new UnixGroupMember;
			$member->unixgroupid = $this->id;
			$member->userid = $userid;
			$member->notice = is_null($notice) ? UnixGroupMember::NOTICE_AUTHORIZED : $notice;
		}

		return $member->save();
	}

	/**
	 * Remove user as a member
	 *
	 * @param   int  $userid
	 * @return  bool
	 */
	public function removeMember(int $userid): bool
	{
		$member = $this->members()
			->where('userid', '=', $userid)
			->first();

		if (!$member)
		{
			return true;
		}

		if (!$member->delete())
		{
			return false;
		}

		return true;
	}

	/**
	 * Get a list of "message of the day"
	 *
	 * @param   string  $name
	 * @return  UnixGroup|null
	 */
	public static function findByLongname(string $name): ?UnixGroup
	{
		return self::query()
			->where('longname', '=', $name)
			->first();
	}

	/**
	 * Get a list of "message of the day"
	 *
	 * @param   string  $name
	 * @return  UnixGroup|null
	 */
	public static function findByShortname(string $name): ?UnixGroup
	{
		return self::query()
			->where('shortname', '=', $name)
			->first();
	}
}
