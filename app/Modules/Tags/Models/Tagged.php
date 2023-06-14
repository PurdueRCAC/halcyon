<?php

namespace App\Modules\Tags\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\History\Traits\Historable;

/**
 * Tag object association
 *
 * @property int    $id
 * @property int    $tag_id
 * @property int    $taggable_id
 * @property string $taggable_type
 * @property int    $created_by
 * @property Carbon|null $created_at
 */
class Tagged extends Model
{
	use Historable;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 */
	protected $table = 'tags_tagged';

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'created_at';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public static $orderDir = 'desc';

	/**
	 * Runs extra setup code when creating a new model
	 *
	 * @return  void
	 */
	protected static function boot(): void
	{
		parent::boot();

		static::created(function ($model)
		{
			$c = self::query()
				->where('tag_id', '=', $model->tag_id)
				->count();

			$model->tag->update(['tagged_count' => $c]);

			return true;
		});
	}

	/**
	 * Get parent tag
	 *
	 * @return  BelongsTo
	 */
	public function tag(): BelongsTo
	{
		return $this->belongsTo(Tag::class, 'tag_id');
	}

	/**
	 * Creator user
	 *
	 * @return  BelongsTo
	 */
	public function creator(): BelongsTo
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'created_by');
	}

	/**
	 * Retrieves one row loaded by a tag field
	 *
	 * @param   string   $scope     Object type (ex: resource, ticket)
	 * @param   int  $scope_id  Object ID (e.g., resource ID, ticket ID)
	 * @param   int  $tag_id    Tag ID
	 * @param   int  $tagger    User ID of person adding tag
	 * @return  Tagged|null
	 **/
	public static function findByScoped($scope, $scope_id, $tag_id, $tagger=0)
	{
		$instance = self::query()
			->where('taggable_type', '=', (string)$scope)
			->where('taggable_id', '=', (int)$scope_id)
			->where('tag_id', '=', (int)$tag_id);

		if ($tagger)
		{
			$instance->where('created_by', '=', $tagger);
		}

		return $instance->limit(1)->first();
	}

	/**
	 * Move all references to one tag to another tag
	 *
	 * @param   int  $oldtagid  ID of tag to be moved
	 * @param   int  $newtagid  ID of tag to move to
	 * @return  bool  True if records changed
	 */
	public static function moveTo(int $oldtagid, int $newtagid): bool
	{
		if (!$oldtagid || !$newtagid)
		{
			return false;
		}

		$items = self::query()
			->where('tag_id', '=', $oldtagid)
			->get();

		foreach ($items as $item)
		{
			$item->update(['tag_id' => $newtagid]);
		}

		return true;
	}

	/**
	 * Copy all tags on an object to another object
	 *
	 * @param   int  $oldtagid  ID of tag to be copied
	 * @param   int  $newtagid  ID of tag to copy to
	 * @return  bool  True if records copied
	 */
	public static function copyTo(int $oldtagid, int $newtagid): bool
	{
		if (!$oldtagid || !$newtagid)
		{
			return false;
		}

		$rows = self::query()
			->where('tag_id', '=', $oldtagid)
			->get();

		if ($rows)
		{
			foreach ($rows as $row)
			{
				$row->id = null;
				$row->tag_id = $newtagid;
				$row->save();
			}
		}

		return true;
	}
}
