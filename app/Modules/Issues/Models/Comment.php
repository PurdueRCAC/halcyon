<?php

namespace App\Modules\Issues\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Pipeline\Pipeline;
use App\Modules\Issues\Formatters\AbsoluteUrls;
use App\Modules\Issues\Formatters\FixHtml;
use App\Modules\Issues\Formatters\MarkdownToHtml;
use App\Modules\Issues\Formatters\ReplaceVariables;
use App\Modules\History\Traits\Historable;
use App\Halcyon\Utility\PorterStemmer;

/**
 * Issue comment model
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
class Comment extends Model
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
	protected $table = 'issuecomments';

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
	 * Code block replacements
	 *
	 * @var  array<string,array>
	 */
	private $replacements = array(
		'preblocks'  => array(),
		'codeblocks' => array()
	);

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
	 * @return  BelongsTo
	 */
	public function creator(): BelongsTo
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'userid');
	}

	/**
	 * Defines a relationship to issue
	 *
	 * @return  BelongsTo
	 */
	public function issue(): BelongsTo
	{
		return $this->belongsTo(Issue::class, 'issueid');
	}

	/**
	 * Get formatted date
	 *
	 * @return  string
	 */
	public function getFormattedDateAttribute(): string
	{
		$startdate = $this->datetimecreated->format('Y-m-d h:i:s');

		$starttime = explode(' ', $startdate);
		$starttime = $starttime[1];

		$datestring = $this->datetimecreated->format('F j, Y');
		if ($starttime != '00:00:00')
		{
			$datestring .= ' ' . $this->datetimecreated->format('g:ia');
		}

		return $datestring;
	}

	/**
	 * Get content variables
	 *
	 * @return array
	 */
	public function getContentVars(): array
	{
		$uvars = array(
			'updatedatetime' => $this->datetimecreated->format('Y-m-d h:i:s'),
			'updatedate'     => $this->datetimecreated->format('l, F jS, Y'),
			'updatetime'     => $this->datetimecreated->format('g:ia')
		);

		$variables = array_merge($this->issue->getContentVars(), $uvars);

		return $variables;
	}

	/**
	 * Get the comment formatted as MarkDown
	 *
	 * @return string
	 */
	public function toMarkdown(): string
	{
		if (is_null($this->markdown))
		{
			$text = $this->comment;

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
	 * Get the comment formatted as HTML
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

			$this->html = $text;
		}

		return $this->html;
	}

	/**
	 * Get formatted comment
	 *
	 * @deprecated
	 * @return string
	 */
	public function getFormattedCommentAttribute(): string
	{
		return $this->toHtml();
	}

	/**
	 * Strip code blocks
	 *
	 * @param   array<int,string>  $match
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
	 * @param   array<int,string>  $match
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
