<?php

namespace App\Modules\Resources\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Modules\Resources\Events\AssetCreating;
use App\Modules\Resources\Events\AssetCreated;
use App\Modules\Resources\Events\AssetUpdating;
use App\Modules\Resources\Events\AssetUpdated;
use App\Modules\Resources\Events\AssetDeleted;
use App\Modules\History\Traits\Historable;

/**
 * Resource asset
 */
class Asset extends Model
{
	use SoftDeletes, Historable;

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
	protected $table = 'resources';

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
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	/*protected $dates = [
		'datetimecreated',
		'datetimeremoved'
	];*/

	/**
	 * The model's default values for attributes.
	 *
	 * @var array
	 */
	protected $attributes = [
		'parentid' => 0
	];

	/**
	 * The event map for the model.
	 *
	 * @var array
	 */
	protected $dispatchesEvents = [
		'creating' => AssetCreating::class,
		'created'  => AssetCreated::class,
		'updating' => AssetUpdating::class,
		'updated'  => AssetUpdated::class,
		'deleted'  => AssetDeleted::class,
	];

	/**
	 * Get alias
	 *
	 * @return  string
	 */
	public function getAliasAttribute()
	{
		return preg_replace('/[^a-z0-9\-_]/', '', strtolower($this->name));
	}

	/**
	 * Get picture
	 *
	 * @return  string
	 */
	public function getPictureAttribute()
	{
		$alias = $this->listname ? $this->listname : $this->alias;
		$path = storage_path('app/public/resources/' . $alias . '/resource.jpg');

		if (is_file($path))
		{
			return Storage::disk('public')->url('resources/' . $alias . '/resource.jpg');
		}

		return '';
	}

	/**
	 * Get thumbnail
	 *
	 * @return  string
	 */
	public function getThumbAttribute()
	{
		$alias = $this->listname ? $this->listname : $this->alias;
		$path = storage_path('app/public/resources/' . $alias . '/thumb.png');

		if (is_file($path))
		{
			return Storage::disk('public')->url('resources/' . $alias . '/thumb.png');
		}

		return '';
	}

	/**
	 * Determine if in a trashed state
	 *
	 * @return  bool
	 */
	public function isTrashed()
	{
		return ($this->datetimeremoved && $this->datetimeremoved != '0000-00-00 00:00:00' && $this->datetimeremoved != '-0001-11-30 00:00:00');
	}

	/**
	 * Defines a relationship to type
	 *
	 * @return  object
	 */
	public function type()
	{
		return $this->belongsTo(Type::class, 'resourcetype')->withDefault(['id' => 0, 'name' => trans('global.none')]);
	}

	/**
	 * Defines a relationship to subresources
	 *
	 * @return  object
	 */
	public function children()
	{
		return $this->hasMany(Child::class, 'resourceid');
	}

	/**
	 * Defines a relationship to subresources
	 *
	 * @return  object
	 */
	public function descendents()
	{
		return $this->hasMany(self::class, 'parentid');
	}

	/**
	 * Get the resource
	 *
	 * @return object
	 */
	public function subresources()
	{
		return $this->hasManyThrough(Subresource::class, Child::class, 'resourceid', 'id', 'id', 'subresourceid')->withTrashed();
	}

	/**
	 * Defines a relationship to subresources
	 *
	 * @return  object
	 */
	public function batchsystm()
	{
		return $this->belongsTo(Batchsystem::class, 'batchsystem');
	}

	/**
	 * Defines a relationship to parent
	 *
	 * @return  object
	 */
	public function parent()
	{
		return $this->belongsTo(self::class, 'parentid');
	}

	/**
	 * The "booted" method of the model.
	 *
	 * @return void
	 */
	protected static function booted()
	{
		static::creating(function ($model)
		{
			$result = self::query()
				->select(DB::raw('MAX(display) + 1 AS ordering'))
				->where('parentid', '=', $model->parentid)
				->get()
				->first()
				->ordering;
			$result = $result ?: 1;

			$model->setAttribute('display', (int)$result);
		});
	}

