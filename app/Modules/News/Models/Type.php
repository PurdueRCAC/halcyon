<?php

namespace App\Modules\News\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Halcyon\Traits\ErrorBag;
use App\Halcyon\Traits\Validatable;
use App\Modules\History\Traits\Historable;
use App\Modules\News\Events\TypeCreating;
use App\Modules\News\Events\TypeCreated;
use App\Modules\News\Events\TypeUpdating;
use App\Modules\News\Events\TypeUpdated;
use App\Modules\News\Events\TypeDeleted;
use Carbon\Carbon;

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
	public function setNameAttribute(string $value)
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
	public static function findByName(string $name, array $columns = ['*'])
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

	/**
	 * Generate basic stats for a given number of days
	 *
	 * @param   string  $start
	 * @param   string  $stop
	 * @return  array
	 */
	public function stats($start, $stop)
	{
		$start = Carbon::parse($start);
		$stop  = Carbon::parse($stop);
		$timeframe = round(($stop->timestamp - $start->timestamp) / (60 * 60 * 24));

		$a = (new Article)->getTable();
		$s = (new Association)->getTable();

		/*$now = Carbon::now();
		$placed = array();
		for ($d = $timeframe; $d >= 0; $d--)
		{
			$yesterday = Carbon::now()->modify('- ' . $d . ' days');
			$tomorrow  = Carbon::now()->modify(($d ? '- ' . ($d - 1) : '+ 1') . ' days');

			$item = [];
			$item['timestamp'] = $yesterday->timestamp;
			$item['count'] = Association::query()
				->select($s . '.newsid', $s . '.associd') //, $s . '.assoctype', DB::raw('COUNT(*) as total'))
				->join($a, $a . '.id', $s . '.newsid')
				->where($a . '.newstypeid', '=', $this->id)
				->where($s . '.assoctype', '=', 'user')
				->where($a . '.datetimenews', '>=', $yesterday->format('Y-m-d') . ' 00:00:00')
				->where($a . '.datetimenewsend', '<', $tomorrow->format('Y-m-d') . ' 00:00:00')
				->whereNull($s . '.datetimeremoved')
				->groupBy($s . '.newsid')
				->groupBy($s . '.associd')
				->get()
				->count();

			$placed[] = $item;
		}*/

		$assocs = Association::query()
			->select($s . '.newsid', $s . '.associd', $a . '.datetimenews', $a . '.datetimenewsend') //, $s . '.assoctype', DB::raw('COUNT(*) as total'))
			->join($a, $a . '.id', $s . '.newsid')
			->where($a . '.newstypeid', '=', $this->id)
			->where($s . '.assoctype', '=', 'user')
			->where($a . '.datetimenews', '>=', $start->format('Y-m-d') . ' 00:00:00')
			->where($a . '.datetimenewsend', '<', $stop->format('Y-m-d') . ' 00:00:00')
			->whereNull($s . '.datetimeremoved')
			//->groupBy($s . '.newsid')
			//->groupBy($s . '.associd')
			->get();

		$dates = array();
		$users = array();
		foreach ($assocs as $user)
		{
			$key = Carbon::parse($user->datetimenews)->format('Y-m-d');

			if (!isset($dates[$key]))
			{
				$dates[$key] = 0;
			}

			$dates[$key]++;

			if (!isset($users[$user->associd]))
			{
				$users[$user->associd] = 0;
			}

			$users[$user->associd]++;
		}

		arsort($users);

		foreach ($dates as $dt => $c)
		{
			$item = [];
			$item['timestamp'] = Carbon::parse($dt)->timestamp;
			$item['count'] = $c;

			$placed[] = $item;
		}

		$stats = array(
			'daily' => $placed,
			'users' => array_slice($users, 0, 10, true),
		);

		return $stats;
	}
}
