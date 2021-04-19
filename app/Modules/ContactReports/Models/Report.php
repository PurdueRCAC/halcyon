<?php

namespace App\Modules\ContactReports\Models;

use Illuminate\Database\Eloquent\Model;
use App\Halcyon\Traits\ErrorBag;
use App\Halcyon\Traits\Validatable;
use App\Modules\Tags\Traits\Taggable;
use App\Modules\History\Traits\Historable;
use App\Modules\ContactReports\Events\ReportPrepareContent;
use Carbon\Carbon;
use App\Halcyon\Utility\PorterStemmer;
use App\Modules\Users\Models\User as SystemUser;
use App\Halcyon\Access\Map;

/**
 * Contact report
 */
class Report extends Model
{
	use ErrorBag, Validatable, Historable, Taggable;

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
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'contactreports';

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'datetimecontact';

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
		'id',
		'datetimecreated',
		'datetimegroupid'
	];

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var  array
	 */
	protected $dates = [
		'datetimecreated',
		'datetimecontact',
		'datetimegroupid',
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
	 * Code block replacements
	 *
	 * @var  array
	 */
	private $replacements = array(
		'preblocks'  => array(),
		'codeblocks' => array()
	);

	/**
	 * Runs extra setup code when creating/updating a new model
	 *
	 * @return  void
	 */
	protected static function boot()
	{
		parent::boot();

		// Parse out hashtags and tag the record
		static::created(function ($model)
		{
			preg_match_all('/(^|[^a-z0-9_])#([a-z0-9\-_]+)/i', $model->report, $matches);

			if (!empty($matches[0]))
			{
				$tags = array();

				foreach ($matches[0] as $match)
				{
					$tags[] = preg_replace("/[^a-z0-9\-_]+/i", '', $match);
				}

				$model->setTags($tags);
			}
		});

		static::updated(function ($model)
		{
			preg_match_all('/(^|[^a-z0-9_])#([a-z0-9\-_]+)/i', $model->report, $matches);

			if (!empty($matches[0]))
			{
				$tags = array();

				foreach ($matches[0] as $match)
				{
					$tags[] = preg_replace("/[^a-z0-9\-_]+/i", '', $match);
				}

				$model->setTags($tags);
			}
		});
	}

	/**
	 * Defines a relationship to updates
	 *
	 * @return  object
	 */
	public function comments()
	{
		return $this->hasMany(Comment::class, 'contactreportid');
	}

	/**
	 * Defines a relationship to resources map
	 *
	 * @return  object
	 */
	public function resources()
	{
		return $this->hasMany(Reportresource::class, 'contactreportid');
	}

	/**
	 * Defines a relationship to group
	 *
	 * @return  object
	 */
	public function group()
	{
		return $this->belongsTo('App\Modules\Groups\Models\Group', 'groupid');
	}

	/**
	 * Defines a relationship to creator
	 *
	 * @return  object
	 */
	public function creator()
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'userid');
	}

	/**
	 * Defines a relationship to type
	 *
	 * @return  object
	 */
	public function users()
	{
		return $this->hasMany(__NAMESPACE__ . '\\User', 'contactreportid');
	}

	/**
	 * Defines a relationship to stemmedtext
	 *
	 * @return  object
	 */
	public function stemmedtext()
	{
		return $this->hasOne(Stem::class, 'contactreportid');
	}

	/**
	 * Defines a relationship to type
	 *
	 * @return  object
	 */
	public function type()
	{
		return $this->belongsTo(Type::class, 'contactreporttypeid');
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

	public function usersAsString()
	{
		$tags = array();
		foreach ($this->users as $u)
		{
			$tags[] = $u->user ? $u->user->name : '#' . $u->userid;
		}

		return implode(', ', $tags);
	}

	/**
	 * Defines a relationship to type
	 *
	 * @return string
	 */
	public function getFormattedReportAttribute()
	{
		$text = $this->report;

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
		if (auth()->user() && auth()->user()->can('manage contactreports'))
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
			if (is_array($value))
			{
				continue;
			}
			$text = preg_replace("/%" . $var . "%/", $value, $text);
		}

		if (auth()->user() && auth()->user()->can('manage contactreports'))
		{
			$text = preg_replace("/%([\w\s]+)%/", '<span style="color:red">$0</span>', $text);
		}

		$text = preg_replace_callback("/\{\{PRE\}\}/", [$this, 'replacePre'], $text);
		$text = preg_replace_callback("/\{\{CODE\}\}/", [$this, 'replaceCode'], $text);
		$text = str_replace('<th>', '<th scope="col">', $text);

		//$text = preg_replace_callback("/\{\{CODE\}\}/", 'replaceCode', $text);

		//$text = '<p>' . $text . '</p>';
		$text = preg_replace("/<p>(.*)(<table.*?>)(.*<\/table>)/m", "<p>$2 <caption>$1</caption>$3", $text);

		event($event = new ReportPrepareContent($text));
		$text = $event->getBody();

		return $text;
	}

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
	 * Delete the record and all associated data
	 *
	 * @return  boolean  False if error, True on success
	 */
	public function delete(array $options = [])
	{
		foreach ($this->comments as $comment)
		{
			if (!$comment->delete($options))
			{
				$this->addError($comment->getError());
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

		foreach ($this->users as $user)
		{
			if (!$user->delete($options))
			{
				$this->addError($user->getError());
				return false;
			}
		}

		if ($stemmedtext = $this->stemmedtext)
		{
			if (!$this->stemmedtext->delete($options))
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
	public function formatDate($startdate)
	{
		$datestring = '';

		if (!$startdate)
		{
			return $datestring;
		}

		if ($startdate && !is_string($startdate))
		{
			$startdate = $startdate->toDateTimeString();
		}
		$starttime = explode(' ', $startdate);
		$starttime = $starttime[1];

		$startyear  = date("Y", strtotime($startdate));
		$startmonth = date("F", strtotime($startdate));
		$startday   = date("j", strtotime($startdate));

		$datestring = date("F j, Y", strtotime($startdate));
		if ($starttime != '00:00:00')
		{
			$datestring .= ' ' . date("g:ia", strtotime($startdate));
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
			if ($this->datetimecreated)
			{
				if ($var == 'datetime' || $var == 'date')
				{
					if ($this->datetimecreated != '0000-00-00 00:00:00')
					{
						$vars[$var] = preg_replace("/&nbsp;/", ' at ', $this->formatDate($this->datetimecreated->format('Y-m-d') . ' 00:00:00'));
					}
				}

				if ($var == 'time')
				{
					if ($this->datetimecreated != '0000-00-00 00:00:00')
					{
						$vars[$var] = $this->datetimecreated->format('g:ia');
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

	/**
	 * Generate stemmed report
	 *
	 * @param   string  $value
	 * @return  string
	 */
	public function setReportAttribute($value) //generateStemmedReport()
	{
		$this->attributes['report'] = $value;

		$report_words = preg_replace('/[^A-Za-z0-9]/', ' ', $value);
		$report_words = preg_replace('/ +/', ' ', $report_words);
		$report_words = preg_replace_callback(
			'/(^|[^\w^@^\/^\.])(((http)(s)?(:\/\/))?(([\w\-\.]+)\.(com|edu|org|mil|gov|net|info|[a-zA-Z]{2})(\/([\w\/\?=\-\&~\.\#\$\+~%;\\,]*[A-Za-z0-9\/])?)?))(\{.+?\})?(?=[^\w^}]|$)/',
			[$this, 'stripURL'],
			$report_words
		);

		// Calculate stem for each word
		$stems = array();
		foreach (explode(' ', $report_words) as $word)
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

		$stemmedreport = '';
		foreach ($stems as $stem)
		{
			$stemmedreport .= $stem . ' ';
		}

		$this->attributes['stemmedreport'] = $stemmedreport;

		return $value;
	}

	/**
	 * Strip URL
	 *
	 * @param   array  $match
	 * @return  string
	 */
	private function stripURL($match)
	{
		if (isset($match[12]))
		{
			return $match[1] . ' ' . preg_replace("/\{|\}/", '', $match[12]);
		}

		return $match[1] . ' ' . $match[2];
	}

	/**
	 * Fetch list of people "subscribed" to a report's comments
	 * This includes anybody with a comment and the report author
	 *
	 * @return  array
	 */
	public function commentSubscribers()
	{
		$subscribers = array($this->userid);

		foreach ($this->comments as $comment)
		{
			$subscribers[] = $comment->userid;
		}

		// Also select all users tagged in the report who have CRM privs.
		$role_id = config('modules.contactreports.staff', 0);

		if ($role_id)
		{
			$a = (new SystemUser)->getTable();
			$b = (new Map)->getTable();

			$query = SystemUser::query()
				->select($a . '.*')
				->with('roles')
				->leftJoin($b, $b . '.user_id', $a . '.id')
				->where($b . '.role_id', '=', (int)$role_id);

			foreach ($query->get() as $user)
			{
				if ($user->can('manage contactreports'))
				{
					$subscribers[] = $user->id;
				}
			}
		}

		$subscribers = array_unique($subscribers);

		return $subscribers;
	}

	/**
	 * Fetch list of people "subscribed" to a report
	 * This includes report author, anyone tagged, and watchers
	 *
	 * @return  array
	 */
	public function subscribers()
	{
		$subscribers = array($this->userid);

		foreach ($this->users as $user)
		{
			$subscribers[] = $user->userid;

			foreach ($user->followers as $follower)
			{
				$subscribers[] = $follower->userid;
			}
		}

		// Also select all users tagged in the report who have CRM privs.
		$role_id = config('modules.contactreports.staff', 0);

		if ($role_id)
		{
			$a = (new SystemUser)->getTable();
			$b = (new Map)->getTable();

			$query = SystemUser::query()
				->select($a . '.*')
				->with('roles')
				->leftJoin($b, $b . '.user_id', $a . '.id')
				->where($b . '.role_id', '=', (int)$role_id);

			foreach ($query->get() as $user)
			{
				if ($user->can('manage contactreports'))
				{
					$subscribers[] = $user->id;
				}
			}
		}

		if ($this->groupid)
		{
			$gusers = $this->group->members()->where('membertype', '=', 10)->get();

			foreach ($gusers as $guser)
			{
				$subscribers[] = $guser->userid;
			}
		}

		$subscribers = array_unique($subscribers);

		return $subscribers;
	}

	/*public function getKeywordsAttribute()
	{
		$str = $this->report;

		$min_word_length = 3;
		$avoid = [
			'we','the','to','i','am','is','are','he','she','a','an','and','here','there','can',
			'they', 'them',
			'could','were','has','have','had','been','welcome','of','home','&nbsp;','&ldquo;',
			'words','into','this','there'
		];
		$strip_arr = ["," ,"." ,";" ,":", "\"", "'", "“","”","(",")", "!","?"];
		$str_clean = str_replace($strip_arr, '', $str);
		$str_arr = explode(' ', $str_clean);
		$clean_arr = [];

		foreach($str_arr as $word)
		{
			if (strlen($word) > $min_word_length)
			{
				$word = strtolower($word);
				if (!in_array($word, $avoid))
				{
					$clean_arr[] = $word;
				}
			}
		}

		return implode(',', $clean_arr);
	}*/

	static $entityNamespace = 'crm';

	public function getHashtagsAttribute()
	{
		$str = $this->report;

		/*$expression = "/(^|[^a-z0-9_])#([a-z0-9_]+)/";
		$str = preg_replace_callback($expression, function($matches)
		{
			$tag = \App\Modules\Tags\Models\Tag::findByTag($matches[1]);

			if (!$tag)
			{
				$tag = new \App\Modules\Tags\Models\Tag;
				$tag->tag = $matches[1];
				$tag->save();
			}

		}, $str);*/
		preg_match_all('/(^|[^a-z0-9_])#([a-z0-9\-_]+)/i', $str, $matches);

		$hashtag = [];
		if (!empty($matches[0]))
		{
			foreach ($matches[0] as $match)
			{
				$match = preg_replace("/[^a-z0-9\-_]+/i", '', $match);
				$this->addTag($match);

				$hashtag[] = $match;
			}
		}

		return implode(', ', $hashtag);
	}
}
