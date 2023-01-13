<?php

namespace App\Modules\News\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Modules\Tags\Models\Tagged;
use App\Modules\History\Traits\Historable;
use App\Modules\News\Events\TypeCreated;
use App\Modules\News\Events\TypeUpdated;
use App\Modules\News\Events\TypeDeleted;
use Carbon\Carbon;

/**
 * Model for news type
 */
class Type extends Model
{
	use Historable;

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
	public static $orderBy = 'ordering';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public static $orderDir = 'asc';

	/**
	 * The model's default values for attributes.
	 *
	 * @var array<string,int>
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
	 * @var array<int,string>
	 */
	protected $guarded = [
		'id',
	];

	/**
	 * Fields and their validation criteria
	 *
	 * @var array<string,string>
	 */
	protected $rules = array(
		'name' => 'required'
	);

	/**
	 * The event map for the model.
	 *
	 * @var array<string,string>
	 */
	protected $dispatchesEvents = [
		'created'  => TypeCreated::class,
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
		$this->attributes['name'] = Str::limit(trim($value), 32);

		$alias = trim($this->attributes['name']);

		// Remove any '-' from the string since they will be used as concatenaters
		$alias = str_replace('-', ' ', $alias);
		$alias = preg_replace('/(\s|[^A-Za-z0-9\-])+/', '-', strtolower($alias));
		$alias = trim($alias, '-');

		$this->attributes['alias'] = $alias;
	}

	/**
	 * Split event into plugin name and event
	 *
	 * @return  string
	 **/
	/*public function setNameAttribute($value)
	{
		$alias = trim($value);

		// Remove any '-' from the string since they will be used as concatenaters
		$alias = str_replace('-', ' ', $alias);
		$alias = preg_replace('/(\s|[^A-Za-z0-9\-])+/', '-', strtolower($alias));
		$alias = trim($alias, '-');

		return $alias;
	}*/

