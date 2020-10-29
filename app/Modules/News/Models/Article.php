<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Modules\News\Models;

use Illuminate\Database\Eloquent\Model;
use App\Halcyon\Traits\ErrorBag;
use App\Halcyon\Traits\Validatable;
use App\Halcyon\Utility\PorterStemmer;
use App\Modules\History\Traits\Historable;
use Carbon\Carbon;
use App\Modules\News\Events\ArticleCreating;
use App\Modules\News\Events\ArticleCreated;
use App\Modules\News\Events\ArticleUpdating;
use App\Modules\News\Events\ArticleUpdated;
use App\Modules\News\Events\ArticleDeleted;
use App\Modules\News\Events\ArticlePrepareContent;

/**
 * NEws article
 */
class Article extends Model
{
	use ErrorBag, Validatable, Historable;

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
	const UPDATED_AT = 'datetimeedited';

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'news';

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'id';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public static $orderDir = 'desc';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [
		'id'
	];

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var  array
	 */
	protected $dates = [
		'datetimenews',
		'datetimenewsend',
		'datetimeupdate',
		'datetimecreated',
		'datetimeedited',
		'datetimeemailed',
	];

	/**
	 * Fields and their validation criteria
	 *
	 * @var array
	 */
	protected $rules = array(
		'headline' => 'required',
		'body'     => 'required'
	);

	/**
	 * The event map for the model.
	 *
	 * @var array
	 */
	protected $dispatchesEvents = [
		'creating' => ArticleCreating::class,
		'created'  => ArticleCreated::class,
		'updating' => ArticleUpdating::class,
		'updated'  => ArticleUpdated::class,
		'deleted'  => ArticleDeleted::class,
		//'restored' => PageRestored::class,
	];

	/**
	 * Defines a relationship to updates
	 *
	 * @return  object
	 */
	public function updates()
	{
		return $this->hasMany(Update::class, 'newsid');
	}

	/**
	 * Defines a relationship to resources map
	 *
	 * @return  object
	 */
	public function resources()
	{
		return $this->hasMany(Newsresource::class, 'newsid');
	}

	/**
	 * Defines a relationship to resources map
	 *
	 * @return  object
	 */
	public function associations()
	{
		return $this->hasMany(Association::class, 'newsid');
	}

