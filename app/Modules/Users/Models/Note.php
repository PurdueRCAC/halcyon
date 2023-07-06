<?php
namespace App\Modules\Users\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use App\Modules\Tags\Traits\Taggable;
use App\Modules\History\Traits\Historable;
use App\Modules\Users\Events\NoteCreated;
use App\Modules\Users\Events\NoteUpdated;
use App\Modules\Users\Events\NoteDeleted;
use Carbon\Carbon;

/**
 * User note model
 *
 * @property int    $id
 * @property int    $user_id
 * @property string $body
 * @property Carbon|null $created_at
 * @property int    $created_by
 * @property Carbon|null $updated_at
 * @property int    $updated_by
 * @property Carbon|null $deleted_at
 */
class Note extends Model
{
	use SoftDeletes, Historable, Taggable;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'user_notes';

	/**
	 * Default order by for model
	 *
	 * @var  string
	 */
	public static $orderBy = 'created_at';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public static $orderDir = 'asc';

	/**
	 * The event map for the model.
	 *
	 * @var array<string,string>
	 */
	protected $dispatchesEvents = [
		'created' => NoteCreated::class,
		'updated' => NoteUpdated::class,
		'deleted' => NoteDeleted::class,
	];

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
			$model->hashtags;
		});

		static::updated(function ($model)
		{
			$model->hashtags;
		});
	}

	/**
	 * Get user
	 *
	 * @return  BelongsTo
	 */
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class, 'user_id');
	}

	/**
	 * Get creator
	 *
	 * @return  BelongsTo
	 */
	public function creator(): BelongsTo
	{
		return $this->belongsTo(User::class, 'created_by');
	}

	/**
	 * Get editor
	 *
	 * @return  BelongsTo
	 */
	public function editor(): BelongsTo
	{
		return $this->belongsTo(User::class, 'updated_by');
	}

	/**
	 * Get formatted body
	 *
	 * @return string
	 */
	public function getFormattedBodyAttribute(): string
	{
		$text = $this->body;

		$converter = new CommonMarkConverter([
			'html_input' => 'allow',
		]);
		$converter->getEnvironment()->addExtension(new TableExtension());
		$converter->getEnvironment()->addExtension(new StrikethroughExtension());
		$converter->getEnvironment()->addExtension(new AutolinkExtension());

		$text = (string) $converter->convertToHtml($text);

		return $text;
	}

	/**
	 * Taggable namespace
	 *
	 * @var string
	 */
	public static $entityNamespace = 'usernote';

	/**
	 * Find all hashtags in the note
	 *
	 * @return  string
	 */
	public function getHashtagsAttribute(): string
	{
		$str = $this->body;

		preg_match_all('/(^|[^a-z0-9_])#([a-z0-9\-_]+)/i', $str, $matches);

		$hashtag = [];
		if (!empty($matches[0]))
		{
			foreach ($matches[0] as $match)
			{
				$match = preg_replace("/[^a-z0-9\-_]+/i", '', $match);

				if (is_numeric($match))
				{
					continue;
				}

				$hashtag[] = $match;
			}

			$this->setTags($hashtag);
		}

		return implode(', ', $hashtag);
	}
}
