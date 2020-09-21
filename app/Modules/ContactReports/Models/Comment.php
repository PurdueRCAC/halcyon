<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Modules\ContactReports\Models;

use Illuminate\Database\Eloquent\Model;
use App\Halcyon\Traits\ErrorBag;
use App\Halcyon\Traits\Validatable;
use App\Modules\History\Traits\Historable;
use App\Halcyon\Utility\PorterStemmer;

/**
 * Model for a news article update
 */
class Comment extends Model
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
	 * @var string
	 */
	const UPDATED_AT = null;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'contactreportcomments';

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
		'body' => 'required'
	);

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var  array
	 */
	protected $dates = [
		'datetimecreated',
	];

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
	 * Defines a relationship to creator
	 *
	 * @return  object
	 */
	public function creator()
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'userid');
	}

	/**
	 * Defines a relationship to article
	 *
	 * @return  object
	 */
	public function report()
	{
		return $this->belongsTo(Report::class, 'contactreportid');
	}

	/**
	 * Defines a relationship to type
	 *
	 * @return  string
	 */
	public function getFormattedDateAttribute()
	{
		$startdate = $this->getOriginal('datetimecreated');
		$enddate = '0000-00-00 00:00:00';

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

		if ($enddate == '0000-00-00 00:00:00' || $startdate == $enddate)
		{
			$datestring = date("F j, Y", strtotime($startdate));
			if ($starttime != '00:00:00')
			{
				$datestring .= '&nbsp; ' . date("g:ia", strtotime($startdate));
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
	 * Defines a relationship to type
	 *
	 * @return string
	 */
	public function getFormattedCommentAttribute()
	{
		$text = $this->comment;

		if (class_exists('Parsedown'))
		{
			$mdParser = new \Parsedown();

			$text = $mdParser->text(trim($text));
		}

		// separate code blocks
		$text = preg_replace_callback("/\<pre\>(.*?)\<\/pre\>/i", [$this, 'stripPre'], $text);
		$text = preg_replace_callback("/\<code\>(.*?)\<\/code\>/i", [$this, 'stripCode'], $text);

		// convert template variables
		if (auth()->user() && auth()->user()->can('manage contactreports'))
		{
			$text = preg_replace("/%%([\w\s]+)%%/", '<span style="color:red">$0</span>', $text);
		}

		$uvars = array(
			'updatedatetime' => $this->getOriginal('datetimecreated'),
			'updatedate'     => date('l, F jS, Y', strtotime($this->getOriginal('datetimecreated'))),
			'updatetime'     => date("g:ia", strtotime($this->getOriginal('datetimecreated')))
		);

		$news = $this->report->getAttributes(); //$this->article->toArray();
		$news['resources'] = $this->report->resources->toArray();
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

		$vars = array_merge($news, $uvars);

		foreach ($vars as $var => $value)
		{
			$text = preg_replace("/%" . $var . "%/", $value, $text);
		}

		if (auth()->user() && auth()->user()->can('manage contactreports'))
		{
			$text = preg_replace("/%([\w\s]+)%/", '<span style="color:red">$0</span>', $text);
		}

		$text = preg_replace_callback("/\{\{PRE\}\}/", [$this, 'replacePre'], $text);
		$text = preg_replace_callback("/\{\{CODE\}\}/", [$this, 'replaceCode'], $text);

		$text = preg_replace("/<p>(.*)(<table.*?>)(.*<\/table>)/m", "<p>$2 <caption>$1</caption>$3", $text);

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
	 * Generate stemmed comment
	 *
	 * @param   string  $value
	 * @return  string
	 */
	public function setCommentAttribute($value)
	{
		$this->attributes['comment'] = $value;

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

		return $stemmedreport;
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
}
