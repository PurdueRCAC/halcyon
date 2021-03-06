<?php

namespace App\Modules\Tags\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Halcyon\Traits\ErrorBag;
use App\Halcyon\Traits\Validatable;
use App\Modules\History\Traits\Historable;
use App\Modules\Tags\Events\TagCreated;
use App\Modules\Tags\Events\TagUpdated;
use App\Modules\Tags\Events\TagDeleted;
use Carbon\Carbon;

/**
 * Tag model
 */
class Tag extends Model
{
	use ErrorBag, Validatable, Historable, SoftDeletes;

	/**
	 * The table to which the class pertains
	 *
	 * @var string
	 **/
	protected $table = 'tags';

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'name';

	/**
	 * Default order direction for select queries
	 *
	 * @var string
	 */
	public static $orderDir = 'asc';

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
		'name' => 'required|string|min:3|max:1500',
		'slug' => 'nullable|string|max:100',
		'parent_id' => 'nullable|integer'
	);

	/**
	 * The event map for the model.
	 *
	 * @var array
	 */
	protected $dispatchesEvents = [
		'created'  => TagCreated::class,
		'updated'  => TagUpdated::class,
		'deleted'  => TagDeleted::class,
	];

	/**
	 * Runs extra setup code when creating/deleting a new model
	 *
	 * @return  void
	 */
	protected static function booted()
	{
		static::created(function ($model)
		{
			if ($model->parent_id)
			{
				$total = self::query()
					->where('parent_id', '=', $model->parent_id)
					->count();

				$model->parent->update(['alias_count' => $total]);
			}
		});

		static::deleted(function ($model)
		{
			if ($model->parent_id)
			{
				$total = self::query()
					->where('parent_id', '=', $model->parent_id)
					->count();

				$model->parent->update(['alias_count' => $total]);
			}
		});
	}

	/**
	 * Generate stemmed report
	 *
	 * @param   string  $value
	 * @return  string
	 */
	public function setNameAttribute($value)
	{
		$this->attributes['name'] = $value;
		$this->attributes['slug'] = $this->normalize($value);

		return $value;
	}

	/**
	 * Normalize tag input
	 *
	 * @param   string  $tag
	 * @return  string
	 */
	public function normalize($name)
	{
		$transliterationTable = array(
			'??' => 'a', '??' => 'A', '??' => 'a', '??' => 'A', '??' => 'a', '??' => 'A', '??' => 'a', '??' => 'A', '??' => 'a', '??' => 'A', '??' => 'a', '??' => 'A', '??' => 'a', '??' => 'A', '??' => 'a', '??' => 'A', '??' => 'ae', '??' => 'AE', '??' => 'ae', '??' => 'AE',
			'???' => 'b', '???' => 'B',
			'??' => 'c', '??' => 'C', '??' => 'c', '??' => 'C', '??' => 'c', '??' => 'C', '??' => 'c', '??' => 'C', '??' => 'c', '??' => 'C',
			'??' => 'd', '??' => 'D', '???' => 'd', '???' => 'D', '??' => 'd', '??' => 'D', '??' => 'dh', '??' => 'Dh',
			'??' => 'e', '??' => 'E', '??' => 'e', '??' => 'E', '??' => 'e', '??' => 'E', '??' => 'e', '??' => 'E', '??' => 'e', '??' => 'E', '??' => 'e', '??' => 'E', '??' => 'e', '??' => 'E', '??' => 'e', '??' => 'E', '??' => 'e', '??' => 'E',
			'???' => 'f', '???' => 'F', '??' => 'f', '??' => 'F',
			'??' => 'g', '??' => 'G', '??' => 'g', '??' => 'G', '??' => 'g', '??' => 'G', '??' => 'g', '??' => 'G',
			'??' => 'h', '??' => 'H', '??' => 'h', '??' => 'H',
			'??' => 'i', '??' => 'I', '??' => 'i', '??' => 'I', '??' => 'i', '??' => 'I', '??' => 'i', '??' => 'I', '??' => 'i', '??' => 'I', '??' => 'i', '??' => 'I', '??' => 'i', '??' => 'I',
			'??' => 'j', '??' => 'J',
			'??' => 'k', '??' => 'K',
			'??' => 'l', '??' => 'L', '??' => 'l', '??' => 'L', '??' => 'l', '??' => 'L', '??' => 'l', '??' => 'L',
			'???' => 'm', '???' => 'M',
			'??' => 'n', '??' => 'N', '??' => 'n', '??' => 'N', '??' => 'n', '??' => 'N', '??' => 'n', '??' => 'N',
			'??' => 'o', '??' => 'O', '??' => 'o', '??' => 'O', '??' => 'o', '??' => 'O', '??' => 'o', '??' => 'O', '??' => 'o', '??' => 'O', '??' => 'oe', '??' => 'OE', '??' => 'o', '??' => 'O', '??' => 'o', '??' => 'O', '??' => 'oe', '??' => 'OE',
			'???' => 'p', '???' => 'P',
			'??' => 'r', '??' => 'R', '??' => 'r', '??' => 'R', '??' => 'r', '??' => 'R',
			'??' => 's', '??' => 'S', '??' => 's', '??' => 'S', '??' => 's', '??' => 'S', '???' => 's', '???' => 'S', '??' => 's', '??' => 'S', '??' => 's', '??' => 'S', '??' => 'SS',
			'??' => 't', '??' => 'T', '???' => 't', '???' => 'T', '??' => 't', '??' => 'T', '??' => 't', '??' => 'T', '??' => 't', '??' => 'T',
			'??' => 'u', '??' => 'U', '??' => 'u', '??' => 'U', '??' => 'u', '??' => 'U', '??' => 'u', '??' => 'U', '??' => 'u', '??' => 'U', '??' => 'u', '??' => 'U', '??' => 'u', '??' => 'U', '??' => 'u', '??' => 'U', '??' => 'u', '??' => 'U', '??' => 'u', '??' => 'U', '??' => 'ue', '??' => 'UE',
			'???' => 'w', '???' => 'W', '???' => 'w', '???' => 'W', '??' => 'w', '??' => 'W', '???' => 'w', '???' => 'W',
			'??' => 'y', '??' => 'Y', '???' => 'y', '???' => 'Y', '??' => 'y', '??' => 'Y', '??' => 'y', '??' => 'Y',
			'??' => 'z', '??' => 'Z', '??' => 'z', '??' => 'Z', '??' => 'z', '??' => 'Z',
			'??' => 'th', '??' => 'Th', '??' => 'u',
			'??' => 'a', '??' => 'a', '??' => 'b',
			'??' => 'b', '??' => 'v', '??' => 'v',
			'??' => 'g', '??' => 'g', '??' => 'd',
			'??' => 'd', '??' => 'e', '??' => 'e',
			'??' => 'e', '??' => 'e', '??' => 'zh',
			'??' => 'zh', '??' => 'z', '??' => 'z',
			'??' => 'i', '??' => 'i', '??' => 'j',
			'??' => 'j', '??' => 'k', '??' => 'k',
			'??' => 'l', '??' => 'l', '??' => 'm',
			'??' => 'm', '??' => 'n', '??' => 'n',
			'??' => 'o', '??' => 'o', '??' => 'p',
			'??' => 'p', '??' => 'r', '??' => 'r',
			'??' => 's', '??' => 's', '??' => 't',
			'??' => 't', '??' => 'u', '??' => 'u',
			'??' => 'f', '??' => 'f', '??' => 'h',
			'??' => 'h', '??' => 'c', '??' => 'c',
			'??' => 'ch', '??' => 'ch', '??' => 'sh',
			'??' => 'sh', '??' => 'sch', '??' => 'sch',
			'??' => '', '??' => '', '??' => 'y',
			'??' => 'y', '??' => '', '??' => '',
			'??' => 'e', '??' => 'e', '??' => 'ju',
			'??' => 'ju', '??' => 'ja', '??' => 'ja'
		);

		$name = str_replace(array_keys($transliterationTable), array_values($transliterationTable), $name);

		$separator = '-';
		// Convert all dashes/underscores into separator
		$flip = '_';

		$name = preg_replace('!['.preg_quote($flip).']+!u', $separator, $name);

		// Replace @ with the word 'at'
		$name = str_replace('@', $separator.'at'.$separator, $name);

		// Remove all characters that are not the separator, letters, numbers, or whitespace.
		$name = preg_replace('![^'.preg_quote($separator).'\pL\pN\s]+!u', '', mb_strtolower($name));

		// Replace all separator characters and whitespace by a single separator
		$name = preg_replace('!['.preg_quote($separator).'\s]+!u', $separator, $name);

		return trim($name, $separator);
		//return strtolower(preg_replace("/[^a-zA-Z0-9_]/", '', $tag));
	}

	/**
	 * Creator profile
	 *
	 * @return  object
	 */
	public function creator()
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'created_by');
	}

	/**
	 * Determine if record was modified
	 *
	 * @return  boolean  True if modified, false if not
	 */
	public function isUpdated()
	{
		if ($this->updated_at
		 && $this->updated_at != $this->created_at)
		{
			return true;
		}
		return false;
	}

	/**
	 * Editor user record
	 *
	 * @return  object
	 */
	public function updater()
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'updated_by');
	}

	/**
	 * Deleter user record
	 *
	 * @return  object
	 */
	public function trasher()
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'deleted_by');
	}

	/**
	 * Parent tag
	 *
	 * @return  object
	 */
	public function parent()
	{
		return $this->belongsTo(self::class, 'parent_id');
	}

	/**
	 * Get a list of aliases
	 *
	 * @return  object
	 */
	public function aliases()
	{
		return $this->hasMany(self::class, 'parent_id');
	}

	/**
	 * Get a comma-separated list of aliases
	 *
	 * @return  string
	 */
	public function getAliasStringAttribute()
	{
		$subs = $this->aliases->pluck('name')->toArray();

		return implode(', ', $subs);
	}

	/**
	 * Get a list of tagged objects
	 *
	 * @return  object
	 */
	public function tagged()
	{
		return $this->hasMany(Tagged::class, 'tag_id');
	}

	/**
	 * Delete the record and all associated data
	 *
	 * @param  array   $options
	 * @return boolean False if error, True on success
	 */
	public function delete(array $options = [])
	{
		foreach ($this->aliases as $row)
		{
			$row->delete();
		}

		foreach ($this->tagged as $row)
		{
			$row->delete();
		}

		$this->deleted_by = auth()->user() ? auth()->user()->id : 0;

		return parent::delete($options);
	}

	/**
	 * Retrieves one row loaded by a tag field
	 *
	 * @param   string  $tag  The tag to load by
	 * @return  mixed
	 **/
	public static function findByTag($tag)
	{
		$instance = new self;

		return self::query()
			->where('slug', '=', $instance->normalize($tag))
			->limit(1)
			->first();
	}

	/**
	 * Remove this tag from an object
	 *
	 * If $taggerid is provided, it will only remove the tags added to an object by
	 * that specific user
	 *
	 * @param   string   $scope     Object type (ex: resource, ticket)
	 * @param   integer  $scope_id  Object ID (e.g., resource ID, ticket ID)
	 * @param   integer  $tagger    User ID of person to filter tag by
	 * @return  boolean
	 */
	public function removeFrom($scope, $scope_id, $tagger=0)
	{
		// Check if the relationship exists
		$to = Tagged::findByScoped($scope, $scope_id, $this->id, $tagger);

		if (!$to->id)
		{
			return true;
		}

		// Attempt to delete the record
		if (!$to->delete())
		{
			$this->addError($to->getError());
			return false;
		}

		$this->tagged_count = $this->tagged()->count();

		return $this->save();
	}

	/**
	 * Add this tag to an object
	 *
	 * @param   string   $scope     Object type (ex: resource, ticket)
	 * @param   integer  $scope_id  Object ID (e.g., resource ID, ticket ID)
	 * @param   integer  $tagger    User ID of person adding tag
	 * @param   integer  $strength  Tag strength
	 * @return  boolean
	 */
	public function addTo($scope, $scope_id, $tagger = 0, $strength = 1)
	{
		// Check if the relationship already exists
		$to = Tagged::findByScoped($scope, $scope_id, $this->id, $tagger);

		if ($to->id)
		{
			return true;
		}

		// Set some data
		$to->taggable_type = (string) $scope;
		$to->taggable_id   = (int) $scope_id;
		$to->tag_id        = (int) $this->id;
		$to->strength      = (int) $strength;
		$to->created_by    = $tagger ? $tagger : auth()->user()->id;

		// Attempt to store the new record
		if (!$to->save())
		{
			$this->addError($to->getError());
			return false;
		}

		$this->tagged_count = $this->tagged()->count();

		return $this->save();
	}

	/**
	 * Move all data from this tag to another, including the tag itself
	 *
	 * @param   integer  $tag_id  ID of tag to merge with
	 * @return  boolean
	 */
	public function mergeWith($tag_id)
	{
		if (!$tag_id)
		{
			$this->addError(trans('tags::tags.error.Missing tag ID.'));
			return false;
		}

		// Get all the associations to this tag
		// Loop through the associations and link them to a different tag
		if (!Tagged::moveTo($this->id, $tag_id))
		{
			$this->addError(trans('tags::tags.error.Failed to move objects attached to tag.'));
			return false;
		}

		// Get all the substitutions to this tag
		// Loop through the records and link them to a different tag
		if (!self::moveTo($this->id, $tag_id))
		{
			$this->addError(trans('tags::tags.error.Failed to move aliases attached to tag.'));
			return false;
		}

		// Make the current tag an alias for the new tag
		$sub = new self;
		$sub->update([
			'name'      => $this->name,
			'parent_id' => $tag_id
		]);

		// Update new tag's counts
		$tag = self::find($tag_id);
		$tag->update([
			'tagged_count' => $tag->tagged()->count(),
			'alias_count'  => $tag->aliases()->count()
		]);

		// Destroy the old tag
		if (!$this->delete())
		{
			return false;
		}

		return true;
	}

	/**
	 * Copy associations from this tag to another
	 *
	 * @param   integer  $tag_id  ID of tag to copy associations to
	 * @return  boolean
	 */
	public function copyTo($tag_id)
	{
		if (!$tag_id)
		{
			$this->addError(trans('tags::tags.error.Missing tag ID.'));
			return false;
		}

		// Get all the associations to this tag
		// Loop through the associations and link them to a different tag
		if (!Tagged::copyTo($this->id, $tag_id))
		{
			$this->addError($to->getError());
			return false;
		}

		// Update new tag's counts
		$tag = self::find($tag_id);
		$tag->update([
			'tagged_count' => $tag->tagged()->count()
		]);

		return true;
	}

	/**
	 * Save tag substitutions
	 *
	 * @param   string   $tag_string
	 * @return  boolean
	 */
	public function saveAliases($tag_string='')
	{
		// Get the old list of substitutions
		$subs = array();
		foreach ($this->aliases as $sub)
		{
			$subs[$sub->slug] = $sub;
		}

		// Add the specified tags as aliases if not
		// already a substitute
		$names = trim($tag_string);
		$names = preg_split("/(,|;)/", $names);

		$tags = array();
		foreach ($names as $name)
		{
			$nrm = $this->normalize($name);

			$tags[] = $nrm;

			if (isset($subs[$nrm]))
			{
				continue; // Substitution already exists
			}

			$sub = new self;
			$sub->name      = trim($name);
			$sub->parent_id = $this->id;
			$sub->save();
		}

		// Run through the old list of aliases, finding any
		// not in the new list and delete them
		foreach ($subs as $key => $sub)
		{
			if (!in_array($key, $tags))
			{
				$sub->delete();
			}
		}

		// Get all possibly existing tags that are now aliases
		$ids = self::query()
			->whereIn('slug', $tags)
			->get();

		// Move associations on tag and delete tag
		foreach ($ids as $tag)
		{
			if ($tag->id != $this->id)
			{
				// Get all the associations to this tag
				// Loop through the associations and link them to a different tag
				Tagged::moveTo($tag->id, $this->id);

				// Get all the aliases to this tag
				// Loop through the records and link them to a different tag
				self::moveTo($tag->id, $this->id);

				// Delete the tag
				$tag->delete();
			}
		}

		$this->tagged_count = $this->tagged()->count();
		$this->alias_count  = $this->aliases()->count();

		return $this->save();
	}
}
