<?php
namespace App\Modules\Groups\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Halcyon\Traits\ErrorBag;
use App\Halcyon\Traits\Validatable;
use App\Modules\History\Traits\Historable;
use App\Modules\Groups\Events\UnixGroupCreating;
use App\Modules\Groups\Events\UnixGroupDeleted;

/**
 * Unix Group model
 */
class UnixGroup extends Model
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
		'longname' => 'required|string|max:32',
		'shortname' => 'nullable|string|max:8'
	);

	/**
	 * The event map for the model.
	 *
	 * @var array
	 */
	protected $dispatchesEvents = [
		'creating' => UnixGroupCreating::class,
		//'created'  => UnixGroupCreated::class,
		//'updating' => UnixGroupUpdating::class,
		//'updated'  => UnixGroupUpdated::class,
		'deleted'  => UnixGroupDeleted::class,
	];

	/**
	 * Group
	 *
	 * @return  object
	 */
	public function group()
	{
		return $this->belongsTo(Group::class, 'groupid');
	}

	/**
	 * Get a list of users
	 *
	 * @return  object
	 */
	public function members()
	{
		return $this->hasMany(UnixGroupMember::class, 'unixgroupid');
	}

	/**
	 * Generate a shortname from a longname
	 *
	 * @param   string  $name
	 * @return  string
	 */
	public function generateShortname(string $name)
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
	 * @param   array  $options
	 * @return  bool
	 */
	public function delete(array $options = [])
	{
		foreach ($this->members as $row)
		{
			$row->delete();
		}

		return parent::delete($options);
	}

	/**
	 * Add user as a member
	 *
	 * @param   integer  $userid
	 * @return  bool
	 */
	public function addMember(int $userid)
	{
		$member = UnixGroupMember::findByGroupAndUser($this->id, $userid);

		$member = $member ?: new UnixGroupMember;
		$member->userid = $userid;
		$member->unixgroupid = $this->id;

		return $member->save();
	}

	/**
	 * Get a list of "message of the day"
	 *
	 * @param   string  $name
	 * @return  mixed   object|null
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
	 * @return  mixed   object|null
	 */
	public static function findByShortname(string $name)
	{
		return self::query()
			->where('shortname', '=', $name)
			->first();
	}
}
