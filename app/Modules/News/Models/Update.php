<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Modules\News\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Halcyon\Config\Registry;
use App\Halcyon\Traits\ErrorBag;
use App\Halcyon\Traits\Validatable;
use App\Modules\History\Traits\Historable;

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
		'body' => 'required'
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
	 * If item is removed
	 *
	 * @return  bool
	 **/
	public function isTrashed()
	{
		return ($this->datetimeremoved && $this->datetimeremoved != '0000-00-00 00:00:00' && $this->datetimeremoved != '-0001-11-30 00:00:00');
	}

	/**
	 * Defines a relationship to type
	 *
	 * @return  string
	 */
	public function formattedDatetimecreated($datetimecreated)
	{
		$startdate = $datetimecreated;
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
	public function getformattedBodyAttribute()
	{
		$text = $this->body;

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

		$uvars = array(
			'updatedatetime' => $this->getOriginal('datetimecreated'),
			'updatedate'     => date('l, F jS, Y', strtotime($this->getOriginal('datetimecreated'))),
			'updatetime'     => date("g:ia", strtotime($this->getOriginal('datetimecreated')))
		);

		$news = $this->article->getAttributes(); //$this->article->toArray();
		$news['resources'] = $this->article->resources->toArray();
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

		/*if (count($resources) > 2)
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
		}*/
		$news['resources'] = implode(', ', $resources);

		$vars = array_merge($news, $uvars);

		foreach ($vars as $var => $value)
		{
			$text = preg_replace("/%" . $var . "%/", $value, $text);
		}

		if (auth()->user() && auth()->user()->can('manage news'))
		{
			$text = preg_replace("/%([\w\s]+)%/", '<span style="color:red">$0</span>', $text);
		}

		// convert links
		//$text = preg_replace_callback("/(news)\s*(story|item)?\s*#?(\d+)(\{.+?\})?/i", 'matchNews', $text);
		//$text = preg_replace("/(contact|CRM?)(\s+report)?\s*#?(\d+)/i", "<a href=\"/account/crm/?id=$3\">Contact Report #$3</a>", $text);
		//$text = preg_replace("/((foot\s*prints?)|(FP))(\s+ticket)?\s*#?(\d+)/i", "<a target=\"_blank\" rel=\"noopener\" href=\"https://support.purdue.edu/MRcgi/MRlogin.pl?DL=$5DA17\">Footprints #$5</a>", $text);

		// make <p>s
		$text = preg_replace("/\n\n\n+/", "\n\n", $text);
		$text = preg_replace("/^\n+/", '', $text);
		$text = preg_replace("/\n+$/", '', $text);
		$text = preg_replace("/\n\n/", "</p>\n<p>", $text);

		$text = preg_replace("/<p><ul/", "<ul", $text);
		$text = preg_replace("/<p><ol/", "<ol", $text);
		$text = preg_replace("/<\/ul><\/p>/", "</ul>\n", $text);
		$text = preg_replace("/<\/ol><\/p>/", "</ol>\n", $text);
		$text = preg_replace("/(.)\n<ul/", "$1</p>\n<ul", $text);
		$text = preg_replace("/(.)\n<ol/", "$1</p>\n<ol", $text);

		//$text = preg_replace_callback("/\{\{CODE\}\}/", 'replaceCode', $text);

		$text = '<p>' . $text . '</p>';
		$text = preg_replace("/<p>(.*)(<table.*?>)(.*<\/table>)/m", "<p>$2 <caption>$1</caption>$3", $text);

		return $text;
	}

	/**
	 * Replace code block
	 *
	 * @param   array  $match
	 * @return  string
	 */
	protected function replaceCode($match)
	{
		$code = array_shift($this->codeblocks);

		$c = '';

		if (preg_match("/^\n/", $code))
		{
			$c .= ' codetop';
		}

		$code = preg_replace("/^\n/", '', $code);

		return '<pre class="code' . $c . '">' . $code . '</pre>';
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
	 * Query scope where record isn't trashed
	 *
	 * @param   object  $query
	 * @return  object
	 */
	public function scopeWhereIsActive($query)
	{
		$t = $this->getTable();

		return $query->where(function($where) use ($t)
		{
			$where->whereNull($t . '.datetimeremoved')
					->orWhere($t . '.datetimeremoved', '=', '0000-00-00 00:00:00');
		});
	}

	/**
	 * Query scope where record is trashed
	 *
	 * @param   object  $query
	 * @return  object
	 */
	public function scopeWhereIsTrashed($query)
	{
		$t = $this->getTable();

		return $query->where(function($where) use ($t)
		{
			$where->whereNotNull($t . '.datetimeremoved')
				->where($t . '.datetimeremoved', '!=', '0000-00-00 00:00:00');
		});
	}
}
