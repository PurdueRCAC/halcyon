<?php

namespace App\Modules\Tags\Models;

use Illuminate\Database\Eloquent\Model;
use App\Halcyon\Traits\ErrorBag;
use App\Halcyon\Traits\Validatable;
use App\Modules\History\Traits\Historable;

/**
 * Tag object association
 */
class Tagged extends Model
{
	use ErrorBag, Validatable, Historable;

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
	protected static function boot()
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
	 * @return  object
	 */
	public function tag()
	{
		return $this->belongsTo(Tag::class, 'tag_id');
	}

	/**
	 * Creator user
	 *
	 * @return  object
	 */
	public function creator()
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'created_by');
	}

	/**
	 * Retrieves one row loaded by a tag field
	 *
	 * @param   string   $scope     Object type (ex: resource, ticket)
	 * @param   integer  $scope_id  Object ID (e.g., resource ID, ticket ID)
	 * @param   integer  $tag_id    Tag ID
	 * @param   integer  $tagger    User ID of person adding tag
	 * @return  Tagged|null
	 **/
	public static function findByScoped($scope, $scope_id, $tag_id, $tagger=0)
	{
		$instance = self::query()
			->where('taggable_type', '=', $scope)
			->where('taggable_id', '=', $scope_id)
			->where('tag_id', '=', $tag_id);

		if ($tagger)
		{
			$instance->where('created_by', '=', $tagger);
		}

		return $instance->limit(1)->get()->first();
	}

	/**
	 * Move all references to one tag to another tag
	 *
	 * @param   integer  $oldtagid  ID of tag to be moved
	 * @param   integer  $newtagid  ID of tag to move to
	 * @return  boolean  True if records changed
	 */
	public static function moveTo(int $oldtagid, int $newtagid)
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
	 * @param   integer  $oldtagid  ID of tag to be copied
	 * @param   integer  $newtagid  ID of tag to copy to
	 * @return  boolean  True if records copied
	 */
	public static function copyTo(int $oldtagid, int $newtagid)
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
