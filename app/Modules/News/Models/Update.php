<?php

namespace App\Modules\News\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Halcyon\Traits\ErrorBag;
use App\Halcyon\Traits\Validatable;
use App\Modules\History\Traits\Historable;
use App\Modules\News\Events\UpdatePrepareContent;

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
	 * Defines a relationship to creator
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
	 * @param   string  $startdate
	 * @return  string
	 */
	public function formattedDatetimecreated(string $startdate)
	{
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
	 * Format body as MarkDown
	 *
	 * @return string
	 */
	public function toMarkdown()
	{
		$text = $this->body;

		event($event = new UpdatePrepareContent($text));

		$text = $event->getBody();

		$text = preg_replace_callback("/```(.*?)```/i", [$this, 'stripPre'], $text);
		$text = preg_replace_callback("/`(.*?)`/i", [$this, 'stripCode'], $text);

		$uvars = array(
			'updatedatetime' => date('F j, Y g:ia', strtotime($this->getOriginal('datetimecreated'))),
			'updatedate'     => date('l, F jS, Y', strtotime($this->getOriginal('datetimecreated'))),
			'updatetime'     => date("g:ia", strtotime($this->getOriginal('datetimecreated')))
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

		if (auth()->user() && auth()->user()->can('manage news'))
		{
			$text = preg_replace("/%([\w\s]+)%/", '<span style="color:red">$0</span>', $text);
		}

		$text = preg_replace_callback("/\{\{PRE\}\}/", [$this, 'replacePre'], $text);
		$text = preg_replace_callback("/\{\{CODE\}\}/", [$this, 'replaceCode'], $text);
		$text = str_replace('<th>', '<th scope="col">', $text);

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
		$text = $this->body;

		event($event = new UpdatePrepareContent($text));

		$text = $event->getBody();

		if (class_exists('Parsedown'))
		{
			$mdParser = new \Parsedown();

			$text = $mdParser->text(trim($text));
		}

		// separate code blocks
		$text = preg_replace("/\}\}\n\{\{/", "}}\n\n\n{{", $text);
		$text = preg_replace("/\}\}\n\n\{\{/", "}}\n\n\n{{", $text);

		// no secret words allowed!
		$text = preg_replace("/\{\{CODE\}\}/", '', $text);

		// first find and remove code blocks. we want to preserve them
		//$text = preg_replace_callback("/(^|\n)(\n)?[{]{2}\n([\s\S]+?)\n[}]{2}(\n|$)/", 'stripCode', $text);
		//$text = preg_replace_callback("/(^|\n)(\n)?[|]{2}\n([\s\S]+?)\n[|]{2}(\n|$)/", 'matchTable', $text);

		// strip unneccessary whitespace
		$text = preg_replace("/(^|\n)[ \t]+/", "$1", $text);
		$text = preg_replace("/\n\n+/", "\n\n", $text);
		$text = preg_replace("/\r/", "\n", $text);

		// swap &#8859; for -
		$text = preg_replace("/\&#8859;/", '-', $text);

		// make bulleted lists
		// first get the ul tags
		$text = preg_replace("/(((\n|^)[\*\-]\s+.+?)+)(\n|$)/", "$3<ul class=\"list\">$1</ul>\n\n", $text);
		$text = preg_replace("/^\n<ul/", "<ul", $text);
		// get first line
		$text = preg_replace("/((<ul class = \"list\">)[\*\-]\s+(.+?))(?=\n|$)/", "<ul class=\"list\"><li>$3</li>", $text);
		// get rest of lines
		$text = preg_replace("/((\n)[\*\-]\s+(.+?))(?=\n|$)/", "<li>$3</li>", $text);
		$text = preg_replace("/<\/ul><\/li>/", "</li></ul>", $text);

		// make numbered lists
		// first get the ol tags
		$text = preg_replace("/(((\n|^)\d+[\)\.]\s+.+?)+)(\n|$)/", "$3<ol class=\"list\">$1</ol>\n\n", $text);
		$text = preg_replace("/^\n<ol/", "<ol", $text);
		// get first line
		$text = preg_replace("/((<ol class = \"list\">)\d+[\)\.]\s+(.+?))(?=\n|$)/", "<ol class=\"list\"><li>$3</li>", $text);
		// get rest of lines
		$text = preg_replace("/((\n)\d+[\)\.]\s+(.+?))(?=\n|$)/", "<li>$3</li>", $text);
		$text = preg_replace("/<\/ol><\/li>/", "</li></ol>", $text);

		// bold
		$text = preg_replace("/(^|\W|_)\*(\S.*?)\*(\W|$|_)/", "$1<span style=\"font-weight:bold;\">$2</span>$3", $text);
		// italics
		$text = preg_replace("/(^|\W)_(\S.*?)_(\W|$)/", "$1<span style=\"font-style:italic;\">$2</span>$3", $text);
		// hyperlinks
		//$text = preg_replace_callback(REGEXP_URL, 'matchURL', $text);

		// convert emails
		$text = preg_replace('/([\w\.\-]+@((\w+\.)*\w{2,}\.\w{2,}))/', "<a target=\"_blank\" href=\"mailto:$1\">$1</a>", $text);

		// convert template variables
		if (auth()->user() && auth()->user()->can('manage news'))
		{
			$text = preg_replace("/%%([\w\s]+)%%/", '<span style="color:red">$0</span>', $text);
		}

		/*$uvars = array(
			'updatedatetime' => date('F j, Y g:ia', strtotime($this->getOriginal('datetimecreated'))),
			'updatedate'     => date('l, F jS, Y', strtotime($this->getOriginal('datetimecreated'))),
			'updatetime'     => date("g:ia", strtotime($this->getOriginal('datetimecreated')))
		);

		$news = $this->article->getAttributes();
		$news['resources'] = $this->article->resources->toArray();
		$resources = array();
		foreach ($this->article->resources as $r)
		{
			$resource = $r->toArray();
			$resource['resourcename'] = $r->resource->name;
			array_push($resources, $resource['resourcename']);
		}

		if (count($resources) > 1)
		{
			$resources[count($resources)-1] = 'and ' . $resources[count($resources)-1];
		}

		$news['resources'] = implode(', ', $resources);

		$vars = array_merge($news, $uvars);

		foreach ($vars as $var => $value)
		{
			$text = preg_replace("/%" . $var . "%/", $value, $text);
		}*/

		if (auth()->user() && auth()->user()->can('manage news'))
		{
			$text = preg_replace("/%([\w\s]+)%/", '<span style="color:red">$0</span>', $text);
		}

		// make <p>s
		/*$text = preg_replace("/\n\n\n+/", "\n\n", $text);
		$text = preg_replace("/^\n+/", '', $text);
		$text = preg_replace("/\n+$/", '', $text);
		$text = preg_replace("/\n\n/", "</p>\n<p>", $text);

		$text = preg_replace("/<p><ul/", "<ul", $text);
		$text = preg_replace("/<p><ol/", "<ol", $text);
		$text = preg_replace("/<\/ul><\/p>/", "</ul>\n", $text);
		$text = preg_replace("/<\/ol><\/p>/", "</ol>\n", $text);
		$text = preg_replace("/(.)\n<ul/", "$1</p>\n<ul", $text);
		$text = preg_replace("/(.)\n<ol/", "$1</p>\n<ol", $text);*/

		//$text = preg_replace_callback("/\{\{CODE\}\}/", 'replaceCode', $text);

		//$text = '<p>' . $text . '</p>';
		$text = preg_replace("/<p>(.*)(<table.*?>)(.*<\/table>)/m", "<p>$2 <caption>$1</caption>$3", $text);

		return $text;
	}

	/**
	 * Replace code block
	 *
	 * @param   array  $match
	 * @return  string
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
	 * Format news date
	 *
	 * @param   string  $startdate
	 * @return  string
	 */
	public function formatDate($startdate)
	{
		$starttime = explode(' ', $startdate);
		$starttime = $starttime[1];

		$datestring = date("F j, Y", strtotime($startdate));
		if ($starttime != '00:00:00')
		{
			$datestring .= ' ' . date("g:ia", strtotime($startdate));
		}

		return $datestring;
	}
}