	/**
	 * Runs extra setup code when creating a new model
	 *
	 * @return  void
	 */
	protected static function boot()
	{
		parent::boot();

		static::creating(function ($model)
		{
			$result = self::query()
				->select(DB::raw('MAX(ordering) + 1 AS seq'))
				->where('parentid', '=', $model->parentid)
				->get()
				->first()
				->seq;

			$model->setAttribute('ordering', (int)$result);
		});

		static::saving(function ($model)
		{
			if (in_array(strtolower($model->name), ['rss', 'manage', 'search', 'calendar']))
			{
				throw new \Exception(trans('":name" is reserved and cannot be used.', ['name' => $model->name]));
			}

			$exist = self::query()
				->where('name', '=', $model->name)
				->where('id', '!=', $model->id)
				->first();

			if ($exist && $exist->id)
			{
				throw new \Exception(trans('An entry with the name ":name" already exists.', ['name' => $model->name]));
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
	 * Defines a relationship to articles of this type
	 *
	 * @return  object
	 */
	public function allArticles()
	{
		$ids = array_merge([$this->id], $this->children->pluck('id')->toArray());

		return Article::query()->whereIn('newstypeid', $ids);
	}

	/**
	 * Defines a relationship to child types
	 *
	 * @return  object
	 */
	public function children()
	{
		return $this->hasMany(self::class, 'parentid');
	}

	/**
	 * Defines a relationship to parent type
	 *
	 * @return  object
	 */
	public function parent()
	{
		return $this->belongsTo(self::class, 'parentid');
	}

	/**
	 * Convert to HTML
	 *
	 * @return  string
	 */
	public function toHtml()
	{
		return '<p>' . $this->name . '</p>';
	}

	/**
	 * Find a model by its name.
	 *
	 * @param  string $name
	 * @param  array  $columns
	 * @return \Illuminate\Database\Eloquent\Model|null
	 */
	public static function findByName(string $name, array $columns = ['*'])
	{
		$result = static::query()
			->where('alias', '=', $name)
			->first($columns);

		if (!$result)
		{
			$name = str_replace('-', ' ', $name);

			$result = static::query()
				->where('name', '=', $name)
				->first($columns);

			if (!$result)
			{
				$result = static::query()
					->where('name', 'like', $name . '%')
					->orderBy('parentid', 'asc')
					->first($columns);
			}
		}

		return $result;
	}

	/**
	 * Delete the record and all associated data
	 *
	 * @return  boolean  False if error, True on success
	 */
	public function delete()
	{
		foreach ($this->articles as $article)
		{
			$article->delete();
		}

		foreach ($this->children as $child)
		{
			$child->delete();
		}

		// Attempt to delete the record
		return parent::delete();
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
	 * Get the list of types as a tree
	 *
	 * @param  string $order Field to sort by
	 * @param  string $dir   Direction to sort
	 * @return array
	 */
	public static function tree(string $order = 'ordering', string $dir = 'asc')
	{
		$rows = self::query()
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

				if (!isset($children[$pt]))
				{
					$children[$pt] = array();
				}
				$children[$pt][] = $k;
			}

			// Second pass - get an indent list of the items
			$list = self::treeRecurse(0, $list, $children, max(0, $levellimit-1));
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
	 * @param   string   $prfx
	 * @return  array
	 */
	protected static function treeRecurse(int $id, array $list, array $children, int $maxlevel=9999, int $level=0, int $type=1, string $prfx = '')
	{
		if (@$children[$id] && $level <= $maxlevel)
		{
			foreach ($children[$id] as $z => $v)
			{
				$vid = $v->id;
				$pt = $v->parentid;

				$list[$vid] = $v;
				$list[$vid]->name = $list[$vid]->name;
				$list[$vid]->level = $level;
				$list[$vid]->children_count = isset($children[$vid]) ? count(@$children[$vid]) : 0;

				$p = '';
				if ($v->parentid)
				{
					$p = $list[$vid]->prefix . $list[$vid]->name;
				}

				unset($children[$id][$z]);

				$list = self::treeRecurse($vid, $list, $children, $maxlevel, $level+1, $type, $p);
			}
			unset($children[$id]);
		}
		return $list;
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

		$stats['reservations'] = count($assocs);

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

		$repeat_users = count($users);

		arsort($users);

		foreach ($dates as $dt => $c)
		{
			$item = [];
			$item['timestamp'] = Carbon::parse($dt)->timestamp;
			$item['count'] = $c;

			$placed[] = $item;
		}

		$canceled = Association::query()
			->withTrashed()
			->select($s . '.newsid', $s . '.associd', $a . '.datetimenews', $a . '.datetimenewsend')
			->join($a, $a . '.id', $s . '.newsid')
			->where($a . '.newstypeid', '=', $this->id)
			->where($s . '.assoctype', '=', 'user')
			->where($a . '.datetimenews', '>=', $start->format('Y-m-d') . ' 00:00:00')
			->where($a . '.datetimenewsend', '<', $stop->format('Y-m-d') . ' 00:00:00')
			->whereNotNull($s . '.datetimeremoved')
			->count();

		$r = (new Tagged)->getTable();
		$c = (new Association)->getTable();

		$tags = Tagged::query()
			->select($r . '.tag_id', DB::raw('COUNT(*) as total'))
			->join($c, $c . '.id', $r . '.taggable_id')
			->where($r . '.taggable_type', '=', Association::class)
			->where($c . '.datetimecreated', '>=', $start->format('Y-m-d') . ' 00:00:00')
			->where($c . '.datetimecreated', '<', $stop->format('Y-m-d') . ' 00:00:00')
			->groupBy($r . '.tag_id')
			->orderBy('total', 'desc')
			->limit(10)
			->get();

		$stats = array(
			'reservations' => count($assocs),
			'repeat_users' => $repeat_users,
			'canceled' => $canceled,
			'daily' => $placed,
			'users' => array_slice($users, 0, 10, true),
			'tags' => $tags,
		);

		return $stats;
	}

	/**
	 * Method to move a row in the ordering sequence of a group of rows defined by an SQL WHERE clause.
	 * Negative numbers move the row up in the sequence and positive numbers move it down.
	 *
	 * @param   integer  $delta  The direction and magnitude to move the row in the ordering sequence.
	 * @param   string   $where  WHERE clause to use for limiting the selection of rows to compact the ordering values.
	 * @return  bool     True on success.
	 */
	public function move($delta, $where = '')
	{
		// If the change is none, do nothing.
		if (empty($delta))
		{
			return true;
		}

		// Select the primary key and ordering values from the table.
		$query = self::query()
			->where('parentid', '=', $this->parentid);

		// If the movement delta is negative move the row up.
		if ($delta < 0)
		{
			$query->where('ordering', '<', (int) $this->ordering);
			$query->orderBy('ordering', 'desc');
		}
		// If the movement delta is positive move the row down.
		elseif ($delta > 0)
		{
			$query->where('ordering', '>', (int) $this->ordering);
			$query->orderBy('ordering', 'asc');
		}

		// Add the custom WHERE clause if set.
		if ($where)
		{
			$query->where(DB::raw($where));
		}

		// Select the first row with the criteria.
		$row = $query->first();

		// If a row is found, move the item.
		if ($row)
		{
			$prev = $this->ordering;

			// Update the ordering field for this instance to the row's ordering value.
			if (!$this->update(['ordering' => (int) $row->ordering]))
			{
				return false;
			}

			// Update the ordering field for the row to this instance's ordering value.
			if (!$row->update(['ordering' => (int) $prev]))
			{
				return false;
			}
		}

		$all = self::query()
			->where('parentid', '=', $this->parentid)
			->orderBy('ordering', 'asc')
			->get();

		foreach ($all as $i => $row)
		{
			if ($row->ordering != ($i + 1))
			{
				$row->update(['ordering' => $i + 1]);
			}
		}

		return true;
	}

	/**
	 * Saves the manually set order of records.
	 *
	 * @param   array  $pks    An array of primary key ids.
	 * @param   array  $order  An array of order values.
	 * @return  bool
	 */
	public static function saveOrder($pks = null, $order = null)
	{
		if (empty($pks))
		{
			return false;
		}

		// Update ordering values
		foreach ($pks as $i => $pk)
		{
			$model = self::findOrFail((int) $pk);

			if ($model->ordering != $order[$i])
			{
				$model->ordering = $order[$i];

				if (!$model->save())
				{
					return false;
				}
			}
		}

		return true;
	}
}
