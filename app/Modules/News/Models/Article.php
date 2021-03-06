<?php

namespace App\Modules\News\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Config\Repository;
use Illuminate\Notifications\Notifiable;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use App\Halcyon\Traits\ErrorBag;
use App\Halcyon\Traits\Validatable;
use App\Halcyon\Utility\PorterStemmer;
use App\Modules\History\Traits\Historable;
use App\Modules\News\Events\ArticleCreating;
use App\Modules\News\Events\ArticleCreated;
use App\Modules\News\Events\ArticleUpdating;
use App\Modules\News\Events\ArticleUpdated;
use App\Modules\News\Events\ArticleDeleted;
use App\Modules\News\Events\ArticlePrepareContent;
use App\Modules\Resources\Models\Asset;
use Carbon\Carbon;

/**
 * News article
 */
class Article extends Model
{
	use ErrorBag, Validatable, Historable, Notifiable;

	/**
	 * The name of the "created at" column.
	 *
	 * @var  string
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
	 * @var  string
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
	 * @var  array
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
		'datetimemailed',
	];

	/**
	 * Fields and their validation criteria
	 *
	 * @var  array
	 */
	protected $rules = array(
		'newstypeid' => 'required|integer|in:0,1',
		'headline' => 'required|string|max:255',
		'body' => 'required|string|max:15000',
		'published' => 'nullable|integer|in:0,1',
		'template' => 'nullable|integer|in:0,1',
		'datetimenews' => 'required|date',
		'datetimenewsend' => 'nullable|date',
		'location' => 'nullable|string|max:32',
		'url' => 'nullable|url',
	);

	/**
	 * The event map for the model.
	 *
	 * @var  array
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
	 * Original end date
	 *
	 * @var object
	 */
	protected $originalend = false;

	/**
	 * Page metadata
	 *
	 * @var  object
	 */
	protected $metadataRepository = null;

	/**
	 * @var string
	 */
	protected $markdown = null;

	/**
	 * @var string
	 */
	protected $html = null;

	/**
	 * Route notifications for the Slack channel.
	 *
	 * @param  \Illuminate\Notifications\Notification  $notification
	 * @return string
	 */
	public function routeNotificationForSlack($notification)
	{
		return env('SLACK_NOTIFICATION_NEWS');
	}

	/**
	 * Set body value
	 *
	 * @param   string  $value
	 * @return  void
	 */
	public function setHeadlineAttribute(string $value)
	{
		$value = strip_tags($value);
		//$value = htmlentities($value, ENT_QUOTES, 'UTF-8');

		$this->attributes['headline'] = $value;
	}

	/**
	 * Set body value
	 *
	 * @param   string  $value
	 * @return  void
	 */
	public function setBodyAttribute(string $value)
	{
		$host = request()->getHttpHost();

		//$value = strip_tags($value);
		$value = preg_replace("/(https?:\/\/)?" . $host . "\/news\/\?id=(\d+)/", "NEWS#$3$4", $value);
		$value = preg_replace("/(https?:\/\/)?" . $host . "\/news\/\?id=(\d+)/", "NEWS#$3$4", $value);
		$value = preg_replace("/(https?:\/\/)?" . $host . "\/news\/(\d+)/", "NEWS#$3$4", $value);
		$value = preg_replace("/(https?:\/\/)?" . $host . "\/news\/(\d+)/", "NEWS#$3$4", $value);

		$this->attributes['body'] = $value;
	}

	/**
	 * Set body value
	 *
	 * @param   string  $value
	 * @return  void
	 */
	public function setLocationAttribute($value)
	{
		$value = strip_tags($value);
		$value = htmlentities($value, ENT_QUOTES, 'UTF-8');

		$this->attributes['location'] = $value;
	}

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
	 * Resource Assets list ordered by resource type & name
	 *
	 * @return  object
	 */
	public function resourceList()
	{
		$a = (new Asset)->getTable();
		$r = (new Newsresource)->getTable();

		return $this->resources()
			->select($r . '.*', $a . '.name')
			->join($a, $a . '.id', $r . '.resourceid')
			->orderBy($a . '.resourcetype', 'asc')
			->orderBy($a . '.name', 'asc');
		//return $this->hasManyThrough(Asset::class, Newsresource::class, 'newsid', 'id', 'id', 'resourceid');
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
		return $this->belongsTo('App\Modules\Users\Models\User', 'userid'); //->withDefault();
	}

