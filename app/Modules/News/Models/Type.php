<?php

namespace App\Modules\News\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Halcyon\Traits\ErrorBag;
use App\Halcyon\Traits\Validatable;
use App\Modules\History\Traits\Historable;
use App\Modules\News\Events\TypeCreating;
use App\Modules\News\Events\TypeCreated;
use App\Modules\News\Events\TypeUpdating;
use App\Modules\News\Events\TypeUpdated;
use App\Modules\News\Events\TypeDeleted;

/**
 * Model for news type
 */
class Type extends Model
{
	use ErrorBag, Validatable, Historable;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'newstypes';

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = false;

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
	 * The model's default values for attributes.
	 *
	 * @var array
	 */
	protected $attributes = [
		'future' => 0,
		'ongoing' => 0,
		'location' => 0,
		'tagresources' => 0
	];

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [
		'id',
	];

	/**
	 * Fields and their validation criteria
	 *
	 * @var array
	 */
	protected $rules = array(
		'name' => 'required'
	);

	/**
	 * The event map for the model.
	 *
	 * @var array
	 */
	protected $dispatchesEvents = [
		'creating' => TypeCreating::class,
		'created'  => TypeCreated::class,
		'updating' => TypeUpdating::class,
		'updated'  => TypeUpdated::class,
		'deleted'  => TypeDeleted::class,
	];

	/**
	 * Split event into plugin name and event
	 *
	 * @param   array   $data  the data being saved
	 * @return  string
	 **/
	public function setNameAttribute($value)
	{
		$this->attributes['name'] = Str::limit($value, 32);
	}

	/**
	 * Split event into plugin name and event
	 *
	 * @return  string
	 **/
	public function getAliasAttribute()
	{
		$alias = trim($this->name);

		// Remove any '-' from the string since they will be used as concatenaters
		$alias = str_replace('-', ' ', $alias);
		$alias = preg_replace('/(\s|[^A-Za-z0-9\-])+/', '-', strtolower($alias));
		$alias = trim($alias, '-');

		return $alias;
	}

	/**
	 * Runs extra setup code when creating a new model
	 *
	 * @return  void
	 */
	protected static function boot()
	{
		parent::boot();

		static::saving(function ($model)
		{
			if (in_array(strtolower($model->name), ['rss', 'search', 'calendar']))
			{
				$model->addError(trans('":name" is reserved and cannot be used.', ['name' => $model->name]));
				return false;
			}

			$exist = self::query()
				->where('name', '=', $model->name)
				->where('id', '!=', $model->id)
				->first();

			if ($exist && $exist->id)
			{
				$model->addError(trans('An entry with the name ":name" already exists.', ['name' => $model->name]));
				return false;
			}

			return true;
		});
	}

	/**
	 * Defines a relationship to articles
	 *
	 * @return  object
	 */
	public function articles()
	{
		return $this->hasMany(Article::class, 'newstypeid');
	}

	/**
	 * Find a model by its primary key.
	 *
	 * @param  mixed  $id
	 * @param  array  $columns
	 * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|static[]|static|null
	 */
	public static function findByName($name, $columns = ['*'])
	{
		$name = str_replace('-', ' ', $name);

		return static::query()
			->where('name', '=', $name)
			->orWhere('name', 'like', '%' . $name . '%')
			->first($columns);
	}

	/**
	 * Delete the record and all associated data
	 *
	 * @param   array    $options
	 * @return  boolean  False if error, True on success
	 */
	public function delete(array $options = [])
	{
		// Remove children
		foreach ($this->articles as $article)
		{
			if (!$article->delete($options))
			{
				$this->addError($article->getError());
				return false;
			}
		}

		// Attempt to delete the record
		return parent::delete($options);
	}

	/**
	 * Generate subscribe calendar link
	 *
	 * @return  string
	 */
	public function getRssLinkAttribute()
	{
		return route('site.news.feed', ['name' => $this->name]);
	}

	/**
	 * Generate subscribe calendar link
	 *
	 * @return  string
	 */
	public function getSubscribeCalendarLinkAttribute()
	{
		return preg_replace('/^https?:\/\//', 'webcal://', route('site.news.calendar', ['name' => strtolower($this->name)]));
	}

	/**
	 * Generate download calendar link
	 *
	 * @return  string
	 */
	public function getDownloadCalendarLinkAttribute()
	{
		return route('site.news.calendar', ['name' => strtolower($this->name)]);
	}
}
