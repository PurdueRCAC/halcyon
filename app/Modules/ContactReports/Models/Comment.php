<?php

namespace App\Modules\ContactReports\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Pipeline\Pipeline;
use App\Halcyon\Utility\PorterStemmer;
use App\Modules\History\Traits\Historable;
use App\Modules\ContactReports\Events\CommentCreated;
use App\Modules\ContactReports\Events\CommentUpdated;
use App\Modules\ContactReports\Events\CommentDeleted;
use App\Modules\ContactReports\Traits\HasPreformattedText;
use App\Modules\ContactReports\Formatters\AbsoluteUrls;
use App\Modules\ContactReports\Formatters\FixHtml;
use App\Modules\ContactReports\Formatters\MarkdownToHtml;
use App\Modules\ContactReports\Formatters\ReplaceVariables;
use Carbon\Carbon;

/**
 * Model for a contact report comment
 *
 * @property int    $id
 * @property int    $contactreportid
 * @property int    $userid
 * @property string $comment
 * @property string $stemmedcomment
 * @property Carbon|null $datetimecreated
 * @property int    $notice
 *
 * @property string $api
 */
class Comment extends Model
{
	use Historable, HasPreformattedText;

	/**
	 * Notice values
	 *
	 * @var int
	 */
	const NO_NOTICE = 0;
	const NOTICE_NEW = 22;

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
	 * The table to which the class pertains
	 *
	 * @var  string
	 */
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
	 * @var array<int,string>
	 */
	protected $guarded = [
		'id'
	];

	/**
	 * The event map for the model.
	 *
	 * @var array<string,string>
	 */
	protected $dispatchesEvents = [
		'created' => CommentCreated::class,
		'updated' => CommentUpdated::class,
		'deleted' => CommentDeleted::class,
	];

	/**
	 * Defines a relationship to creator
	 *
	 * @return  BelongsTo
	 */
	public function creator(): BelongsTo
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'userid');
	}

	/**
	 * Defines a relationship to contact report
	 *
	 * @return  BelongsTo
	 */
	public function report(): BelongsTo
	{
		return $this->belongsTo(Report::class, 'contactreportid');
	}

	/**
	 * Get formatted created time
	 *
	 * @return  string
	 */
	public function getFormattedDateAttribute(): string
	{
		$datestring = '';

		if ($this->datetimecreated)
		{
			$startdate = $this->datetimecreated->toDateTimeString();

			$starttime = explode(' ', $startdate);
			$starttime = $starttime[1];

			$datestring = $this->datetimecreated->format('F j, Y');
			if ($starttime != '00:00:00')
			{
				$datestring .= ' ' . $this->datetimecreated->format('g:ia');
			}
		}

		return $datestring;
	}

	/**
	 * Get content variables
	 *
	 * @return array<string,mixed>
	 */
	public function getContentVars(): array
	{
		$uvars = array(
			'updatedatetime' => $this->datetimecreated ? $this->datetimecreated->format('Y-m-d h:i:s') : '',
			'updatedate'     => $this->datetimecreated ? $this->datetimecreated->format('l, F jS, Y') : '',
			'updatetime'     => $this->datetimecreated ? $this->datetimecreated->format('g:ia') : ''
		);

		$variables = array_merge($this->report->getContentVars(), $uvars);

		return $variables;
	}

	/**
	 * Get the comment formatted as MarkDown
	 *
	 * @return string
	 */
	public function toMarkdown(): string
	{
		$text = $this->comment;
		$text = $text ?: '';

		$text = $this->removePreformattedText($text);

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

		$text = $this->putbackPreformattedText($text);

		return $text;
	}

	/**
	 * Get the comment formatted as HTML
	 *
	 * @return string
	 */
	public function toHtml(): string
	{
		$data = app(Pipeline::class)
				->send([
					'id' => $this->id,
					'content' => $this->toMarkdown(),
					'variables' => $this->getContentVars(),
				])
				->through([
					MarkdownToHtml::class,
					FixHtml::class,
					//HighlightUnusedVariables::class,
				])
				->thenReturn();

		return $data['content'];
	}

	/**
	 * Get the comment formatted as HTML
	 *
	 * @deprecated
	 * @return string
	 */
	public function getFormattedCommentAttribute(): string
	{
		return $this->toHtml();
	}

	/**
	 * Generate stemmed comment
	 *
	 * @param   string  $value
	 * @return  void
	 */
	public function setCommentAttribute($value): void
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

		$this->attributes['stemmedcomment'] = $stemmedreport;
	}

	/**
	 * Strip URL
	 *
	 * @param   array<int,string>  $match
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
}
