<?php

namespace App\Modules\Software\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * Model for application types
 *
 * @property int    $id
 * @property string $title
 * @property string $alias
 *
 * @property string $api
 */
class Type extends Model
{
	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'application_types';

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
	 * Boot
	 *
	 * @return  void
	 */
	public static function boot(): void
	{
		parent::boot();

		self::deleted(function($model)
		{
			foreach ($model->applications as $application)
			{
				$application->delete();
			}
		});
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
	 * Get a list of applications
	 *
	 * @return  HasMany
	 */
	public function applications(): HasMany
	{
		return $this->hasMany(Application::class, 'type_id');
	}

	/**
	 * Query scope with search
	 *
	 * @param   Builder  $query
	 * @param   string|int  $search
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
					->orWhere('alias', 'like', '%' . $search . '%');
			});
		}

		return $query;
	}
}
