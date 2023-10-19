<?php

namespace App\Modules\Software\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use App\Modules\History\Traits\Historable;
use App\Modules\Software\Events\ApplicationCreated;
use App\Modules\Software\Events\ApplicationUpdated;
use App\Modules\Software\Events\ApplicationDeleted;
use App\Modules\Software\Helpers\Formatter;
use Carbon\Carbon;

/**
 * Model for Application
 *
 * @property int    $id
 * @property int    $type_id
 * @property string $title
 * @property string $alias
 * @property string $description
 * @property string $content
 * @property int    $state
 * @property int    $access
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property string $api
 */
class Application extends Model
{
	use Historable, SoftDeletes;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'applications';

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'title';

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
		'id',
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var  array<string,string>
	 */
	protected $casts = [
		'published_at' => 'datetime:Y-m-d H:i:s',
	];

	/**
	 * The event map for the model.
	 *
	 * @var array<string,string>
	 */
	protected $dispatchesEvents = [
		'created' => ApplicationCreated::class,
		'updated' => ApplicationUpdated::class,
		'deleted' => ApplicationDeleted::class,
	];

	/**
	 * Boot
	 *
	 * @return  void
	 */
	public static function boot(): void
	{
		parent::boot();

		self::deleted(function($model)
		{
			foreach ($model->versions as $version)
			{
				$version->delete();
			}
		});
	}

	/**
	 * Get a list of versions
	 *
	 * @return  HasMany
	 */
	public function versions(): HasMany
	{
		return $this->hasMany(Version::class, 'application_id');
	}

	/**
	 * Get type
	 *
	 * @return  BelongsTo
	 */
	public function type(): BelongsTo
	{
		return $this->belongsTo(Type::class, 'type_id');
	}

	/**
	 * Set the type title
	 *
	 * @param   string $value
	 * @return  void
	 **/
	public function setTitleAttribute(string $value): void
	{
		$this->attributes['title'] = Str::limit(trim($value), 255);

		$alias = trim($this->attributes['title']);

		// Remove any '-' from the string since they will be used as concatenaters
		$alias = str_replace('-', ' ', $alias);
		$alias = preg_replace('/(\s|[^A-Za-z0-9\-])+/', '-', strtolower($alias));
		$alias = trim($alias, '-');

		$this->attributes['alias'] = $alias;
	}

	/**
	 * Set the type alias
	 *
	 * @param   string $value
	 * @return  void
	 **/
	public function setAliasAttribute(string $value): void
	{
		$alias = Str::limit(trim($value), 255);

		// Remove any '-' from the string since they will be used as concatenaters
		$alias = str_replace('-', ' ', $alias);
		$alias = preg_replace('/(\s|[^A-Za-z0-9\-])+/', '-', strtolower($alias));
		$alias = trim($alias, '-');

		$this->attributes['alias'] = $alias;
	}

	/**
	 * Format a publication
	 * 
	 * @return string
	 */
	public function toString(): string
	{
		return strip_tags($this->toHtml());
	}

	/**
	 * Format a publication as HTML
	 * 
	 * @return string
	 */
	public function toHtml(): string
	{
		return $this->content;
	}

	/**
	 * Is the record published
	 * 
	 * @return bool
	 */
	public function isPublished(): bool
	{
		return ($this->state == 1);
	}

	/**
	 * Is the record unpublished
	 * 
	 * @return bool
	 */
	public function isUnpublished(): bool
	{
		return !$this->isPublished();
	}

	/**
	 * Query scope with search
	 *
	 * @param   Builder  $query
	 * @param   string|int   $search
	 * @return  Builder
	 */
	public function scopeWhereSearch(Builder $query, $search): Builder
	{
		if (is_numeric($search))
		{
			$query->where('id', '=', $search);
		}
		else
		{
			$filters['search'] = strtolower((string)$search);

			$query->where(function ($where) use ($search)
			{
				$where->where('title', 'like', '%' . $search . '%')
					->orWhere('alias', 'like', '%' . $search . '%')
					->orWhere('description', 'like', '%' . $search . '%');
			});
		}

		return $query;
	}

	/**
	 * Query scope with state
	 *
	 * @param   Builder  $query
	 * @param   string   $state
	 * @return  Builder
	 */
	public function scopeWhereState(Builder $query, $state): Builder
	{
		switch ($state)
		{
			case 'unpublished':
				$query->where('state', '=', 0);
			break;

			case 'trashed':
				$query->onlyTrashed();
			break;

			case 'published':
			default:
				$query->where('state', '=', 1);
		}

		return $query;
	}

	/**
	 * Find all versions by resource
	 *
	 * @return array<string,array<int,Version>>
	 */
	public function versionsByResource(): array
	{
		$resources = array();

		foreach ($this->versions()->orderBy('title', 'asc')->get() as $version)
		{
			foreach ($version->resources as $asset)
			{
				if (!isset($resources[$asset->name]))
				{
					$resources[$asset->name] = array();
				}
				$resources[$asset->name][] = $version;
			}
		}

		ksort($resources);

		return $resources;
	}

	/**
	 * Find a record by an alias
	 *
	 * @param string $alias
	 * @return Application|null
	 */
	public static function findByAlias(string $alias): ?Application
	{
		return self::query()
			->where('alias', '=', $alias)
			->first();
	}
}