	/**
	 * Defines a relationship to creator
	 *
	 * @return  object
	 */
	public function creator()
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'userid')->withDefault();
	}

	/**
	 * Defines a relationship to modifier
	 *
	 * @return  object
	 */
	public function modifier()
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'edituserid')->withDefault();
	}

	/**
	 * Defines a relationship to modifier
	 *
	 * @return  object
	 */
	public function mailer()
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'lastmailuserid');
	}

	/**
	 * Defines a relationship to type
	 *
	 * @return  object
	 */
	public function type()
	{
		return $this->belongsTo(Type::class, 'newstypeid')->withDefault();
	}

	/**
	 * Defines a relationship to stemmedtext
	 *
	 * @return  object
	 */
	public function stemmedtext()
	{
		return $this->hasOne(Stemmedtext::Class, 'id');
	}

	/**
	 * Is the entry published?
	 *
	 * @return  boolean
	 */
	public function isPublished()
	{
		return ($this->published == 1);
	}

	/**
	 * Determine if entry was edited
	 *
	 * @return  bool
	 */
	public function isModified()
	{
		return ($this->datetimeedited && $this->datetimeedited != '0000-00-00 00:00:00' && $this->datetimeedited != '-0001-11-30 00:00:00');
	}

	/**
	 * Determine if entry was edited
	 *
	 * @return  bool
	 */
	public function isMailed()
	{
		return ($this->datetimemailed && $this->datetimemailed != '0000-00-00 00:00:00' && $this->datetimemailed != '-0001-11-30 00:00:00');
	}

	/**
	 * Determine if entry has a start time
	 *
	 * @return  bool
	 */
	public function hasStart()
	{
		return ($this->datetimenews && $this->datetimenews != '0000-00-00 00:00:00' && $this->datetimenews != '-0001-11-30 00:00:00');
	}

	/**
	 * Determine if entry has an end time
	 *
	 * @return  bool
	 */
	public function hasEnd()
	{
		return ($this->datetimenewsend && $this->datetimenewsend != '0000-00-00 00:00:00' && $this->datetimenewsend != '-0001-11-30 00:00:00');
	}

	/**
	 * Determine if entry has an end time
	 *
	 * @return  bool
	 */
	public function isSameDay()
	{
		return $this->hasStart() && $this->hasEnd() && ($this->datetimenewsend->format('Y-m-d') == $this->datetimenews->format('Y-m-d'));
	}

	/**
	 * Check if the job is available
	 *
	 * @return  boolean
	 */
	public function isAvailable()
	{
		// If it doesn't exist or isn't published
		if (!$this->id || !$this->isPublished())
		{
			return false;
		}

		// Make sure the item is published and within the available time range
		if ($this->started() && !$this->ended())
		{
			return true;
		}

		return false;
	}

	/**
	 * Check if the job is available
	 *
	 * @return  boolean
	 */
	public function isToday()
	{
		$now = Carbon::now()->format('Y-m-d');
		$start = Carbon::parse($this->datetimenews)->format('Y-m-d');

		return ($now == $start);
	}

	/**
	 * Check if the job is available
	 *
	 * @return  boolean
	 */
	public function isNow()
	{
		if (!$this->isToday())
		{
			return false;
		}

		$now = Carbon::now()->format('Y-m-d h:i:s');

		if ($this->hasEnd()
		 && $now > $this->datetimenews
		 && $now < $this->datetimenewsend)
		{
			return true;
		}

		return false;
	}

	/**
	 * Check if the job is available
	 *
	 * @return  boolean
	 */
	public function isTomorrow()
	{
		$now = Carbon::now()->modify('+1 day')->format('Y-m-d');
		$start = Carbon::parse($this->datetimenews)->format('Y-m-d');

		return ($now == $start);
	}

	/**
	 * Has the job started?
	 *
	 * @return  boolean
	 */
	public function started()
	{
		if (!$this->id || !$this->isPublished())
		{
			return false;
		}

		$now = Carbon::now()->toDateTimeString();

		if ($this->datetimenews
		 && $this->datetimenews != '0000-00-00 00:00:00'
		 && $this->datetimenews > $now)
		{
			return false;
		}

		return true;
	}

	/**
	 * Has the job ended?
	 *
	 * @return  boolean
	 */
	public function ended()
	{
		if (!$this->id || !$this->isPublished())
		{
			return true;
		}

		$now = Carbon::now()->toDateTimeString();

		if ($this->datetimenewsend
		 && $this->datetimenewsend != '0000-00-00 00:00:00'
		 && $this->datetimenewsend <= $now)
		{
			return true;
		}

		return false;
	}

	/**
	 * Generate a link to item
	 *
	 * @return  string
	 */
	public function link()
	{
		if (app('isAdmin'))
		{
			return route('admin.news.edit', ['id' => $this->id]);
		}

		return route('site.news.show', ['id' => $this->id]);
	}

	/**
	 * Set a query's WHERE clause to include published state
	 *
	 * @param   object  $query
	 * @return  object
	 */
	public function scopeWherePublished($query)
	{
		return $query->where('published', '=', 1);
		/*$now = Carbon::now()->toDateTimeString();

		return $query->where('published', '=', 1)
			->where(function($where) use ($now)
			{
				$where->whereNull('datetimenewsend')
					->orWhere('datetimenewsend', '=', '0000-00-00 00:00:00')
					->orWhere(function($w) use ($now)
					{
						$w->where('datetimenewsend', '!=', '0000-00-00 00:00:00')
							->where('datetimenewsend', '>', $now);
					});
			});*/
	}

	/**
	 * Set a query's WHERE clause to include published state
	 *
	 * @param   object  $query
	 * @param   array   $ids
	 * @return  object
	 */
	public function scopeWhereResourceIn($query, $ids)
	{
		$n = $this->getTable();
		$r = (new Newsresource)->getTable();

		return $query->join($r, $r . '.newsid', $n . '.id')
			->whereIn($r . '.resourceid', $ids);
	}

	/**
	 * Defines a relationship to type
	 *
	 * @return string
	 */
	public function getFormattedBodyAttribute()
	{
		$body = $this->body;

		event($event = new ArticlePrepareContent($body));

		$text = $event->getBody();

		if (class_exists('Parsedown'))
		{
			$mdParser = new \Parsedown();

			$text = $mdParser->text(trim($text));
		}

		// separate code blocks
		$text = preg_replace_callback("/\<pre\>(.*?)\<\/pre\>/i", [$this, 'stripPre'], $text);
		$text = preg_replace_callback("/\<code\>(.*?)\<\/code\>/i", [$this, 'stripCode'], $text);

		// convert emails
		$text = preg_replace('/([\w\.\-]+@((\w+\.)*\w{2,}\.\w{2,}))/', "<a target=\"_blank\" href=\"mailto:$1\">$1</a>", $text);

		// convert template variables
		if (auth()->user() && auth()->user()->can('manage news'))
		{
			$text = preg_replace("/%%([\w\s]+)%%/", '<span style="color:red">$0</span>', $text);
		}

		$uvars = array(
			'updatedatetime' => $this->datetimecreated,
			'updatedate'     => date('l, F jS, Y', strtotime($this->datetimecreated)),
			'updatetime'     => date("g:ia", strtotime($this->datetimecreated))
		);

		$news = array_merge($this->getContentVars(), $this->getAttributes()); //$this->toArray();
		$news['resources'] = $this->resources->toArray();

		$resources = array();
		foreach ($news['resources'] as $resource)
		{
			$resource['resourcename'] = $resource['resourceid'];
			array_push($resources, $resource['resourcename']);
		}

		if (count($resources) > 1)
		{
			$resources[count($resources)-1] = 'and ' . $resources[count($resources)-1];
		}

		if (count($resources) > 2)
		{
			$news['resources'] = implode(', ', $resources);
		}
		else if (count($resources) == 2)
		{
			$news['resources'] = $resources[0] . ' ' . $resources[1];
		}
		else if (count($resources) == 1)
		{
			$news['resources'] = $resources[0];
		}
		else
		{
			$news['resources'] = implode('', $resources);
		}

		foreach ($news as $var => $value)
		{
			$text = preg_replace("/%" . $var . "%/", $value, $text);
		}

		if (auth()->user() && auth()->user()->can('manage news'))
		{
			$text = preg_replace("/%([\w\s]+)%/", '<span style="color:red">$0</span>', $text);
		}

		// Put code blocks back
		$text = preg_replace_callback("/\{\{PRE\}\}/", [$this, 'replacePre'], $text);
		$text = preg_replace_callback("/\{\{CODE\}\}/", [$this, 'replaceCode'], $text);

		$text = preg_replace('/<p>([^\n]+)<\/p>\n(<table.*?>)(.*<\/table>)/usm', '$2 <caption>$1</caption>$3', $text);
		$text = preg_replace('/src="\/include\/images\/(.*?)"/i', 'src="' . asset("files/$1") . '"', $text);

		return $text;
	}

	/**
	 * Replace code block
	 *
	 * @param   array  $match
	 * @return  string
	 */
	private $replacements = array(
		'preblocks' => array(),
		'codeblocks' => array()
	);

	/**
	 * Strip code blocks
	 *
	 * @param   array  $match
	 * @return  string
	 */
	protected function stripCode($match)
	{
		array_push($this->replacements['codeblocks'], $match[0]);

		return '{{CODE}}';
	}

	/**
	 * Strip pre blocks
	 *
	 * @param   array  $match
	 * @return  string
	 */
	protected function stripPre($match)
	{
		array_push($this->replacements['preblocks'], $match[0]);

		return '{{PRE}}';
	}

	/**
	 * Replace code block
	 *
	 * @param   array  $match
	 * @return  string
	 */
	protected function replaceCode($match)
	{
		return array_shift($this->replacements['codeblocks']);
	}

	/**
	 * Replace pre block
	 *
	 * @param   array  $match
	 * @return  string
	 */
	protected function replacePre($match)
	{
		return array_shift($this->replacements['preblocks']);
	}

	/**
	 * The "booted" method of the model.
	 *
	 * @return void
	 */
	protected static function booted()
	{
		static::created(function ($article)
		{
			$row = new Stemmedtext;
			$row->id = $article->id;
			$row->stemmedtext = $article->stemText();
			$row->save();
		});

		static::updated(function ($article)
		{
			$row = Stemmedtext::find($article->id);
			if (!$row)
			{
				$row = new Stemmedtext;
			}
			$row->id = $article->id;
			$row->stemmedtext = $article->stemText();
			$row->save();
		});
	}

	/**
	 * Stem text
	 *
	 * @return  string
	 */
	public function stemText()
	{
		// Trim extra garbage and concatenate headline for searching
		$news_text = preg_replace_callback('/(^|[^\w^@^\/^\.])(((http)(s)?(:\/\/))?(([\w\-\.]+)\.(com|edu|org|mil|gov|net|info|[a-zA-Z]{2})(\/([\w\/\?=\-\&~\.\#\$\+~%;\\,]*[A-Za-z0-9\/])?)?))(\{.+?\})?(?=[^\w^}]|$)/', [$this, 'stripURL'], $this->body);
		$news_words = preg_replace('/[^A-Za-z0-9]/', ' ', $this->headline . " " . $news_text);
		$news_words = preg_replace('/ +/', ' ', $news_words);

		// Calculate stem for each word
		$stems = array();

		foreach (explode(' ', $news_words) as $word)
		{
			$stem = PorterStemmer::Stem($word);
			$stem = substr($stem, 0, 1) . $stem;

			array_push($stems, $stem);

			// If word ends in a number, also store it without the number
			if (preg_match('/[A-Za-z]+[0-9]+/', $word))
			{
				$word = preg_replace('/[^A-Za-z]/', '', $word);

				$stem = PorterStemmer::Stem($word);
				$stem = substr($stem, 0, 1) . $stem;

				array_push($stems, $stem);
			}
		}

		$stemmedtext = '';
		foreach ($stems as $stem)
		{
			$stemmedtext .= $stem . ' ';
		}

		return $stemmedtext;
	}

	/**
	 * Strip URL
	 *
	 * @param   array  $match
	 * @return  string
	 */
	protected function stripURL($match)
	{
		if (isset($match[12]))
		{
			return $match[1] . ' ' . preg_replace("/\{|\}/", '', $match[12]);
		}

		return $match[1] . ' ' . $match[2];
	}

	/**
	 * Delete the record and all associated data
	 *
	 * @return  boolean  False if error, True on success
	 */
	public function delete(array $options = [])
	{
		foreach ($this->updates as $update)
		{
			if (!$update->delete($options))
			{
				$this->addError($update->getError());
				return false;
			}
		}

		foreach ($this->resources as $resource)
		{
			if (!$resource->delete($options))
			{
				$this->addError($resource->getError());
				return false;
			}
		}

		if ($stemmedtext = $this->stemmedtext)
		{
			if (!$stemmedtext->delete($options))
			{
				$this->addError($stemmedtext->getError());
				return false;
			}
		}

		// Attempt to delete the record
		return parent::delete($options);
	}

	/**
	 * Format news date
	 *
	 * @param   string  $startdate
	 * @param   string  $enddate
	 * @return  string
	 */
	public function formatDate($startdate, $enddate='0000-00-00 00:00:00')
	{
		if (!$startdate || $startdate == '0000-00-00 00:00:00')
		{
			return '';
		}
		$datestring = '';

		$starttime = explode(' ', $startdate);
		$starttime = $starttime[1];

		$endtime = explode(' ', $enddate);
		$endtime = $endtime[1];

		$startyear  = date("Y", strtotime($startdate));
		$startmonth = date("F", strtotime($startdate));
		$startday   = date("j", strtotime($startdate));

		$endyear    = date("Y", strtotime($enddate));
		$endmonth   = date("F", strtotime($enddate));
		$endday     = date("j", strtotime($enddate));

		if ($enddate == '-0001-11-30 00:00:00' || $enddate == '0000-00-00 00:00:00' || $startdate == $enddate)
		{
			$datestring = date("F j, Y", strtotime($startdate));
			if ($starttime != '00:00:00')
			{
				$datestring .= ' ' . date("g:ia", strtotime($startdate));
			}
		}
		else
		{
			if ($starttime == '00:00:00' && $endtime == '00:00:00')
			{
				$endtime   = '';
				$starttime = '';
			}
			else
			{
				$starttime = date("g:ia", strtotime($startdate));
				$endtime   = date("g:ia", strtotime($enddate));
			}

			if ($startmonth == $endmonth && $startyear == $endyear && $starttime == '' && $endtime == '')
			{
				$datestring = $startmonth . ' ' . $startday . ' - ' . $endday . ', ' . $endyear;
			}
			elseif ($startmonth == $endmonth && $startyear == $endyear && $startday == $endday && $starttime != $endtime)
			{
				$datestring = $startmonth . ' ' . $startday . ', ' . $startyear . ' ' . $starttime . ' - ' . $endtime;
			}
			else
			{
				if ($starttime != '')
				{
					$starttime = ' ' . $starttime;
				}
				if ($endtime != '')
				{
					$endtime = ' ' . $endtime;
				}
				$datestring  = $startmonth . ' ' . $startday . ', ' . $startyear . ' ' . $starttime . ' - ';
				$datestring .= $endmonth . ' ' . $endday . ', ' . $endyear . ' ' . $endtime;
			}
		}

		return $datestring;
	}

	/**
	 * Get news vars
	 *
	 * @return  array
	 */
	protected function getContentVars()
	{
		$vars = array(
			'date'           => "%date%",
			'datetime'       => "%datetime%",
			'time'           => "%time%",
			'updatedatetime' => "%updatedatetime%",
			'startdatetime'  => "%startdatetime%",
			'startdate'      => "%startdate%",
			'starttime'      => "%starttime%",
			'enddatetime'    => "%enddatetime%",
			'enddate'        => "%enddate%",
			'endtime'        => "%endtime%",
		);

		foreach ($vars as $var => $value)
		{
			if ($var == 'datetime' || $var == 'date')
			{
				if ($this->getOriginal('datetimenews')
				 && $this->getOriginal('datetimenews') != '0000-00-00 00:00:00')
				{
					if ($this->datetimenews->format('Y-m-d') == $this->datetimenewsend->format('Y-m-d')
					 || $this->getOriginal('datetimenewsend') == '0000-00-00 00:00:00')
					{
						// single day
						$date = $this->datetimenews->format('l, F j, Y');
						$time = $this->datetimenews->format('g:ia');

						if ($this->getOriginal('datetimenewsend') != '0000-00-00 00:00:00')
						{
							$time = 'from ' . $time . ' - ' . $this->datetimenewsend->format('g:ia');
						}
						else
						{
							$time = 'at ' . $time;
						}

						if ($time == '12:00am' || $var == 'date')
						{
							$vars[$var] = $date;
						}
						else
						{
							$vars[$var] = $date . ' ' . $time;
						}
					}
					else
					{
						if ($var == 'date')
						{
							$vars[$var] = preg_replace("/&nbsp;/", ' at ', $this->formatDate($this->datetimenews->format('Y-m-d') . ' 00:00:00', $this->datetimenewsend->format('Y-m-d') . ' 00:00:00'));
						}
						else
						{
							$vars[$var] = preg_replace("/&nbsp;/", ' at ', $this->formatDate($this->getOriginal('datetimenews'), $this->getOriginal('datetimenewsend')));
						}
					}
				}
			}

			if ($var == 'time')
			{
				if ($this->getOriginal('datetimenews')
				 && $this->getOriginal('datetimenews') != '0000-00-00 00:00:00')
				{
					if ($this->getOriginal('datetimenewsend') == '0000-00-00 00:00:00')
					{
						$vars[$var] = $this->datetimenews->format('g:ia');
					}
					else
					{
						$vars[$var] = $this->datetimenews->format('g:ia') . ' &#8211; ' . $this->datetimenewsend->format('g:ia');
					}
				}
			}

			if ($var == 'startdatetime' || $var == 'startdate' || $var == 'starttime')
			{
				if ($this->getOriginal('datetimenews')
				 && $this->getOriginal('datetimenews') != '0000-00-00 00:00:00')
				{
					$date = $this->datetimenews->format('l, F jS, Y');
					$time = $this->datetimenews->format('g:ia');

					if ($var == 'starttime')
					{
						$vars[$var] = $time;
					}
					else if ($time == '12:00am' || $var == 'startdate')
					{
						$vars[$var] = $date;
					}
					else
					{
						$vars[$var] = $date . ' at ' . $time;
					}
				}
			}

			if ($var == 'enddatetime' || $var == 'enddate' || $var == 'endtime')
			{
				if ($this->getOriginal('datetimenewsend')
				 && $this->getOriginal('datetimenewsend') != '0000-00-00 00:00:00')
				{
					$date = $this->datetimenewsend->format('l, F jS, Y');
					$time = $this->datetimenewsend->format('g:ia');

					if ($var == 'endtime')
					{
						$vars[$var] = $time;
					}
					else if ($time == '12:00am' || $var == 'enddate')
					{
						$vars[$var] = $date;
					}
					else
					{
						$vars[$var] = $date . ' at ' . $time;
					}
				}
			}

			if ($var == 'updatedatetime')
			{
				if ($this->getOriginal('datetimeupdate')
				 && $this->getOriginal('datetimeupdate') != '0000-00-00 00:00:00')
				{
					$vars[$var] = $this->formatDate($this->getOriginal('datetimeupdate'));
				}
				else
				{
					if ($this->getOriginal('datetimecreated')
					 && $this->getOriginal('datetimecreated') != '0000-00-00 00:00:00')
					{
						$vars[$var] = $this->formatDate($this->getOriginal('datetimecreated'));
					}
					else
					{
						$vars[$var] = $this->formatDate(date("Y-m-d H:i:s"));
					}
				}
			}
		}

		if (isset($this->location) && $this->location != '')
		{
			$vars['location'] = $this->location;
		}

		if (isset($this->resources))
		{
			$resources = array();
			foreach ($this->resources as $resource)
			{
				array_push($resources, $resource['resourcename']);
			}

			if (count($resources) > 1)
			{
				$resources[count($resources)-1] = 'and ' . $resources[count($resources)-1];
			}

			if (count($resources) > 2)
			{
				$vars['resources'] = implode(', ', $resources);
			}
			else if (count($resources) == 2)
			{
				$vars['resources'] = $resources[0] . ' ' . $resources[1];
			}
			else if (count($resources) == 1)
			{
				$vars['resources'] = $resources[0];
			}
		}

		return $vars;
	}
}
