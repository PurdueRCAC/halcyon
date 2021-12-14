<?php

namespace App\Modules\Storage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\Messages\Models\Message;
use App\Modules\Groups\Models\Group;
use App\Modules\Groups\Models\UnixGroup;
use App\Halcyon\Traits\ErrorBag;
use App\Halcyon\Traits\Validatable;
use App\Modules\History\Traits\Historable;
use App\Halcyon\Utility\Number;
use App\Modules\Storage\Events\DirectoryCreated;
use App\Modules\Storage\Events\DirectoryUpdated;
use App\Modules\Storage\Events\DirectoryDeleted;
use Carbon\Carbon;

/**
 * Storage model for a resource directory
 */
class Directory extends Model
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
	protected $table = 'storagedirs';

	/**
	 * Automatic fields to populate every time a row is created
	 *
	 * @var  array
	 */
	protected $dates = array(
		'datetimecreated',
		'datetimeremoved',
		'datetimeconfigured'
	);

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [
		'id',
		'datetimecreated',
		'datetimeremoved',
	];

	/**
	 * The event map for the model.
	 *
	 * @var array
	 */
	protected $dispatchesEvents = [
		'created'  => DirectoryCreated::class,
		'updated'  => DirectoryUpdated::class,
		'deleted'  => DirectoryDeleted::class,
	];

	/**
	 * Determine if configured
	 *
	 * @return  bool
	 */
	public function isConfigured()
	{
		return !is_null($this->datetimeconfigured);
	}

	/**
	 * Defines a relationship to a resource
	 *
	 * @return  object
	 */
	public function storageResource()
	{
		return $this->belongsTo(StorageResource::class, 'storageresourceid');
	}

	/**
	 * Defines a relationship to a group
	 *
	 * @return  object
	 */
	public function group()
	{
		return $this->belongsTo(Group::class, 'groupid');
	}

	/**
	 * Defines a relationship to a unixgroup
	 *
	 * @return  object
	 */
	public function unixgroup()
	{
		return $this->belongsTo(UnixGroup::class, 'unixgroupid');
	}

	/**
	 * Defines a relationship to a unixgroup
	 *
	 * @return  object
	 */
	public function autounixgroup()
	{
		return $this->belongsTo(UnixGroup::class, 'autouserunixgroupid');
	}

	/**
	 * Defines a relationship to a group
	 *
	 * @return  object
	 */
	public function owner()
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'owneruserid');
	}

	/**
	 * Defines a relationship to a group
	 *
	 * @return  object
	 */
	public function parent()
	{
		return $this->belongsTo(self::class, 'parentstoragedirid');
	}

	/**
	 * Defines a relationship to a group
	 *
	 * @return  object
	 */
	public function children()
	{
		return $this->hasMany(self::class, 'parentstoragedirid');
	}

	/**
	 * Defines a relationship to a group
	 *
	 * @return  object
	 */
	public function notifications()
	{
		return $this->hasMany(Notification::class, 'storagedirid');
	}

	/**
	 * Get a list of messages
	 *
	 * @return  object
	 */
	public function messages()
	{
		return $this->hasMany(Message::class, 'targetobjectid');
	}

	/**
	 * Add a message to the message queue
	 *
	 * @param   integer  $typeid
	 * @param   integer  $userid
	 * @param   integer  $offset
	 * @return  void
	 */
	public function addMessageToQueue($typeid = null, $userid = 0, $offset = 0)
	{
		$message = new Message;
		$message->userid = $userid ?: (auth()->user() ? auth()->user()->id : 0);
		$message->targetobjectid = $this->id;
		$message->messagequeuetypeid = !is_null($typeid) ? $typeid : $this->storageResource->getquotatypeid;
		$message->messagequeuetypeid = $message->messagequeuetypeid ?: $this->storageResource->createtypeid;
		if (!$message->messagequeuetypeid)
		{
			// We need a type.
			error_log('Trying to add message for tagret #' . $message->targetobjectid . ' without MQ type id.');
			return;
		}
		if ($offset)
		{
			$message->datetimesubmitted = Carbon::now()->add($offset . ' seconds')->toDateTimeString();
		}

		// Check for any pending messages
		// We want to avoid duplicates
		$exists = Message::query()
			->where('userid', '=', $message->userid)
			->where('targetobjectid', '=', $message->targetobjectid)
			->where('messagequeuetypeid', '=', $message->messagequeuetypeid)
			//->where('datetimesubmitted', '=', $message->datetimesubmitted)
			->whereNotCompleted()
			->first();

		if (!$exists)
		{
			$message->save();
		}
	}

	/**
	 * Get a list of usage
	 *
	 * @return  object
	 */
	public function usage()
	{
		return $this->hasMany(Usage::class, 'storagedirid');
	}

	/**
	 * Get permissions
	 *
	 * @return  object
	 */
	public function getFullPathAttribute()
	{
		$path = $this->storageResource ? $this->storageResource->path : '';

		/*while ($parent)
		{
			$parent = $this->parentl;
			$path .= '/' . 
		}*/
		$path .= $this->path ? '/' . $this->path : '';

		return $path;
	}

	/**
	 * Get permissions
	 *
	 * @return  object
	 */
	public function getUnixPermissionsAttribute()
	{
		/*$permissions = [
			'user' => [
				'read' => $this->ownerread,
				'write' => $this->ownerwrite,
				'execute' => $this->ownerread,
			],
			'group' => [
				'read' => $this->groupread,
				'write' => $this->groupwrite,
				'execute' => $this->groupread,
			],
			'other' => [
				'read' => $this->publicread,
				'write' => $this->publicwrite,
				'execute' => $this->publicread,
			],
		];*/
		$permissions = new \stdClass;

		$permissions->user = new \stdClass;
		$permissions->user->read = $this->ownerread;
		$permissions->user->write = $this->ownerwrite;
		$permissions->user->execute = $this->ownerread;

		$permissions->group = new \stdClass;
		$permissions->group->read = $this->groupread;
		$permissions->group->write = $this->groupwrite;
		$permissions->group->execute = $this->groupread;

		$permissions->other = new \stdClass;
		$permissions->other->read = $this->publicread;
		$permissions->other->write = $this->publicwrite;
		$permissions->other->execute = $this->publicread;

		return (object)$permissions;
	}

	/**
	 * Get mode
	 *
	 * @return  string
	 */
	public function getModeAttribute()
	{
		$permissions = $this->unixPermissions;

		$umode = 0;
		$gmode = 0;
		$omode = 0;

		if ($permissions->user->read == '1')
		{
			$umode += 4;
		}

		if ($permissions->user->write == '1')
		{
			$umode += 2;
		}

		if ($permissions->user->read == '1')
		{
			$umode += 1;
		}

		if ($permissions->group->read == '1')
		{
			$gmode += 4;
		}

		if ($permissions->group->write == '1')
		{
			$gmode += 2;
		}

		if ($permissions->group->read == '1')
		{
			$gmode += 1;
		}

		if ($permissions->other->read == '1')
		{
			$omode += 4;
		}

		if ($permissions->other->write == '1')
		{
			$omode += 2;
		}

		if ($permissions->other->read == '1')
		{
			$omode += 1;
		}

		return $umode . $gmode . $omode;
	}

	/**
	 * Get ACL
	 *
	 * @return  string
	 */
	public function getAclAttribute()
	{
		$permissions = $this->unixPermissions;

		$uacl = '';
		$gacl = '';
		$oacl = '';

		if ($permissions->user->read == '1')
		{
			$uacl .= 'r';
		}

		if ($permissions->user->write == '1')
		{
			$uacl .= 'w';
		}

		if ($permissions->user->read == '1')
		{
			$uacl .= 'X';
		}

		if ($permissions->group->read == '1')
		{
			$gacl .= 'r';
		}

		if ($permissions->group->write == '1')
		{
			$gacl .= 'w';
		}

		if ($permissions->group->read == '1')
		{
			$gacl .= 'X';
		}

		if ($permissions->other->read == '1')
		{
			$oacl .= 'r';
		}

		if ($permissions->other->write == '1')
		{
			$oacl .= 'w';
		}

		if ($permissions->other->read == '1')
		{
			$oacl .= 'X';
		}

		if ($uacl == '')
		{
			$uacl = '0';
		}

		if ($gacl == '')
		{
			$gacl = '0';
		}

		if ($oacl == '')
		{
			$oacl = '0';
		}

		return 'd:u::' . $uacl . ',d:g::' . $gacl . ',d:o::' . $oacl;
	}

	/**
	 * Get formatted bytes
	 *
	 * @return  string
	 */
	public function getQuotaAttribute()
	{
		return Number::formatBytes($this->bytes);
	}

	/**
	 * Get storage buckets
	 *
	 * @return  array
	 */
	public function getBucketsAttribute()
	{
		// Fetch storage buckets under this group
		$bucket = null;
		//$now = Carbon::now();

		$purchases = Purchase::query()
			->where('groupid', $this->groupid)
			->where('resourceid', $this->resourceid)
			->whenAvailable()
			->get();

		if (count($purchases))
		{
			$bucket = array(
				'resourceid'  => $this->resourceid,
				'soldbytes'   => 0,
				'loanedbytes' => 0,
				'totalbytes'  => 0,
			);

			foreach ($purchases as $purchase)
			{
				/*if (!isset($buckets[$purchase->resourceid]))
				{
					$buckets[$purchase->resourceid] = array(
						'resourceid'  => $purchase->resourceid,
						'soldbytes'   => 0,
						'loanedbytes' => 0,
						'totalbytes'  => 0,
					);
				}
				$buckets[$purchase->resourceid]['soldbytes'] += $row->bytes;
				$buckets[$purchase->resourceid]['totalbytes'] += $row->bytes;*/
				$bucket['soldbytes']  += $row->bytes;
				$bucket['totalbytes'] += $row->bytes;
			}
		}

		return $bucket;
	}

	/**
	 * Get resource total
	 *
	 * @return  array
	 */
	public function getResourceTotalAttribute()
	{
		// Fetch storage buckets under this group
		//$bucket = null;
		//$now = Carbon::now();

		$purchases = Purchase::query()
			->where('groupid', $this->groupid)
			->where('resourceid', $this->resourceid)
			->get();

		$loans = Loan::query()
			->where('groupid', $this->groupid)
			->where('resourceid', $this->resourceid)
			->get();

		$items = $purchases->merge($loans);

		$increments = array();

		foreach ($items as $purchase)
		{
			if ($purchase->start)
			{
				if (!isset($increments[strtotime($purchase->start)]))
				{
					$increments[strtotime($purchase->start)] = 0;
				}

				$increments[strtotime($purchase->start)] += $purchase->bytes;
			}

			if ($purchase->stop)
			{
				if (!isset($increments[strtotime($purchase->stop)]))
				{
					$increments[strtotime($purchase->stop)] = 0;
				}

				$increments[strtotime($purchase->stop)] -= $purchase->bytes;
			}
		}

		ksort($increments);

		$totals = array();
		$storagedirtotals = array();
		$total = 0;
		foreach ($increments as $time => $inc)
		{
			$total += $inc;
			$totals[$time] = $total;
		}

		foreach ($totals as $time => $total)
		{
			array_push($storagedirtotals, array(
				'time'  => date('Y-m-d H:i:s', $time),
				'bytes' => $total
			));
		}

		return $storagedirtotals;
	}

	/**
	 * Get future quotas
	 *
	 * @return  array
	 */
	public function getFuturequotasAttribute()
	{
		// Find appropriate bucket
		$this_bucket = $this->bucket;
		$futurequotas = array();

		if ($this->bytes && $this_bucket != null)
		{
			// Set up future quota information
			foreach ($this->resourceTotal as $total)
			{
				// Is this a future quota?
				if ($total['time'] > $now->toDateTimeString())
				{
					// Will this oversubscribe us?
					if ($allocated[$this->resourceid] > $total['bytes'])
					{
						$future_quota = array();
						$future_quota['time']  = $total['time'];
						$future_quota['quota'] = $this->bytes + round(($this->bytes / $this_bucket['totalbytes']) * ($total['bytes'] - $allocated[$this->resourceid]));

						array_push($futurequotas, $future_quota);
					}
				}
			}
		}

		return $futurequotas;
	}

	/**
	 * Get directory tree
	 *
	 * @param   bool   $expanded
	 * @return  array
	 */
	public function tree($expanded = true, $active = [])
	{
		$item = array(); //$this->toArray();
		$item['id'] = $this->id;
		$item['data'] = $this->toArray();
		$item['title'] = $this->name;
		$item['folder'] = true;
		$item['expanded'] = $expanded;
		$item['quota'] = $this->quota;

		$children = array();
		foreach ($this->children()->orderBy('name', 'asc')->get() as $child)
		{
			$children[] = $child->tree(in_array($child->id, $active), $active);
		}

		$new_quota = $this->quota;
		if (!$this->bytes)
		{
			$new_quota = $this->parent ? $this->parent->quota : 0;
		}

		$children[] = array(
			'title' => trans('storage::storage.add new directory'),
			'folder' => false,
			'expanded' => false,
			'id'   => 'new_dir',
			'data'  => array(
				'parentdir'       => $this->id,
				'parentunixgroup' => $this->unixgroup ? $this->unixgroup->longname : null,
				'path'            => $this->path,
				'parentquota'     => $new_quota
			)
		);

		$item['children'] = $children;

		return $item;
	}

	/**
	 * Get nested directory tree
	 *
	 * @param   array  $items
	 * @return  array
	 */
	public function nested($items = array())
	{
		$items[] = $this;

		foreach ($this->children()->orderBy('name', 'asc')->get() as $child)
		{
			$items = $child->nested($items);
		}

		return $items;
	}

	/**
	 * Set value in bytes
	 *
	 * @param   mixed  $value
	 * @return  void
	 */
	public function setBytesAttribute($value)
	{
		$this->attributes['bytes'] = Number::toBytes($value);
	}

	/**
	 * Get bytes in human readable format
	 *
	 * @return  string
	 */
	public function getFormattedBytesAttribute()
	{
		return Number::formatBytes($this->bytes);
	}

	/**
	 * The "booted" method of the model.
	 *
	 * @return void
	 */
	protected static function booted()
	{
		static::deleted(function ($model)
		{
			if ($model->parentstoragedirid)
			{
				return;
			}

			// Look for other, active top-level dirs for the
			// same group and resource. If there are any, don't
			// go any further.
			$others = self::query()
				->where('groupid', $model->groupid)
				->where('resourceid', $model->resourceid)
				->where('parentstoragedirid', '=', 0)
				->where('id', '!=', $model->id)
				->count();

			if ($others)
			{
				return;
			}

			// End any loans or purchases
			$purchases = Purchase::query()
				->where('groupid', $model->groupid)
				->where('resourceid', $model->resourceid)
				->get();

			foreach ($purchases as $purchase)
			{
				$counter = $purchase->counter;

				$purchase->delete();

				if ($purchase->sellergroupid && $counter)
				{
					$counter->delete();
				}
			}

			$loans = Loan::query()
				->where('groupid', $model->groupid)
				->where('resourceid', $model->resourceid)
				->get();

			foreach ($loans as $loan)
			{
				$counter = $loan->counter;

				$loan->delete();

				if ($loan->lendergroupid && $counter)
				{
					$counter->delete();
				}
			}
		});
	}
}