	/**
	 * Defines a relationship to modifier
	 *
	 * @return  object
	 */
	public function modifier()
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'edituserid'); //->withDefault();
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
	 * Get the end time before any changes or updates
	 *
	 * @return  Carbon
	 */
	public function getVisitableUrlAttribute()
	{
		$userid = auth()->user() ? auth()->user()->id : 0;

		return route('site.news.visit', ['id' => $this->id, 'token' => urlencode(base64_encode($userid))]);
	}

	/**
	 * Mark a visit
	 *
	 * @param integer $userid
	 * @return void
	 */
	public function markVisit($userid)
	{
		$found = false;

		foreach ($this->associations as $i => $assoc)
		{
			if ($userid = $assoc->associd)
			{
				$assoc->visit();
				$found = true;
				break;
			}
		}

		if (!$found)
		{
			$r = new Association;
			$r->associd = $userid;
			$r->assoctype = 'user';
			$r->newsid = $this->id;
			$r->datetimevisited = Carbon::now();
			$r->comment = 'url_visit';
			$r->save();
		}
	}

	/**
	 * Get the end time before any changes or updates
	 *
	 * @return  Carbon
	 */
	public function getOriginalDatetimenewsendAttribute()
	{
		if (!$this->originalend)
		{
			$dt = $this->datetimenewsend;

			if ($this->hasEnd() && $this->isModified())
			{
				// Find the first update datetime
				$first = $this->updates()->orderBy('datetimecreated', 'asc')->first();

				if ($first)
				{
					$before = $this->history()
						->where('created_at', '<', $first->datetimecreated->toDateTimeString())
						->orderBy('created_at', 'desc')
						->get();

					foreach ($before as $item)
					{
						//if (isset($item->old->datetimenewsend)
						//&& isset($item->new->datetimenewsend)
						//&& $item->old->datetimenewsend != $item->new->datetimenewsend
						//&& $item->old->datetimenewsend != null)
						if (isset($item->new->datetimenewsend)
						&& $item->new->datetimenewsend != null)
						{
							if (strstr($item->new->datetimenewsend, 'T'))
							{
								$dt = Carbon::parse($item->new->datetimenewsend)->tz(config('app.timezone'));
							}
							else
							{
								$dt = Carbon::parse($item->new->datetimenewsend);
							}
							break;
						}
					}
				}
			}

			$this->originalend = $dt;
		}

		return $this->originalend;
	}

	/**
	 * Determine if entry was edited
	 *
	 * @return  bool
	 */
	public function isModified()
	{
		return !is_null($this->datetimeedited);
	}

	/**
	 * Determine if entry was updated
	 *
	 * @return  bool
	 */
	public function isUpdated()
	{
		return !is_null($this->datetimeupdate);
	}

	/**
	 * Determine if entry was edited
	 *
	 * @return  bool
	 */
	public function isMailed()
	{
		return !is_null($this->datetimemailed);
	}

	/**
	 * Determine if entry has a start time
	 *
	 * @return  bool
	 */
	public function hasStart()
	{
		return !is_null($this->datetimenews);
	}

	/**
	 * Determine if entry has an end time
	 *
	 * @return  bool
	 */
	public function hasEnd()
	{
		return !is_null($this->datetimenewsend);
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
	 * Check if the event is available
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
	 * Check if the event is happening today
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
	 * Check if the event is happening now
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
	 * Check if the event is tomorrow
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
	 * Check if the event is an outage
	 *
	 * @return  boolean
	 */
	public function isOutage()
	{
		if (stristr($this->headline, 'outage'))
		{
			return true;
		}

		if (stristr($this->headline, 'failure'))
		{
			return true;
		}

		if (stristr($this->headline, 'problem'))
		{
			return true;
		}

		return false;
	}

	/**
	 * Has the event started?
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
		 && $this->datetimenews > $now)
		{
			return false;
		}

		return true;
	}

	/**
	 * Has the event ended?
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

		if ($this->hasEnd()
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
	 * Generate a download link to item
	 *
	 * @return  string
	 */
	public function getDownloadCalendarLinkAttribute()
	{
		return route('site.news.calendar', ['name' => $this->id]);
	}

	/**
	 * Generate a subscribe link to item
	 *
	 * @return  string
	 */
	public function getSubscribeCalendarLinkAttribute()
	{
		return str_replace(['http:', 'https:'], 'webcal:', route('site.news.calendar', ['name' => $this->id]));
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
					->orWhere(function($w) use ($now)
					{
						$w->whereNotNull('datetimenewsend')
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
	 * Get a metadata Repository object
	 *
	 * @return  object
	 */
	public function getMetadataAttribute()
	{
		if (!($this->metadataRepository instanceof Repository))
		{
			$this->metadataRepository = new Repository();
		}

		return $this->metadataRepository;
	}

	/**
	 * Output body as MarkDown
	 *
	 * @return string
	 */
	public function toMarkdown()
	{
		if (is_null($this->markdown))
		{
			$body = $this->body;

			// Auto-expand relative URLs to absolute
			$body = preg_replace_callback('/\[.*?\]\(([^\)]+)\)/i', function($matches)
			{
				if (substr($matches[1], 0, 4) == 'http')
				{
					return $matches[0];
				}

				return str_replace($matches[1], asset(ltrim($matches[1], '/')), $matches[0]);
			}, $body);

			event($event = new ArticlePrepareContent($body));

			$text = $event->getBody();

			// Separate code blocks as we don't want to do any processing on their content
			$text = preg_replace_callback("/```(.*?)```/uis", [$this, 'stripPre'], $text);
			$text = preg_replace_callback("/`(.*?)`/i", [$this, 'stripCode'], $text);

			$news = array_merge($this->getContentVars(), $this->getAttributes());

			foreach ($news as $var => $value)
			{
				if (is_array($value))
				{
					$value = implode(', ', $value);
				}
				$text = preg_replace("/%" . $var . "%/", $value, $text);
			}

			$text = preg_replace_callback("/(news)\s*(story|item)?\s*#?(\d+)(\{.+?\})?/i", array($this, 'matchNews'), $text);

			$text = preg_replace_callback("/\{\{PRE\}\}/", [$this, 'replacePre'], $text);
			$text = preg_replace_callback("/\{\{CODE\}\}/", [$this, 'replaceCode'], $text);

			$this->markdown = $text;
		}

		return $this->markdown;
	}

	/**
	 * Output body as HTML
	 *
	 * @return string
	 */
	public function toHtml()
	{
		if (is_null($this->html))
		{
			$text = $this->toMarkdown();

			$converter = new CommonMarkConverter([
				'html_input' => 'allow',
			]);
			$converter->getEnvironment()->addExtension(new TableExtension());
			$converter->getEnvironment()->addExtension(new StrikethroughExtension());
			$converter->getEnvironment()->addExtension(new AutolinkExtension());

			$text = (string) $converter->convertToHtml($text);

			// Separate code blocks as we don't want to do any processing on their content
			$text = preg_replace_callback("/\<pre\>(.*?)\<\/pre\>/uis", [$this, 'stripPre'], $text);
			$text = preg_replace_callback("/\<code\>(.*?)\<\/code\>/i", [$this, 'stripCode'], $text);

			// Convert emails
			//$text = preg_replace('/([\w\.\-]+@((\w+\.)*\w{2,}\.\w{2,}))/', "<a target=\"_blank\" href=\"mailto:$1\">$1</a>", $text);

			// Convert template variables
			if (auth()->user() && auth()->user()->can('manage news'))
			{
				$text = preg_replace("/%%([\w\s]+)%%/", '<span style="color:red">$0</span>', $text);
			}

			// Highlight unused variables for admins
			if (auth()->user() && auth()->user()->can('manage news'))
			{
				$text = preg_replace("/%([\w\s]+)%/", '<span style="color:red">$0</span>', $text);
			}

			$text = str_replace('<th>', '<th scope="col">', $text);
			$text = str_replace('align="right"', 'class="text-right"', $text);

			// Put code blocks back
			$text = preg_replace_callback("/\{\{PRE\}\}/", [$this, 'replacePre'], $text);
			$text = preg_replace_callback("/\{\{CODE\}\}/", [$this, 'replaceCode'], $text);

			$text = preg_replace('/<p>([^\n]+)<\/p>\n(<table.*?>)(.*?<\/table>)/usm', '$2 <caption>$1</caption>$3', $text);
			$text = preg_replace('/src="\/include\/images\/(.*?)"/i', 'src="' . asset("files/$1") . '"', $text);

			$this->html = $text;
		}

		return $this->html;
	}

	/**
	 * Defines a relationship to type
	 *
	 * @return string
	 */
	public function getFormattedBodyAttribute()
	{
		return $this->toHtml();

		if (is_null($this->formatted_body))
		{
			$body = $this->body;

			// Auto-expand relative URLs to absolute
			$body = preg_replace_callback('/\[.*?\]\(([^\)]+)\)/i', function($matches)
			{
				if (substr($matches[1], 0, 4) == 'http')
				{
					return $matches[0];
				}

				return str_replace($matches[1], asset(ltrim($matches[1], '/')), $matches[0]);
			}, $body);

			event($event = new ArticlePrepareContent($body));

			$text = $event->getBody();

			$converter = new CommonMarkConverter([
				'html_input' => 'allow',
			]);
			$converter->getEnvironment()->addExtension(new TableExtension());
			$converter->getEnvironment()->addExtension(new StrikethroughExtension());

			$text = (string) $converter->convertToHtml($text);

			// Separate code blocks as we don't want to do any processing on their content
			$text = preg_replace_callback("/\<pre\>(.*?)\<\/pre\>/uis", [$this, 'stripPre'], $text);
			$text = preg_replace_callback("/\<code\>(.*?)\<\/code\>/i", [$this, 'stripCode'], $text);

			// Convert emails
			$text = preg_replace('/([\w\.\-]+@((\w+\.)*\w{2,}\.\w{2,}))/', "<a target=\"_blank\" href=\"mailto:$1\">$1</a>", $text);

			// Convert template variables
			if (auth()->user() && auth()->user()->can('manage news'))
			{
				$text = preg_replace("/%%([\w\s]+)%%/", '<span style="color:red">$0</span>', $text);
			}

			$news = array_merge($this->getContentVars(), $this->getAttributes());

			foreach ($news as $var => $value)
			{
				if (is_array($value))
				{
					$value = implode(', ', $value);
				}
				$text = preg_replace("/%" . $var . "%/", $value, $text);
			}

			// Highlight unused variables for admins
			if (auth()->user() && auth()->user()->can('manage news'))
			{
				$text = preg_replace("/%([\w\s]+)%/", '<span style="color:red">$0</span>', $text);
			}

			$text = preg_replace_callback("/(news)\s*(story|item)?\s*#?(\d+)(\{.+?\})?/i", array($this, 'matchNews'), $text);

			// Put code blocks back
			$text = preg_replace_callback("/\{\{PRE\}\}/", [$this, 'replacePre'], $text);
			$text = preg_replace_callback("/\{\{CODE\}\}/", [$this, 'replaceCode'], $text);
			$text = str_replace('<th>', '<th scope="col">', $text);

			$text = preg_replace('/<p>([^\n]+)<\/p>\n(<table.*?>)(.*?<\/table>)/usm', '$2 <caption>$1</caption>$3', $text);
			$text = preg_replace('/src="\/include\/images\/(.*?)"/i', 'src="' . asset("files/$1") . '"', $text);

			$this->formatted_body = $text;
		}

		return $this->formatted_body;
	}

	/**
	 * Expand NEWS#123 to linked article titles
	 * This resturns the linked title in MarkDown syntax
	 *
	 * @param   array  $match
	 * @return  string
	 */
	private function matchNews(array $match)
	{
		$title = trans('news::news.news story number', ['number' => $match[3]]);

		$news = self::find($match[3]);

		if (!$news)
		{
			return $match[0];
		}

		$title = $news->headline;

		if (isset($match[4]))
		{
			$title = preg_replace("/[{}]+/", '', $match[4]);
		}

		return '[' . $title . '](' . route('site.news.show', ['id' => $match[3]]) . ')';
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
	protected function stripCode(array $match)
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
	protected function stripPre(array $match)
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
	protected function replaceCode(array $match)
	{
		return array_shift($this->replacements['codeblocks']);
	}

	/**
	 * Replace pre block
	 *
	 * @param   array  $match
	 * @return  string
	 */
	protected function replacePre(array $match)
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
	protected function stripURL(array $match)
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
	 * @param   array    $options
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

		//if ($this->trashed())
		//{
			foreach ($this->resources as $resource)
			{
				$resource->delete($options);
			}

			foreach ($this->associations as $association)
			{
				$association->delete($options);
			}

			$row = Stemmedtext::find($this->id);
			$row->delete();
		//}

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
	public function formatDate($startdate, $enddate=null)
	{
		if (!$startdate || $startdate == '0000-00-00 00:00:00')
		{
			return '';
		}
		if (!$enddate)
		{
			$enddate = $startdate;
		}
		$datestring = '';

		$starttime = explode(' ', $startdate);
		$starttime = $starttime[1];

		$endtime = explode(' ', $enddate);
		$endtime = $endtime[1];

		$startdate = Carbon::parse($startdate);
		$enddate = Carbon::parse($enddate);

		$startyear  = $startdate->format('Y');
		$startmonth = $startdate->format('F');
		$startday   = $startdate->format('j');

		$endyear    = $enddate->format('Y');
		$endmonth   = $enddate->format('F');
		$endday     = $enddate->format('j');

		if ($enddate == '-0001-11-30 00:00:00'
		 || $enddate == '0000-00-00 00:00:00'
		 || $startdate == $enddate)
		{
			//$startdate = Carbon::parse($startdate);

			$datestring = $startdate->format('F j, Y');
			if ($starttime != '00:00:00')
			{
				$datestring .= ' ' . $startdate->format('g:ia') . ' ' . $startdate->format('T');
			}
		}
		else
		{
			//$startdate = Carbon::parse($startdate);
			//$enddate = Carbon::parse($enddate);

			if ($starttime == '00:00:00' && $endtime == '00:00:00')
			{
				$endtime   = '';
				$starttime = '';
			}
			else
			{
				$starttime = $startdate->format('g:ia');
				$endtime   = $enddate->format('g:ia') . ' ' . $enddate->format('T');
			}

			if ($startmonth == $endmonth && $startyear == $endyear && !$starttime && !$endtime)
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
					$starttime = ' ' . $starttime . (!$endtime ? ' ' . $startdate->format('T') : '');
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
	public function getContentVars()
	{
		$vars = array(
			'date'           => '%date%',
			'datetime'       => '%datetime%',
			'time'           => '%time%',
			'updatedatetime' => '%updatedatetime%',
			'updatedate'     => '%updatedate%',
			'updatetime'     => '%updatetime%',
			'startdatetime'  => '%startdatetime%',
			'startdate'      => '%startdate%',
			'starttime'      => '%starttime%',
			'enddatetime'    => '%enddatetime%',
			'enddate'        => '%enddate%',
			'endtime'        => '%endtime%',
		);

		if ($this->vars)
		{
			$vars = array_merge($vars, $this->vars);
			$this->vars = null;
		}

		if (count($this->updates))
		{
			$end = $this->originalDatetimenewsend;
		}
		else
		{
			$end = $this->datetimenewsend;
		}

		foreach ($vars as $var => $value)
		{
			if ($var == 'datetime' || $var == 'date')
			{
				if ($this->hasStart())
				{
					if (!$this->hasEnd() || $this->datetimenews->format('Y-m-d') == $end->format('Y-m-d'))
					{
						// single day
						$date = $this->datetimenews->format('l, F j, Y');
						$time = $this->datetimenews->format('g:ia');
						$tzon = $this->datetimenews->format('T');

						if ($this->hasEnd())
						{
							$time = 'from ' . $time . ' - ' . $end->format('g:ia');
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
							$vars[$var] = $date . ' ' . $time . ' ' . $tzon;
						}
					}
					else
					{
						if ($var == 'date')
						{
							$vars[$var] = preg_replace("/&nbsp;/", ' at ', $this->formatDate($this->datetimenews->format('Y-m-d') . ' 00:00:00', $end->format('Y-m-d') . ' 00:00:00'));
						}
						else
						{
							$vars[$var] = preg_replace("/&nbsp;/", ' at ', $this->formatDate($this->datetimenews, $end));
						}
					}
				}
			}

			if ($var == 'time')
			{
				if ($this->hasStart())
				{
					$vars[$var] = $this->datetimenews->format('g:ia');

					if ($this->hasEnd())
					{
						$vars[$var] .= ' &#8211; ' . $this->datetimenewsend->format('g:ia');
					}

					$vars[$var] .= ' ' . $this->datetimenews->format('T');
				}
			}

			if ($var == 'startdatetime' || $var == 'startdate' || $var == 'starttime')
			{
				if ($this->hasStart())
				{
					$date = $this->datetimenews->format('l, F jS, Y');
					$time = $this->datetimenews->format('g:ia T');

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
				if ($this->hasEnd())
				{
					$date = $end->format('l, F jS, Y');
					$time = $end->format('g:ia T');

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

			if ($var == 'updatedatetime' || $var == 'updatedate' || $var == 'updatetime')
			{
				if (!$this->isUpdated())
				{
					if ($this->datetimecreated)
					{
						$this->datetimeupdate = $this->datetimecreated;
					}
					else
					{
						$this->datetimeupdate = Carbon::now();
					}
				}

				$date = $this->datetimeupdate->format('l, F jS, Y');
				$time = $this->datetimeupdate->format('g:ia T');

				if ($var == 'updatetime')
				{
					$vars[$var] = $time;
				}
				else if ($time == '12:00am' || $var == 'updatedate')
				{
					$vars[$var] = $date;
				}
				else
				{
					$vars[$var] = $date . ' at ' . $time;
				}
			}
		}

		if (isset($this->location) && $this->location)
		{
			$vars['location'] = $this->location;
		}

		if (!isset($vars['resources']))
		{
			$vars['resources'] = array();
		}
		foreach ($this->resourceList()->get() as $resource)
		{
			$vars['resources'][] = $resource->name;
		}

		if (count($vars['resources']) > 1)
		{
			$vars['resources'][count($vars['resources'])-1] = 'and ' . $vars['resources'][count($vars['resources'])-1];
		}

		$vars['resources'] = array_filter($vars['resources']);
		$vars['resources'] = array_unique($vars['resources']);

		if (count($vars['resources']) == 2)
		{
			$vars['resources'] = $vars['resources'][0] . ' ' . $vars['resources'][1];
		}
		else
		{
			$vars['resources'] = implode(', ', $vars['resources']);
		}

		return $vars;
	}

	/**
	 * Set resources list
	 *
	 * @param   array  $resources
	 * @return  void
	 */
	public function setResources(array $resources = [])
	{
		if (empty($resources))
		{
			return;
		}

		$resources = (array)$resources;

		// Remove and add resource-news mappings
		// First calculate diff
		$addresources = array();
		$deleteresources = array();

		foreach ($this->resources as $r)
		{
			$found = false;

			foreach ($resources as $r2)
			{
				if ($r2 == $r->resourceid)
				{
					$found = true;
				}
			}

			if (!$found)
			{
				array_push($deleteresources, $r);
			}
		}

		foreach ($resources as $r)
		{
			$found = false;

			foreach ($this->resources as $r2)
			{
				if ($r2->resourceid == $r)
				{
					$found = true;
				}
			}

			if (!$found)
			{
				array_push($addresources, $r);
			}
		}

		foreach ($deleteresources as $r)
		{
			$r->delete();
		}

		foreach ($addresources as $resourceid)
		{
			$r = new Newsresource;
			$r->resourceid = $resourceid;
			$r->newsid = $this->id;
			$r->save();
		}
	}

	/**
	 * Set associations list
	 *
	 * @param   array  $associations
	 * @return  void
	 */
	public function setAssociations($associations = [])
	{
		if (empty($associations))
		{
			return;
		}

		$associations = (array)$associations;

		$addassoc = array();
		$delassoc = array();

		foreach ($this->associations as $r)
		{
			$found = false;

			foreach ($associations as $r2)
			{
				if (!is_array($r2))
				{
					$r2 = array(
						'associd' => $r2,
						'assoctype' => 'user'
					);
				}

				if ($r2['associd'] == $r->associd
				 && $r2['assoctype'] == $r->assoctype)
				{
					$found = true;
				}
			}

			if (!$found)
			{
				array_push($delassoc, $r);
			}
		}

		foreach ($associations as $r2)
		{
			$found = false;

			foreach ($this->associations as $r)
			{
				if (!is_array($r2))
				{
					$r2 = array(
						'associd' => $r2,
						'assoctype' => 'user'
					);
				}

				if ($r2['associd'] == $r->associd
				 && $r2['assoctype'] == $r->assoctype)
				{
					$found = true;
				}
			}

			if (!$found)
			{
				array_push($addassoc, $r2);
			}
		}

		foreach ($delassoc as $r)
		{
			$r->delete();
		}

		foreach ($addassoc as $assoc)
		{
			$r = new Association;
			$r->associd = $assoc['associd'];
			$r->assoctype = $assoc['assoctype'];
			$r->newsid = $this->id;
			$r->save();
		}
	}
}
