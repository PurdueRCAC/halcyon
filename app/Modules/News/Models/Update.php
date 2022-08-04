<?php

namespace App\Modules\News\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use App\Halcyon\Traits\ErrorBag;
use App\Halcyon\Traits\Validatable;
use App\Modules\History\Traits\Historable;
use App\Modules\News\Events\UpdatePrepareContent;
use Carbon\Carbon;

/**
 * Model for a news article update
 */
class Update extends Model
{
	use ErrorBag, Validatable, Historable, SoftDeletes;

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
	 * The name of the "deleted at" column.
	 *
	 * @var  string
	 */
	const DELETED_AT = 'datetimeremoved';

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'newsupdates';

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
	 * Fields and their validation criteria
	 *
	 * @var array
	 */
	protected $rules = array(
		'body' => 'required|string'
	);

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var  array
	 */
	protected $dates = [
		'datetimecreated',
		'datetimeedited',
		'datetimeremoved',
	];

	/**
	 * @var string
	 */
	protected $markdown = null;

	/**
	 * @var string
	 */
	protected $html = null;

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
	 * Defines a relationship to editor
	 *
	 * @return  object
	 */
	public function modifier()
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'edituserid');
	}

	/**
	 * Defines a relationship to article
	 *
	 * @return  object
	 */
	public function article()
	{
		return $this->belongsTo(Article::class, 'newsid');
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
	 * Format datetimecreated
	 *
	 * @return  string
	 */
	public function getFormattedDatetimecreatedAttribute()
	{
		return $this->formatDate($this->datetimecreated);
	}

	/**
	 * Format body as MarkDown
	 *
	 * @return string
	 */
	public function toMarkdown()
	{
		$text = $this->body;

		event($event = new UpdatePrepareContent($text));

		$text = $event->getBody();

		$text = preg_replace_callback("/```(.*?)```/uis", [$this, 'stripPre'], $text);
		$text = preg_replace_callback("/`(.*?)`/i", [$this, 'stripCode'], $text);

		$uvars = array(
			'updatedatetime' => $this->datetimecreated->format('F j, Y g:ia T'),
			'updatedate'     => $this->datetimecreated->format('l, F jS, Y'),
			'updatetime'     => $this->datetimecreated->format('g:ia T')
		);

		$news = $this->article->getContentVars();

		$vars = array_merge($news, $uvars);

		foreach ($vars as $var => $value)
		{
			$text = preg_replace("/%" . $var . "%/", $value, $text);
		}

		$text = preg_replace_callback("/(news)\s*(story|item)?\s*#?(\d+)(\{.+?\})?/i", array($this, 'matchNews'), $text);

		$text = preg_replace_callback("/\{\{PRE\}\}/", [$this, 'replacePre'], $text);
		$text = preg_replace_callback("/\{\{CODE\}\}/", [$this, 'replaceCode'], $text);

		return $text;
	}

	/**
	 * Format body as HTML
	 *
	 * @return string
	 */
	public function toHtml()
	{
		$text = $this->toMarkdown();

		$converter = new CommonMarkConverter([
			'html_input' => 'allow',
		]);
		$converter->getEnvironment()->addExtension(new TableExtension());
		$converter->getEnvironment()->addExtension(new StrikethroughExtension());
		$converter->getEnvironment()->addExtension(new AutolinkExtension());

		$text = (string) $converter->convertToHtml($text);

		// separate code blocks
		$text = preg_replace_callback("/\<pre\>(.*?)\<\/pre\>/uis", [$this, 'stripPre'], $text);
		$text = preg_replace_callback("/\<code\>(.*?)\<\/code\>/i", [$this, 'stripCode'], $text);

		// convert emails
		//$text = preg_replace('/([\w\.\-]+@((\w+\.)*\w{2,}\.\w{2,}))/', "<a target=\"_blank\" href=\"mailto:$1\">$1</a>", $text);

		// convert template variables
		if (auth()->user() && auth()->user()->can('manage news'))
		{
			$text = preg_replace("/%%([\w\s]+)%%/", '<span style="color:red">$0</span>', $text);
		}

		if (auth()->user() && auth()->user()->can('manage news'))
		{
			$text = preg_replace("/%([\w\s]+)%/", '<span style="color:red">$0</span>', $text);
		}

		$text = str_replace('<th>', '<th scope="col">', $text);
		$text = str_replace('align="right"', 'class="text-right"', $text);

		$text = preg_replace_callback("/\{\{PRE\}\}/", [$this, 'replacePre'], $text);
		$text = preg_replace_callback("/\{\{CODE\}\}/", [$this, 'replaceCode'], $text);

		$text = preg_replace('/<p>([^\n]+)<\/p>\n(<table.*?>)(.*?<\/table>)/usm', '$2 <caption>$1</caption>$3', $text);
		$text = preg_replace('/src="\/include\/images\/(.*?)"/i', 'src="' . asset("files/$1") . '"', $text);

		return $text;
	}

	/**
	 * Format body
	 *
	 * @deprecated
	 * @return string
	 */
	public function getFormattedBodyAttribute()
	{
		return $this->toHtml();
	}

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
	 * Expand NEWS#123 to linked article titles
	 * This resturns the linked title in MarkDown syntax
	 *
	 * @param   array  $match
	 * @return  string
	 */
	private function matchNews(array $match)
	{
		$title = trans('news::news.news story number', ['number' => $match[3]]);

		$news = Article::find($match[3]);

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
	 * Format update date
	 *
	 * @param   string|Carbon  $startdate
	 * @return  string
	 */
	public function formatDate($startdate)
	{
		$startdate = Carbon::parse($startdate);

		$starttime = explode(' ', $startdate->toDateTimeString());
		$starttime = $starttime[1];

		$datestring = $startdate->format('F j, Y');
		if ($starttime != '00:00:00')
		{
			$datestring .= ' ' . $startdate->format('g:ia T');
		}

		return $datestring;
	}
}