	/**
	 * Defines a relationship to parent
	 *
	 * @return  object
	 */
	public function tree($order = 'name', $dir = 'asc')
	{
		$query = self::query();
			//->where('datetimeremoved', '=', '0000-00-00 00:00:00');
		if ($this->id)
		{
			$query->where('id', '!=', $this->id);
		}
		$rows = $query
			->orderBy($order, $dir)
			->get();

		$list = array();

		if (count($rows) > 0)
		{
			$levellimit = 9999;
			$list       = array();
			$children   = array();

			// First pass - collect children
			foreach ($rows as $k)
			{
				$pt = $k->parentid;
				$list = @$children[$pt] ? $children[$pt] : array();
				array_push($list, $k);
				$children[$pt] = $list;
			}

			// Second pass - get an indent list of the items
			$list = $this->treeRecurse(0, array(), $children, max(0, $levellimit-1));
		}

		return $list;
	}

	/**
	 * Recursive function to build tree
	 *
	 * @param   integer  $id        Parent ID
	 * @param   array    $list      List of records
	 * @param   array    $children  Container for parent/children mapping
	 * @param   integer  $maxlevel  Maximum levels to descend
	 * @param   integer  $level     Indention level
	 * @param   integer  $type      Indention type
	 * @return  array
	 */
	protected function treeRecurse($id, $list, $children, $maxlevel=9999, $level=0, $type=1)
	{
		if (@$children[$id] && $level <= $maxlevel)
		{
			foreach ($children[$id] as $v)
			{
				$id = $v->id;
				$pt = $v->parentid;

				$list[$id] = $v;
				$list[$id]->level = $level;
				$list[$id]->children = isset($children[$id]) ? count(@$children[$id]) : 0;

				$list = $this->treeRecurse($id, $list, $children, $maxlevel, $level+1, $type);
			}
		}
		return $list;
	}

	/**
	 * Delete the model from the database.
	 *
	 * @return bool|null
	 *
	 * @throws \Exception
	 */
	public function setListnameAttribute($value)
	{
		$this->attributes['listname'] = (string)$value;
	}

	/**
	 * Delete the model from the database.
	 *
	 * @return bool|null
	 *
	 * @throws \Exception
	 */
	public function setRolenameAttribute($value)
	{
		$this->attributes['rolename'] = (string)$value;
	}

	/**
	 * Delete the model from the database.
	 *
	 * @return bool|null
	 *
	 * @throws \Exception
	 */
	public function delete()
	{
		$result = parent::delete();

		foreach ($this->children as $child)
		{
			$child->delete();
		}

		return $result;
	}

	/**
	 * Retrieve record by name
	 *
	 * @return  object
	 */
	public static function findByName($name)
	{
		$name = str_replace('-', ' ', $name);

		return self::query()
			->withTrashed()
			->where('listname', '=', $name)
			->orWhere('name', '=', $name)
			->limit(1)
			->get()
			->first();
	}

	/**
	 * Query scope where record isn't trashed
	 *
	 * @param   object  $query
	 * @return  object
	 */
	public function scopeWhereIsActive($query)
	{
		$t = $this->getTable();

		return $query->where(function($where) use ($t)
		{
			$where->whereNull($t . '.datetimeremoved')
					->orWhere($t . '.datetimeremoved', '=', '0000-00-00 00:00:00');
		});
	}

	/**
	 * Query scope where record is trashed
	 *
	 * @param   object  $query
	 * @return  object
	 */
	public function scopeWhereIsTrashed($query)
	{
		$t = $this->getTable();

		return $query->where(function($where) use ($t)
		{
			$where->whereNotNull($t . '.datetimeremoved')
				->where($t . '.datetimeremoved', '!=', '0000-00-00 00:00:00');
		});
	}
}
