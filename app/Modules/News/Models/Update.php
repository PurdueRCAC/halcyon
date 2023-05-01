<?php

namespace App\Modules\News\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Pipeline\Pipeline;
use App\Modules\History\Traits\Historable;
use App\Modules\News\Events\UpdatePrepareContent;
use App\Modules\News\Formatters\AbsoluteUrls;
use App\Modules\News\Formatters\FixHtml;
use App\Modules\News\Formatters\MarkdownToHtml;
use App\Modules\News\Formatters\NewsStory;
use App\Modules\News\Formatters\ReplaceVariables;
use App\Modules\News\Traits\HasPreformattedText;
use Carbon\Carbon;

/**
 * Model for a news article update
 *
 * @property int    $id
 * @property int    $userid
 * @property int    $edituserid
 * @property Carbon|null $datetimecreated
 * @property Carbon|null $datetimeedited
 * @property Carbon|null $datetimeremoved
 * @property string $body
 * @property int    $newsid
 */
class Update extends Model
{
	use Historable, SoftDeletes, HasPreformattedText;

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
	const UPDATED_AT = 'datetimeedited';

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
	 * @var array<int,string>
	 */
	protected $guarded = [
		'id'
	];

	/**
	 * MarkDown version of the entry
	 *
	 * @var string
	 */
	protected $markdown = null;

	/**
	 * HTML version of the entry
	 *
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
	 * Defines a relationship to editor
	 *
	 * @return  BelongsTo
	 */
	public function modifier(): BelongsTo
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'edituserid');
	}

	/**
	 * Defines a relationship to article
	 *
	 * @return  BelongsTo
	 */
	public function article(): BelongsTo
	{
		return $this->belongsTo(Article::class, 'newsid');
	}

	/**
	 * Determine if entry was edited
	 *
	 * @return  bool
	 */
	public function isModified(): bool
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
	 * Get news vars
	 *
	 * @return  array<string,mixed>
	 */
	public function getContentVars(): array
	{
		$uvars = array(
			'updatedatetime' => $this->datetimecreated->format('F j, Y g:ia T'),
			'updatedate'     => $this->datetimecreated->format('l, F jS, Y'),
			'updatetime'     => $this->datetimecreated->format('g:ia T')
		);

		$news = $this->article->getContentVars();

		$vars = array_merge($news, $uvars);

		return $vars;
	}

	/**
	 * Format body as MarkDown
	 *
	 * @return string
	 */
	public function toMarkdown()
	{
		if (!$this->markdown)
		{
			event($event = new UpdatePrepareContent($this->body));

			$text = $event->getBody();

			$text = $this->removePreformattedText($text);

			$data = app(Pipeline::class)
					->send([
						'id' => $this->id,
						'content' => $text,
						'headline' => $this->article->headline,
						'variables' => $this->getContentVars(),
					])
					->through([
						AbsoluteUrls::class,
						ReplaceVariables::class,
						NewsStory::class,
					])
					->thenReturn();

			$text = $data['content'];

			$text = $this->putbackPreformattedText($text);

			$this->markdown = $text;
		}

		return $this->markdown;
	}

	/**
	 * Format body as HTML
	 *
	 * @return string
	 */
	public function toHtml()
	{
		if (!$this->html)
		{
			$text = $this->toMarkdown();

			$data = app(Pipeline::class)
				->send([
					'id' => $this->id,
					'content' => $text,
					'headline' => $this->article->headline,
					'variables' => $this->getContentVars(),
				])
				->through([
					MarkdownToHtml::class,
					FixHtml::class,
					//HighlightUnusedVariables::class,
				])
				->thenReturn();

			$this->html = $data['content'];
		}

		return $this->html;
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
