<?php

namespace App\Modules\Issues\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use App\Halcyon\Utility\PorterStemmer;
use App\Halcyon\Models\Timeperiod;
use App\Modules\History\Traits\Historable;
use App\Modules\Issues\Events\ReportPrepareContent;
use App\Modules\Users\Models\User;
use Carbon\Carbon;

/**
 * To-Do model
 */
class ToDo extends Model
{
	use Historable, SoftDeletes;

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
	protected $table = 'issuetodos';

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'name';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public static $orderDir = 'asc';

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
		'name' => 'required|string|max:255',
		'description' => 'required|string|max:2000'
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
	 * Defines a relationship to issues
	 *
	 * @return  object
	 */
	public function issues()
	{
		return $this->hasMany(Issue::class, 'issuetodoid');
	}

	/**
	 * Defines a relationship to timeperiod
	 *
	 * @return  object
	 */
	public function timeperiod()
	{
		return $this->belongsTo(Timeperiod::class, 'recurringtimeperiodid');
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
	 * Delete the record and all associated data
	 *
	 * @return  boolean  False if error, True on success
	 */
	public function delete()
	{
		foreach ($this->issues as $issue)
		{
			$issue->delete();
		}

		// Attempt to delete the record
		return parent::delete();
	}

	/**
	 * Get the status (complete/incomplete) of the current item
	 *
	 * @return string
	 */
	public function getStatusAttribute()
	{
		if (!isset($this->attributes['state']))
		{
			$status = 'incomplete';

			if ($this->timeperiod)
			{
				$now = Carbon::now();

				// Check for completed todos in the recurring time period
				switch ($this->timeperiod->name)
				{
					case 'hourly':
						$period = $now->format('Y-m-d h') . ':00:00';
					break;

					case 'daily':
						$period = $now->format('Y-m-d') . ' 00:00:00';
					break;

					case 'weekly':
						$day = date('w');
						$period = $now->modify('-' . $day . ' days')->format('Y-m-d') . ' 00:00:00';
					break;

					case 'monthly':
						$period = $now->format('Y-m-01') . ' 00:00:00';
					break;

					case 'annual':
						$period = $now->format('Y-01-01') . ' 00:00:00';
					break;
				}

				$issue = $this->issues()
					->where('datetimecreated', '>=', $period)
					->first();

				// We found an item for this time period
				if ($issue)
				{
					$status = 'complete';
					$this->attributes['issueid'] = $issue->id;
				}
			}

			$this->attributes['state'] = $status;
		}

		return $this->attributes['state'];
	}

	/**
	 * Get the description formatted as HTML
	 *
	 * @return string
	 */
	public function getFormattedDescriptionAttribute()
	{
		$text = $this->description;

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

		$text = preg_replace("/<p>(.*)(<table.*?>)(.*<\/table>)/m", "<p>$2 <caption>$1</caption>$3", $text);
		$text = str_replace('<th>', '<th scope="col">', $text);
		$text = str_replace('align="right"', 'class="text-right"', $text);

		$text = preg_replace_callback("/\{\{PRE\}\}/", [$this, 'replacePre'], $text);
		$text = preg_replace_callback("/\{\{CODE\}\}/", [$this, 'replaceCode'], $text);

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
}
