<?php

namespace App\Modules\Issues\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Pipeline\Pipeline;
use App\Modules\History\Traits\Historable;
use App\Halcyon\Utility\PorterStemmer;
use App\Modules\Issues\Formatters\AbsoluteUrls;
use App\Modules\Issues\Formatters\FixHtml;
use App\Modules\Issues\Formatters\MarkdownToHtml;
use App\Modules\Issues\Formatters\ReplaceVariables;
use App\Modules\Issues\Events\IssuePrepareContent;
use App\Modules\Users\Models\User;
use App\Modules\Tags\Traits\Taggable;

/**
 * Issue model
 *
 * @property int    $id
 * @property int    $issueid
 * @property int    $userid
 * @property string $comment
 * @property string $stemmedcomment
 * @property Carbon|null $datetimecreated
 * @property Carbon|null $datetimeremoved
 * @property int    $resolution
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
	 * @var string
	 */
	protected $markdown = null;

	/**
	 * @var string
	 */
	protected $html = null;

	/**
	 * Runs extra setup code when creating/updating a new model
	 *
	 * @return  void
	 */
	protected static function boot(): void
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
	 * @return  HasMany
	 */
	public function comments(): HasMany
	{
		return $this->hasMany(Comment::class, 'issueid');
	}

	/**
	 * Defines a relationship to resources map
	 *
	 * @return  HasMany
	 */
	public function resources(): HasMany
	{
		return $this->hasMany(Issueresource::class, 'issueid');
	}

	/**
	 * Defines a relationship to creator
	 *
	 * @return  BelongsTo
	 */
	public function creator(): BelongsTo
	{
		return $this->belongsTo(User::class, 'userid');
	}

	/**
	 * Output body as MarkDown
	 *
	 * @return string
	 */
	public function toMarkdown(): string
	{
		if (is_null($this->markdown))
		{
			$text = $this->report;

			event($event = new IssuePrepareContent($text));
			$text = $event->getBody();

			$text = preg_replace_callback("/```(.*?)```/i", [$this, 'stripPre'], $text);
			$text = preg_replace_callback("/`(.*?)`/i", [$this, 'stripCode'], $text);

			$data = app(Pipeline::class)
					->send([
						'id' => $this->id,
						'content' => $text,
						'variables' => $this->getContentVars(),
					])
					->through([
						AbsoluteUrls::class,
						ReplaceVariables::class,
					])
					->thenReturn();

			$text = $data['content'];

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
	public function toHtml(): string
	{
		if (is_null($this->html))
		{
			$text = $this->toMarkdown();

			$data = app(Pipeline::class)
				->send([
					'id' => $this->id,
					'content' => $text,
					'variables' => $this->getContentVars(),
				])
				->through([
					MarkdownToHtml::class,
					FixHtml::class,
					//HighlightUnusedVariables::class,
				])
				->thenReturn();

			$text = $data['content'];

			if (count($this->tags))
			{
				preg_match_all('/(^|[^a-z0-9_])#([a-z0-9\-_\.]+)/i', $text, $matches);

				if (!empty($matches))
				{
					foreach ($matches[0] as $match)
					{
						$slug = preg_replace("/[^a-z0-9\-_]+/i", '', $match);

						if ($tag = $this->isTag($slug))
						{
							$text = str_replace($match, ' <a class="tag badge badge-sm badge-secondary" href="' . route((app('isAdmin') ? 'admin' : 'site') . '.issues.index', ['tag' => $tag->slug]) . '">' . $tag->name . '</a> ', $text);
						}
					}
				}
			}

			$this->html = $text;
		}

		return $this->html;
	}

	/**
	 * Get formatted report
	 *
	 * @deprecated
	 * @return string
	 */
	public function getFormattedReportAttribute(): string
	{
		return $this->toHtml();
	}

	/**
	 * Strip code blocks
	 *
	 * @param   array  $match
	 * @return  string
	 */
	protected function stripCode($match): string
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
	protected function stripPre($match): string
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
	protected function replaceCode($match): string
	{
		return array_shift($this->replacements['codeblocks']);
	}

	/**
	 * Replace pre block
	 *
	 * @param   array  $match
	 * @return  string
	 */
	protected function replacePre($match): string
	{
		return array_shift($this->replacements['preblocks']);
	}

	/**
	 * Delete the record and all associated data
	 *
	 * @return  bool  False if error, True on success
	 */
	public function delete(): bool
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
	 * @param   string|null  $startdate
	 * @return  string
	 */
	public function formatDate($startdate): string
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
	 * @return  array<string,string>
	 */
	public function getContentVars(): array
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

		$vars = array_merge($vars, $this->getAttributes());

		return $vars;
	}

	/**
	 * Generate stemmed report
	 *
	 * @param   string  $value
	 * @return  void
	 */
	public function setReportAttribute($value): void
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
			$stem = PorterStemmer::stem($word);
			$stem = substr($stem, 0, 1) . $stem;

			array_push($stems, $stem);

			// If word ends in a number, also store it without the number
			if (preg_match('/[A-Za-z]+[0-9]+/', $word))
			{
				$word = preg_replace('/[^A-Za-z]/', '', $word);

				$stem = PorterStemmer::stem($word);
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
	}

	/**
	 * Strip URL
	 *
	 * @param   array  $match
	 * @return  string
	 */
	private function stripURL($match): string
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
	public function getResourcesStringAttribute(): string
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
