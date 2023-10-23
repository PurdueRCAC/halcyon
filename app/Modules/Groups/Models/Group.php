<?php
namespace App\Modules\Groups\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
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
use App\Modules\Users\Models\User;
use Carbon\Carbon;

/**
 * Group model
 *
 * @property int    $id
 * @property string $name
 * @property int    $owneruserid
 * @property string $unixgroup
 * @property int    $unixid
 * @property string $githuborgname
 * @property Carbon|null $datetimecreated
 * @property Carbon|null $datetimeremoved
 * @property int    $cascademanagers
 * @property int    $prefix_unixgroup
 * @property string $description
 *
 * @property string $api
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
	 * @return  BelongsTo
	 */
	public function owner(): BelongsTo
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'owneruserid');
	}

	/**
	 * Determine if a user is a manager
	 *
	 * @param   User  $user
	 * @return  bool  True if modified, false if not
	 */
	public function isManager(User $user): bool
	{
		$managers = $this->managers->pluck('userid')->toArray();
		return in_array($user->id, $managers);
	}

	/**
	 * Department
	 *
	 * @return  HasMany
	 */
	public function departments(): HasMany
	{
		return $this->hasMany(GroupDepartment::class, 'groupid');
		//return $this->hasOneThrough(Department::class, GroupDepartment::class, 'groupid', 'id', 'groupid', 'collegedeptid');
	}

	/**
	 * Get a list of fields of science
	 *
	 * @return  HasMany
	 */
	public function fieldsOfScience(): HasMany
	{
		return $this->hasMany(GroupFieldOfScience::class, 'groupid');
	}

	/**
	 * Departments
	 *
	 * @return  HasManyThrough
	 */
	public function departmentList(): HasManyThrough
	{
		return $this->hasManyThrough(Department::class, GroupDepartment::class, 'groupid', 'id', 'id', 'collegedeptid');
	}

	/**
	 * Get a list of users
	 *
	 * @return  HasMany
	 */
	public function members(): HasMany
	{
		return $this->hasMany(Member::class, 'groupid');
	}

	/**
	 * Get a list of managers
	 *
	 * @return  HasMany
	 */
	/*public function managers(): HasMany
	{
		return $this->hasMany(Member::class, 'groupid')->where('membertype', '=', 2);
	}*/

	/**
	 * Get a list of managers
	 *
	 * @return  Collection
	 */
	public function getManagersAttribute(): Collection
	{
		$m = (new Member)->getTable();
		$u = (new \App\Modules\Users\Models\UserUsername)->getTable();

		$managers = $this->members()
			->withTrashed()
			->select($m . '.*')
			->join($u, $u . '.userid', $m . '.userid')
			->whereNull($u . '.dateremoved')
			->whereNull($m . '.dateremoved')
			->where($m . '.membertype', '=', Type::MANAGER)
			->orderBy($m . '.datecreated', 'desc')
			->get();

		return $managers;
	}

	/**
	 * Get a list of "message of the day"
	 *
	 * @return  HasMany
	 */
	public function motds(): HasMany
	{
		return $this->hasMany(Motd::class, 'groupid');
	}

	/**
	 * Get a list of storage directories
	 *
	 * @return  HasMany
	 */
	public function directories(): HasMany
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
	 * @return  array<int,array{resourceid:int,soldbytes:int,loanedbytes:int,totalbytes:int,unallocatedbytes:int,allocatedbytes:int}>
	 */
	public function getStorageBucketsAttribute(): array
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
				->first();

			$allocatedbytes = 0;

			if ($b)
			{
				$allocatedbytes = $b->bytes;
			}

			$bucket['unallocatedbytes'] = abs($bucket['totalbytes'] - $allocated[$bucket['resourceid']]);
			$bucket['allocatedbytes'] = $allocatedbytes;
		}

		return $storagebuckets;*/
	}

	/**
	 * Get a list of storage loans
	 *
	 * @return  HasMany
	 */
	public function loans(): HasMany
	{
		return $this->hasMany(Loan::class, 'groupid');
	}

	/**
	 * Get a list of storage purchases
	 *
	 * @return  HasMany
	 */
	public function purchases(): HasMany
	{
		return $this->hasMany(Purchase::class, 'groupid');
	}

	/**
	 * Get a list of queues
	 *
	 * @return  HasMany
	 */
	public function queues(): HasMany
	{
		return $this->hasMany(Queue::class, 'groupid');
	}

	/**
	 * Get a list of unix groups
	 *
	 * @return  HasMany
	 */
	public function unixGroups(): HasMany
	{
		return $this->hasMany(UnixGroup::class, 'groupid');
	}

	/**
	 * Get the primary unix group
	 *
	 * @return  UnixGroup|null
	 */
	public function getPrimaryUnixGroupAttribute(): ?UnixGroup
	{
		return $this->unixGroups()
			->where('longname', '=', $this->unixgroup)
			->first();
	}

	/**
	 * Get a list of "message of the day"
	 *
	 * @return  Motd|null
	 */
	public function getMotdAttribute(): ?Motd
	{
		return $this->motds()
			->orderBy('datetimecreated', 'desc')
			->first();
	}

	/**
	 * Get a list of resources
	 *
	 * @return  Collection
	 */
	public function getResourcesAttribute(): Collection
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

		return new Collection(array_values($resources));
	}

	/**
	 * Get a list of prior (trashed) resources
	 *
	 * @return  Collection
	 */
	public function getPriorResourcesAttribute(): Collection
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

		return new Collection(array_values($resources));
	}

	/**
	 * Get a count of pending memberships
	 *
	 * @return  int
	 */
	public function getPendingMembersCountAttribute(): int
	{
		$q = (new Queue)->getTable();
		$s = (new \App\Modules\Resources\Models\Child)->getTable();
		$r = (new \App\Modules\Resources\Models\Asset)->getTable();

		$queues = $this->queues()
			//->with('users')
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
			/*$queueids = $queue->users
				->sortBy('membertype');*/

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

			$users = $queueids->where('membertype', '=', \App\Modules\Queues\Models\MemberType::PENDING);

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
						$me->setAsMember();
						$me->doNotNotify();
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
	 * @return  Group|null
	 */
	public static function findByName(string $name): ?Group
	{
		return self::query()
			->where('name', '=', $name)
			->first();
	}

	/**
	 * Get a list of "message of the day"
	 *
	 * @param   string  $unixgroup
	 * @return  Group|null
	 */
	public static function findByUnixgroup(string $unixgroup): ?Group
	{
		return self::query()
			->where('unixgroup', '=', $unixgroup)
			->first();
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

		return parent::delete();
	}

	/**
	 * Add user as a manager
	 *
	 * @param   int  $userid
	 * @param   int  $owner
	 * @return  bool
	 */
	public function addManager(int $userid, int $owner = 0): bool
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
	 * @param   int  $userid
	 * @return  bool
	 */
	public function addMember(int $userid): bool
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
	 * @param   int  $userid
	 * @return  bool
	 */
	public function addViewer(int $userid): bool
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
	 * Remove user
	 *
	 * @param   int  $userid
	 * @return  bool
	 */
	public function removeMember(int $userid): bool
	{
		$member = Member::findByGroupAndUser($this->id, $userid);

		if (!$member)
		{
			return true;
		}

		return $member->delete();
	}

	/**
	 * Add a department
	 *
	 * @param   int  $depid
	 * @return  bool
	 */
	public function addDepartment(int $depid): bool
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
	 * @param   int  $fid
	 * @return  bool
	 */
	public function addFieldOfScience(int $fid): bool
	{
		$row = new GroupFieldOfScience;
		$row->groupid = $this->id;
		$row->fieldofscienceid = $fid;
		$row->percentage = 100;

		return $row->save();
	}

	/**
	 * Query scope with search
	 *
	 * @param   Builder  $query
	 * @param   string   $search
	 * @return  Builder
	 */
	public function scopeWhereSearch(Builder $query, $search): Builder
	{
		if ($search)
		{
			$g = $this->getTable();

			if (is_numeric($search))
			{
				$query->where($g . '.id', '=', $search);
			}
			else
			{
				$search = trim((string)$search);
				$search = strtolower($search);
				$search = preg_replace('/ +/', ' ', $search);

				// Skip matches on trailing "group" or we'll return a billion results
				if (preg_match('/Group$/i', $search))
				{
					$search = preg_replace('/Group$/i', '', $search);
				}

				$query->where(function ($where) use ($search, $g)
				{
					$where->where($g . '.name', 'like', '%' . $search . '%')
						->orWhere($g . '.unixgroup', 'like', '%' . $search . '%');
				});
			}
		}

		return $query;
	}

	/**
	 * Query scope with department
	 *
	 * @param   Builder  $query
	 * @param   int  $department
	 * @return  Builder
	 */
	public function scopeWhereDepartment(Builder $query, $department): Builder
	{
		$dep = Department::find($department);

		if ($dep)
		{
			// We need to include all children of this department
			//
			// This handles cases where the group is tagged with a child department
			// and filtering by its parent department should include it
			$deps = $dep->children->pluck('id')->toArray();
			$deps[] = $department;

			$g = $this->getTable();
			$gd = (new GroupDepartment)->getTable();

			$query->join($gd, $gd . '.groupid', $g . '.id')
				->whereIn($gd . '.collegedeptid', $deps);
		}

		return $query;
	}

	/**
	 * Query scope with field of science
	 *
	 * @param   Builder  $query
	 * @param   int  $field
	 * @return  Builder
	 */
	public function scopeWhereFieldOfScience(Builder $query, $field): Builder
	{
		$g = $this->getTable();
		$gf = (new GroupFieldOfScience)->getTable();

		$query->join($gf, $gf . '.groupid', $g . '.id')
			->where($gf . '.fieldofscienceid', $field);

		return $query;
	}
}
