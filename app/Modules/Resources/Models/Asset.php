<?php

namespace App\Modules\Resources\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Modules\Resources\Events\AssetCreating;
use App\Modules\Resources\Events\AssetCreated;
use App\Modules\Resources\Events\AssetUpdating;
use App\Modules\Resources\Events\AssetUpdated;
use App\Modules\Resources\Events\AssetDeleted;
use App\Modules\History\Traits\Historable;
use App\Halcyon\Models\Casts\Params;

/**
 * Resource asset
 *
 * @property int    $id
 * @property string $name
 * @property Carbon|null $datetimecreated
 * @property Carbon|null $datetimeremoved
 * @property int    $parentid
 * @property int    $batchsystem
 * @property string $rolename
 * @property string $listname
 * @property int    $display
 * @property int    $resourcetype
 * @property int    $producttype
 * @property string $status
 * @property string $description
 * @property string $params
 */
class Asset extends Model
{
	use SoftDeletes, Historable;

	/**
	 * The name of the "created at" column.
	 *
	 * @var string|null
	 */
	const CREATED_AT = 'datetimecreated';

	/**
	 * The name of the "updated at" column.
	 *
	 * @var string|null
	 */
	const UPDATED_AT = null;

	/**
	 * The name of the "deleted at" column.
	 *
	 * @var string|null
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
	 * @var array<int,string>
	 */
	protected $guarded = [
		'id',
		'datetimecreated',
		'datetimeremoved',
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array<string,string>
	 */
	protected $casts = [
		'parentid' => 'integer',
		'display' => 'integer',
		'resourcetype' => 'integer',
		'producttype' => 'integer',
		'params' => Params::class,
	];

	/**
	 * The model's default values for attributes.
	 *
	 * @var array<string,int>
	 */
	protected $attributes = [
		'parentid' => 0
	];

	/**
	 * The event map for the model.
	 *
	 * @var array<string,string>
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
	public function getAliasAttribute(): string
	{
		return preg_replace('/[^a-z0-9\-_]/', '', strtolower($this->name));
	}

	/**
	 * Get picture
	 *
	 * @return  string
	 */
	public function getPictureAttribute(): string
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
	public function getThumbAttribute(): string
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
	 * Get mailing list
	 *
	 * @return  string
	 */
	public function getMailinglistAttribute(): string
	{
		$host = config('module.resources.email_lists_host');

		return $this->listname && $host ? $this->listname . '-users@' . $host : '';
	}

	/**
	 * Defines a relationship to type
	 *
	 * @return  BelongsTo
	 */
	public function type(): BelongsTo
	{
		return $this->belongsTo(Type::class, 'resourcetype')->withDefault(['id' => 0, 'name' => trans('global.none')]);
	}

	/**
	 * Defines a relationship to subresources
	 *
	 * @return  HasMany
	 */
	public function children(): HasMany
	{
		return $this->hasMany(Child::class, 'resourceid');
	}

	/**
	 * Defines a relationship to child assets
	 *
	 * @return  HasMany
	 */
	public function descendents(): HasMany
	{
		return $this->hasMany(self::class, 'parentid');
	}

	/**
	 * Defines a direct relationship to subresources
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
	 * @return  BelongsTo
	 */
	public function batchsystm(): BelongsTo
	{
		return $this->belongsTo(Batchsystem::class, 'batchsystem');
	}

	/**
	 * Defines a relationship to parent
	 *
	 * @return  BelongsTo
	 */
	public function parent(): BelongsTo
	{
		return $this->belongsTo(self::class, 'parentid');
	}

	/**
	 * Defines a relationship to facets
	 *
	 * @return  HasMany
	 */
	public function facets(): HasMany
	{
		return $this->hasMany(Facet::class, 'asset_id');
	}

	/**
	 * Defines a relationship to facets
	 *
	 * @param   string  $name
	 * @return  bool
	 */
	public function hasFacet($name): bool
	{
		$found = $this->getFacet($name);

		return $found ? true : false;
	}

	/**
	 * Defines a relationship to facets
	 *
	 * @param   string  $name
	 * @return  null|Facet
	 */
	public function getFacet($name): ?Facet
	{
		$f = (new Facet)->getTable();
		$ft = (new FacetType)->getTable();

		return $this->facets()
			->select($f . '.*')
			->join($ft, $ft . '.id', $f . '.facet_type_id')
			->where($ft . '.name', '=', $name)
			->where($ft . '.type_id', '=', $this->resourcetype)
			->first();
	}

	/**
	 * The "booted" method of the model.
	 *
	 * @return void
	 */
	protected static function booted(): void
	{
		static::creating(function ($model)
		{
			$result = self::query()
				->select(DB::raw('MAX(display) + 1 AS ordering'))
				->where('parentid', '=', $model->parentid)
				->first()
				->ordering;
			$result = $result ?: 1;

			$model->setAttribute('display', (int)$result);
		});

		static::deleted(function ($model)
		{
			foreach ($model->descendents as $child)
			{
				$child->delete();
			}

			foreach ($model->subresources as $subresource)
			{
				$subresource->delete();
			}
		});
	}

	/**
	 * Defines a relationship to parent
	 *
	 * @param   string  $order
	 * @param   string  $dir
	 * @return  array
	 */
	public function tree($order = 'name', $dir = 'asc'): array
	{
		$query = self::query();

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
	 * @param   int  $id        Parent ID
	 * @param   array    $list      List of records
	 * @param   array    $children  Container for parent/children mapping
	 * @param   int  $maxlevel  Maximum levels to descend
	 * @param   int  $level     Indention level
	 * @param   int  $type      Indention type
	 * @return  array
	 */
	protected function treeRecurse($id, $list, $children, $maxlevel=9999, $level=0, $type=1): array
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
	 * Set listname attribute
	 *
	 * @param  string  $value
	 * @return void
	 */
	public function setListnameAttribute($value): void
	{
		$value = strip_tags((string)$value);
		$value = str_replace(' ', '-', $value);

		$this->attributes['listname'] = $value;
	}

	/**
	 * Set rolename attribute
	 *
	 * @param  string  $value
	 * @return void
	 */
	public function setRolenameAttribute($value): void
	{
		$value = strip_tags((string)$value);
		$value = str_replace(' ', '-', $value);

		$this->attributes['rolename'] = $value;
	}

	/**
	 * Retrieve record by name
	 *
	 * @param   string  $name
	 * @return  Asset|null
	 */
	public static function findByName($name)
	{
		$name = str_replace('-', ' ', $name);

		return self::query()
			->withTrashed()
			->where('listname', '=', $name)
			->orWhere('name', '=', $name)
			->orderBy('datetimeremoved', 'asc') // look for non-trashed entries first
			->limit(1)
			->first();
	}
}
