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
	 * Fields and their validation criteria
	 *
	 * @var  array<string,string>
	 */
	protected $rules = array(
		'longname' => 'required|string|max:32',
		'shortname' => 'nullable|string|max:8'
	);

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

		return 'rcs' . str_pad($this->groupid, 4, '0', STR_PAD_LEFT) . $lastchar;
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
	 * @return  bool
	 */
	public function addMember(int $userid): bool
	{
		$member = $this->members()
			->withTrashed()
			->where('userid', '=', $userid)
			->first(); //UnixGroupMember::findByGroupAndUser($this->id, $userid);

		if ($member)
		{
			if ($member->trashed())
			{
				$member->restore();

				event(new UnixGroupMemberCreated($member));
			}

			// Nothing to do, we are cancelling a removal
			$member->notice = 0;
		}
		else
		{
			$member = new UnixGroupMember;
			$member->unixgroupid = $this->id;
			$member->userid = $userid;
			$member->notice = 2;
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
			->first();//UnixGroupMember::findByGroupAndUser($this->id, $userid);

		if (!$member || !$member->id)
		{
			return true;
		}

		// Determine notice level
		/*if ($member->notice == 2)
		{
			$member->notice = 0;
		}
		else
		{
			$member->notice = 3;
		}

		$member->save();

		// Check to see if another unix group by the same name exists
		//
		// This is a catch for a loophole condition that allowed for multiple
		// unix groups by the same name. In such a case, only ONE should have
		// a unixgid.
		$altunixgroup = UnixGroup::query()
			->where('longname', '=', $member->unixgroup->longname)
			->where('id', '!=', $member->unixgroupid)
			->first();

		if ($altunixgroup && (!$unixgroup->unixgid || !$altunixgroup->unixgid))
		{
			$altrow = UnixGroupMember::query()
				->withTrashed()
				->where('unixgroupid', '=', $altunixgroup->id)
				->where('userid', '=', $member->userid)
				->get()
				->first();

			if ($altrow)
			{
				$altrow->delete();
			}
		}*/

		return $member->delete();
	}

	/**
	 * Get a list of "message of the day"
	 *
	 * @param   string  $name
	 * @return  UnixGroup|null
	 */
	public static function findByLongname(string $name)
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
	public static function findByShortname(string $name)
	{
		return self::query()
			->where('shortname', '=', $name)
			->first();
	}
}
