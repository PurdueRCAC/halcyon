<?php

namespace App\Modules\Issues\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use App\Modules\History\Traits\Historable;
use App\Halcyon\Utility\PorterStemmer;
use App\Modules\Issues\Events\IssuePrepareContent;
use App\Modules\Users\Models\User;
use App\Modules\Tags\Traits\Taggable;

/**
 * Issue model
 */
class Issue extends Model
{
	use Historable, SoftDeletes, Taggable;

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
	protected $table = 'issues';

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'datetimecreated';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public static $orderDir = 'desc';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int,string>
	 */
	protected $guarded = [
		'id',
		'datetimecreated',
		'datetimeremoved'
	];

	/**
	 * Fields and their validation criteria
	 *
	 * @var array<string,string>
	 */
	protected $rules = array(
		'userid' => 'required|integer',
		'report' => 'required|string'
	);

	/**
	 * Code block replacements
	 *
	 * @var  array<string,array>
	 */
	private $replacements = array(
		'preblocks'  => array(),
		'codeblocks' => array()
	);

	/**
	 * Tag namespace
	 *
	 * @var  string
	 */
	static $entityNamespace = 'issues';

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
	 * Defines a relationship to comments
	 *
	 * @return  object
	 */
	public function comments()
	{
		return $this->hasMany(Comment::class, 'issueid');
	}

	/**
	 * Defines a relationship to resources map
	 *
	 * @return  object
	 */
	public function resources()
	{
		return $this->hasMany(Issueresource::class, 'issueid');
	}

	/**
	 * Defines a relationship to creator
	 *
	 * @return  object
	 */
	public function creator()
	{
		return $this->belongsTo(User::class, 'userid');
	}

	/**
	 * Get formatted report
	 *
	 * @return string
	 */
	public function getFormattedReportAttribute()
	{
		$text = $this->report;

		$converter = new CommonMarkConverter([
			'html_input' => 'allow',
		]);
		$converter->getEnvironment()->addExtension(new TableExtension());
		$converter->getEnvironment()->addExtension(new StrikethroughExtension());
		$converter->getEnvironment()->addExtension(new AutolinkExtension());

		$text = (string) $converter->convertToHtml($text);

		// separate code blocks
		$text = preg_replace_callback("/\<pre\>(.*?)\<\/pre\>/i", [$this, 'stripPre'], $text);
		$text = preg_replace_callback("/\<code\>(.*?)\<\/code\>/i", [$this, 'stripCode'], $text);

		// convert emails
		$text = preg_replace('/([\w\.\-]+@((\w+\.)*\w{2,}\.\w{2,}))/', "<a target=\"_blank\" href=\"mailto:$1\">$1</a>", $text);

		// convert template variables
		if (auth()->user() && auth()->user()->can('manage issues'))
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

		if (auth()->user() && auth()->user()->can('manage issues'))
		{
			$text = preg_replace("/%([\w\s]+)%/", '<span style="color:red">$0</span>', $text);
		}

		$text = str_replace('<th>', '<th scope="col">', $text);
		$text = str_replace('align="right"', 'class="text-right"', $text);

		$text = preg_replace_callback("/\{\{PRE\}\}/", [$this, 'replacePre'], $text);
		$text = preg_replace_callback("/\{\{CODE\}\}/", [$this, 'replaceCode'], $text);

		$text = preg_replace("/<p>(.*)(<table.*?>)(.*<\/table>)/m", "<p>$2 <caption>$1</caption>$3", $text);

		event($event = new IssuePrepareContent($text));
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
	 * @return  bool  False if error, True on success
	 */
	public function delete()
	{
		foreach ($this->comments as $comment)
		{
			$comment->delete();
		}

		foreach ($this->resources as $resource)
		{
			$resource->delete();
		}

		// Attempt to delete the record
		return parent::delete();
	}

	/**
	 * Format date
	 *
	 * @param   string  $startdate
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

		$datestring = date("F j, Y", strtotime($startdate));
		if ($starttime != '00:00:00')
		{
			$datestring .= ' ' . date("g:ia", strtotime($startdate));
		}

		return $datestring;
	}

	/**
	 * Get content vars
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
				if ($this->datetimecreated)
				{
					$vars[$var] = preg_replace("/&nbsp;/", ' at ', $this->formatDate($this->datetimecreated->format('Y-m-d') . ' 00:00:00'));
				}
			}

			if ($var == 'time')
			{
				if ($this->datetimecreated)
				{
					$vars[$var] = $this->datetimecreated->format('g:ia');
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
	 * Get a list of resources as comma-separated string
	 *
	 * @return string
	 */
	public function getResourcesStringAttribute()
	{
		$names = array();

		foreach ($this->resources as $res)
		{
			if ($res->resource)
			{
				$names[] = $res->resource->name;
			}
			else
			{
				$names[] = $res->resourceid;
			}
		}

		asort($names);

		return implode(', ', $names);
	}
}
