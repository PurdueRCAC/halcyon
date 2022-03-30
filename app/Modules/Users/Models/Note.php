<?php
namespace App\Modules\Users\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\Tags\Traits\Taggable;
use App\Modules\History\Traits\Historable;
use App\Modules\Users\Events\NoteCreated;
use App\Modules\Users\Events\NoteUpdated;
use App\Modules\Users\Events\NoteDeleted;

/**
 * User note model
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
	 * Fields and their validation criteria
	 *
	 * @var  array
	 */
	protected $rules = array(
		'user_id' => 'positive|nonzero',
		'body'    => 'notempty'
	);

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var  array
	 */
	protected $dates = [
		'created_at',
		'updated_at',
		'deleted_at',
	];

	/**
	 * The event map for the model.
	 *
	 * @var array
	 */
	protected $dispatchesEvents = [
		'created'  => NoteCreated::class,
		'updated'  => NoteUpdated::class,
		'deleted'  => NoteDeleted::class,
	];

/**
	 * Runs extra setup code when creating/updating a new model
	 *
	 * @return  void
	 */
	protected static function boot()
	{
		parent::boot();

		// Parse out hashtags and tag the record
		static::created(function ($model)
		{
			preg_match_all('/(^|[^a-z0-9_])#([a-z0-9\-_]+)/i', $model->body, $matches);

			if (!empty($matches[0]))
			{
				$tags = array();

				foreach ($matches[0] as $match)
				{
					$tag = preg_replace("/[^a-z0-9\-_]+/i", '', $match);

					// Ignore purely numeric items as this is most likely
					// a reference to some ID. e.g., ticket #1234
					if (is_numeric($tag))
					{
						continue;
					}

					$tags[] = $tag;
				}

				$model->setTags($tags);
			}
		});

		static::updated(function ($model)
		{
			preg_match_all('/(^|[^a-z0-9_])#([a-z0-9\-_]+)/i', $model->body, $matches);

			if (!empty($matches[0]))
			{
				$tags = array();

				foreach ($matches[0] as $match)
				{
					$tag = preg_replace("/[^a-z0-9\-_]+/i", '', $match);

					// Ignore purely numeric items as this is most likely
					// a reference to some ID. e.g., ticket #1234
					if (is_numeric($tag))
					{
						continue;
					}

					$tags[] = $tag;
				}

				$model->setTags($tags);
			}
		});
	}

	/**
	 * Get user
	 *
	 * @return  object
	 */
	public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
	}

	/**
	 * Get creator
	 *
	 * @return  object
	 */
	public function creator()
	{
		return $this->belongsTo(User::class, 'created_by');
	}

	/**
	 * Get editor
	 *
	 * @return  object
	 */
	public function editor()
	{
		return $this->belongsTo(User::class, 'updated_by');
	}

	/**
	 * Get formatted body
	 *
	 * @return string
	 */
	public function getFormattedBodyAttribute()
	{
		$text = $this->body;

		if (class_exists('Parsedown'))
		{
			$mdParser = new \Parsedown();

			$text = $mdParser->text(trim($text));
		}

		return $text;
	}

/**
	 * Taggable namespace
	 */
	static $entityNamespace = 'usernote';

	/**
	 * Find all hashtags in the report
	 *
	 * @return  array
	 */
	public function getHashtagsAttribute()
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

				$this->addTag($match);

				$hashtag[] = $match;
			}
		}

		return implode(', ', $hashtag);
	}
}
