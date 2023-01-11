<?php
namespace App\Modules\Groups\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use App\Modules\Messages\Models\Message;
use App\Modules\History\Traits\Historable;
use App\Modules\Storage\Models\Directory;
use App\Modules\Storage\Models\Loan;
use App\Modules\Storage\Models\Purchase;
use App\Modules\Queues\Models\Queue;
use App\Modules\Groups\Events\GroupCreating;
use App\Modules\Groups\Events\GroupCreated;
use App\Modules\Groups\Events\GroupUpdating;
use App\Modules\Groups\Events\GroupUpdated;
use App\Modules\Groups\Events\GroupDeleted;
use Carbon\Carbon;

/**
 * Group model
 */
class Group extends Model
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
	protected $table = 'groups';

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'name';

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
		'name' => 'required|string|max:48'
	);

	/**
	 * The event map for the model.
	 *
	 * @var array<string,string>
	 */
	protected $dispatchesEvents = [
		'creating' => GroupCreating::class,
		'created'  => GroupCreated::class,
		'updating' => GroupUpdating::class,
		'updated'  => GroupUpdated::class,
		'deleted'  => GroupDeleted::class,
	];

	/**
	 * Owner
	 *
	 * @return  object
	 */
	public function owner()
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'owneruserid');
	}

	/**
	 * Determine if a user is a manager
	 *
	 * @param   object  $user
	 * @return  boolean  True if modified, false if not
	 */
	public function isManager($user)
	{
		$managers = $this->managers->pluck('userid')->toArray();
		return in_array($user->id, $managers);
	}

	/**
	 * Department
	 *
	 * @return  object
	 */
	public function departments()
	{
		return $this->hasMany(GroupDepartment::class, 'groupid');
		//return $this->hasOneThrough(Department::class, GroupDepartment::class, 'groupid', 'id', 'groupid', 'collegedeptid');
	}

	/**
	 * Get a list of fields of science
	 *
	 * @return  object
	 */
	public function fieldsOfScience()
	{
		return $this->hasMany(GroupFieldOfScience::class, 'groupid');
	}

	/**
	 * Departments
	 *
	 * @return  object
	 */
	public function departmentList()
	{
		return $this->hasManyThrough(Department::class, GroupDepartment::class, 'groupid', 'id', 'id', 'collegedeptid');
	}

	/**
	 * Get a list of users
	 *
	 * @return  object
	 */
	public function members()
	{
		return $this->hasMany(Member::class, 'groupid');
	}

	/**
	 * Get a list of managers
	 *
	 * @return  object
	 */
	/*public function managers()
	{
		return $this->hasMany(Member::class, 'groupid')->where('membertype', '=', 2);
	}*/

	/**
	 * Get a list of managers
	 *
	 * @return  object
	 */
	public function getManagersAttribute()
	{
		$m = (new Member)->getTable();
		$u = (new \App\Modules\Users\Models\UserUsername)->getTable();

		$managers = $this->members()
			->withTrashed()
			->select($m . '.*')
			->join($u, $u . '.userid', $m . '.userid')
			->whereNull($u . '.dateremoved')
			->whereNull($m . '.dateremoved')
			->where($m . '.membertype', '=', 2)
			->orderBy($m . '.datecreated', 'desc')
			->get();

		return $managers;
	}

	/**
	 * Get a list of "message of the day"
	 *
	 * @return  object
	 */
	public function motds()
	{
		return $this->hasMany(Motd::class, 'groupid');
	}

	/**
	 * Get a list of storage directories
	 *
	 * @return  object
	 */
	public function directories()
	{
		return $this->hasMany(Directory::class, 'groupid');
	}

	/**
	 * Get a list of messages
	 *
	 * @return  object
	 */
	public function getMessagesAttribute()
	{
		$ids = $this->directories->pluck('id')->toArray();

		return Message::query()->whereIn('targetobjectid', $ids);
	}

	/**
	 * Get a list of storage buckets
	 *
	 * @return  array
	 */
	public function getStorageBucketsAttribute()
	{
		$buckets = array();
		foreach ($this->loans()->whenAvailable()->get() as $dir)
		{
			if (!isset($buckets[$dir->resourceid]))
			{
				$buckets[$dir->resourceid] = array(
					'resourceid' => 0,
					'soldbytes' => 0,
					'loanedbytes' => 0,
					'totalbytes' => 0,
					'unallocatedbytes' => 0,
					'allocatedbytes' => 0,
				);
			}
			$buckets[$dir->resourceid]['resourceid'] = $dir->resourceid;
			$buckets[$dir->resourceid]['loanedbytes'] += $dir->bytes;
			$buckets[$dir->resourceid]['totalbytes'] += $dir->bytes;
		}

		foreach ($this->purchases()->whenAvailable()->get() as $dir)
		{
			if (!isset($buckets[$dir->resourceid]))
			{
				$buckets[$dir->resourceid] = array(
					'resourceid' => 0,
					'soldbytes' => 0,
					'loanedbytes' => 0,
					'totalbytes' => 0,
					'unallocatedbytes' => 0,
					'allocatedbytes' => 0,
				);
			}
			$buckets[$dir->resourceid]['resourceid'] = $dir->resourceid;
			$buckets[$dir->resourceid]['soldbytes'] += $dir->bytes;
			$buckets[$dir->resourceid]['totalbytes'] += $dir->bytes;
		}

		foreach ($this->directories as $dir)
		{
			if (!isset($buckets[$dir->resourceid]))
			{
				continue;
			}
			$buckets[$dir->resourceid]['allocatedbytes'] += $dir->bytes;
		}

		foreach ($buckets as $k => $v)
		{
			$buckets[$k]['unallocatedbytes'] = ($buckets[$k]['totalbytes'] - $buckets[$k]['allocatedbytes']);
		}

		return $buckets;

		/*$allocated = array();
		$now = Carbon::now();

		// Fetch allocated amounts
		$data = Directory::query()
			->select(DB::raw('SUM(bytes) AS allocated', 'resourceid'))
			->where('groupid', '=', $this->id)
			->where(function($where) use ($now)
			{
				$where->whereNull('datetimecreated')
					->orWhere('datetimecreated', '<', $now->toDateTimeString());
			})
			->where(function($where) use ($now)
			{
				$where->whereNull('datetimeremoved')
					->orWhere('datetimeremoved', '>', $now->toDateTimeString());
			})
			->get();

		foreach ($data as $row)
		{
			$allocated[$row->resourceid] = $row->allocated;
		}

		// Fetch storage buckets under this group
		$storagebuckets = array();

		$data = Purchase::query()
			->select(DB::raw('SUM(bytes) AS soldbytes'), 'resourceid')
			->where('groupid', '=', $this->id)
			->where(function($where) use ($now)
			{
				$where->whereNull('datetimestop')
					->orWhere('datetimestop', '>', $now->toDateTimeString());
			})
			->where(function($where) use ($now)
			{
				$where->whereNull('datetimestart')
					->orWhere('datetimestart', '<', $now->toDateTimeString());
			})
			->groupBy('resourceid')
			->get();

		foreach ($data as $row)
		{
			array_push($storagebuckets, array(
				'resourceid'       => $row->resourceid,
				'soldbytes'        => $row->soldbytes,
				'loanedbytes'      => 0,
				'totalbytes'       => $row->soldbytes,
				'unallocatedbytes' => 0,
			));
		}

		$data = array();

		$data = Loan::query()
			->select(DB::raw('SUM(bytes) AS loanedbytes'), 'resourceid')
			->where('groupid', '=', $this->id)
			->where(function($where) use ($now)
			{
				$where->whereNull('datetimestop')
					->orWhere('datetimestop', '>', $now->toDateTimeString());
			})
			->where(function($where) use ($now)
			{
				$where->whereNull('datetimestart')
					->orWhere('datetimestart', '<', $now->toDateTimeString());
			})
			->groupBy('resourceid')
			->get();

		foreach ($data as $row)
		{
			$found = false;
			foreach ($storagebuckets as $bucket)
			{
				if ($bucket['resourceid'] == $row->resourceid)
				{
					$bucket['loanedbytes'] = $row->loanedbytes;
					$bucket['totalbytes'] += $row->loanedbytes;
					$found = true;
					break;
				}
			}

			if (!$found)
			{
				// TODO: calculate remainder quota
				array_push($storagebuckets, array(
					'resourceid'       => $row->resourceid,
					'soldbytes'        => 0,
					'unallocatedbytes' => 0,
					'loanedbytes'      => $row->loanedbytes,
					'totalbytes'       => $row->loanedbytes,
				));
			}
		}

		foreach ($storagebuckets as $bucket)
		{
			if (!isset($allocated[$bucket['resourceid']]))
			{
				$allocated[$bucket['resourceid']] = 0;
			}

			$b = Directory::query()
				->select(DB::raw('SUM(bytes)'))
				->where('groupid', '=', $this->id)
				->where('resourceid', '=', $bucket['resourceid'])
				->whereNull('datetimeremoved')
				->get()
				->first();

			$allocatedbytes = 0;

			if ($b)
			{
				$allocatedbytes = $b->bytes;
			}

			$bucket['unallocatedbytes'] = abs($bucket['totalbytes'] - $allocated[$bucket['resourceid']]);
			$bucket['allocatedbytes'] = $allocatedbytes;
		}*/

		return $storagebuckets;
	}

	/**
	 * Get a list of storage loans
	 *
	 * @return  object
	 */
	public function loans()
	{
		return $this->hasMany(Loan::class, 'groupid');
	}

	/**
	 * Get a list of storage purchases
	 *
	 * @return  object
	 */
	public function purchases()
	{
		return $this->hasMany(Purchase::class, 'groupid');
	}

	/**
	 * Get a list of queues
	 *
	 * @return  object
	 */
	public function queues()
	{
		return $this->hasMany(Queue::class, 'groupid');
	}

	/**
	 * Get a list of unix groups
	 *
	 * @return  object
	 */
	public function unixGroups()
	{
		return $this->hasMany(UnixGroup::class, 'groupid');
	}

	/**
	 * Get the primary unix group
	 *
	 * @return  object
	 */
	public function getPrimaryUnixGroupAttribute()
	{
		return $this->unixGroups()
			->where('longname', '=', $this->unixgroup)
			->first();
	}

	/**
	 * Get a list of "message of the day"
	 *
	 * @return  object
	 */
	public function getMotdAttribute()
	{
		return $this->motds()
			->orderBy('datetimecreated', 'desc')
			->first();
	}

	/**
	 * Get a list of resources
	 *
	 * @return  object
	 */
	public function getResourcesAttribute()
	{
		$resources = [];

		foreach ($this->queues as $queue)
		{
			if (!$queue->resource)
			{
				continue;
			}

			if ($queue->resource->trashed())
			{
				continue;
			}

			if (!isset($resources[$queue->resource->id]))
			{
				$resources[$queue->resource->id] = $queue->resource;
			}
		}

		return collect(array_values($resources));
	}

	/**
	 * Get a list of prior (trashed) resources
	 *
	 * @return  object
	 */
	public function getPriorResourcesAttribute()
	{
		$resources = [];

		foreach ($this->queues as $queue)
		{
			if (!$queue->resource)
			{
				continue;
			}

			if (!$queue->resource->trashed())
			{
				continue;
			}

			if (!isset($resources[$queue->resource->id]))
			{
				$resources[$queue->resource->id] = $queue->resource;
			}
		}

		return collect(array_values($resources));
	}

	/**
	 * Get a count of pending memberships
	 *
	 * @return  object
	 */
	public function getPendingMembersCountAttribute()
	{
		$q = (new Queue)->getTable();
		$s = (new \App\Modules\Resources\Models\Child)->getTable();
		$r = (new \App\Modules\Resources\Models\Asset)->getTable();

		$queues = $this->queues()
			->withTrashed()
			->select($q . '.*')
			->join($s, $s . '.subresourceid', $q . '.subresourceid')
			->join($r, $r . '.id', $s . '.resourceid')
			->whereNull($q . '.datetimeremoved')
			->whereNull($r . '.datetimeremoved')
			->get();

		$processed = array();
		$pending = 0;
		$managers = $this->managers->pluck('userid')->toArray();

		foreach ($queues as $queue)
		{
			// First we need to look for potentially duplicate records
			$active = array();

			$queueids = $queue->users()
				->orderBy('membertype', 'asc')
				->get();

			foreach ($queueids as $queueid)
			{
				$key = $queueid->queueid . '-' . $queueid->userid;

				if (in_array($key, $active) && $queueid->isPending())
				{
					// Duplicate record, remove
					$queueid->delete();
				}

				if (!$queueid->isPending())
				{
					$active[] = $key;
				}
			}

			$users = $queue->users()
				->whereIsPending()
				->get();

			foreach ($users as $me)
			{
				if (in_array($me->userid, $processed))
				{
					continue;
				}

				if ($me->user && !$me->user->trashed())
				{
					// If the user is already a group manager, then they're approved
					if (in_array($me->userid, $managers))
					{
						$me->membertype = 1;
						$me->notice = 0;
						$me->save();

						continue;
					}

					$pending++;
				}

				$processed[] = $me->userid;
			}
		}

		return $pending;
	}

	/**
	 * Get a list of "message of the day"
	 *
	 * @param   string  $name
	 * @return  object
	 */
	public static function findByName(string $name)
	{
		return self::query()
			->where('name', '=', $name)
			->first();
	}

	/**
	 * Get a list of "message of the day"
	 *
	 * @param   string  $unixgroup
	 * @return  object
	 */
	public static function findByUnixgroup(string $unixgroup)
	{
		return self::query()
			->where('unixgroup', '=', $unixgroup)
			->first();
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

		foreach ($this->motds as $row)
		{
			$row->delete();
		}

		foreach ($this->queues as $row)
		{
			$row->delete();
		}

		foreach ($this->directories as $row)
		{
			$row->delete();
		}

		foreach ($this->fieldsOfScience as $row)
		{
			$row->delete();
		}

		foreach ($this->unixGroups as $row)
		{
			$row->delete();
		}

		return parent::delete($options);
	}

	/**
	 * Add user as a manager
	 *
	 * @param   integer  $userid
	 * @param   integer  $owner
	 * @return  bool
	 */
	public function addManager(int $userid, int $owner = 0)
	{
		$member = Member::findByGroupAndUser($this->id, $userid);

		if ($member && $member->membertype == Type::MANAGER)
		{
			return true;
		}

		$member = $member ?: new Member;
		$member->userid = $userid;
		$member->groupid = $this->id;
		$member->membertype = Type::MANAGER;
		$member->owner = $owner;

		return $member->save();
	}

	/**
	 * Add user as a member
	 *
	 * @param   integer  $userid
	 * @return  bool
	 */
	public function addMember(int $userid)
	{
		$member = Member::findByGroupAndUser($this->id, $userid);

		if ($member && $member->membertype == Type::MEMBER)
		{
			return true;
		}

		$member = $member ?: new Member;
		$member->userid = $userid;
		$member->groupid = $this->id;
		$member->membertype = Type::MEMBER;
		$member->owner = 0;

		return $member->save();
	}

	/**
	 * Add a user as Viewer
	 *
	 * @param   integer  $userid
	 * @return  bool
	 */
	public function addViewer(int $userid)
	{
		$member = Member::findByGroupAndUser($this->id, $userid);

		if ($member && $member->membertype == Type::VIEWER)
		{
			return true;
		}

		$member = $member ?: new Member;
		$member->userid = $userid;
		$member->groupid = $this->id;
		$member->membertype = Type::VIEWER;
		$member->owner = 0;

		return $member->save();
	}

	/**
	 * Add a department
	 *
	 * @param   integer  $depid
	 * @return  bool
	 */
	public function addDepartment(int $depid)
	{
		$row = new GroupDepartment;
		$row->groupid = $this->id;
		$row->collegedeptid = $depid;
		$row->percentage = 100;

		return $row->save();
	}

	/**
	 * Add field of science
	 *
	 * @param   integer  $fid
	 * @return  bool
	 */
	public function addFieldOfScience(int $fid)
	{
		$row = new GroupFieldOfScience;
		$row->groupid = $this->id;
		$row->fieldofscienceid = $fid;
		$row->percentage = 100;

		return $row->save();
	}
}
