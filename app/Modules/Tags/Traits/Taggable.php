<?php

namespace App\Modules\Tags\Traits;

use Illuminate\Database\Eloquent\Builder;
use App\Modules\Tags\Models\Tag;
use Carbon\Carbon;

trait Taggable
{
	/**
	 * @var string
	 */
	protected static $tagsModel = Tag::class;

	/**
	 * Get the tags model class name
	 *
	 * @return string
	 */
	public static function getTagsModel()
	{
		return static::$tagsModel;
	}

	/**
	 * Set the tags model class name
	 *
	 * @param string $model
	 * @return void
	 */
	public static function setTagsModel($model)
	{
		static::$tagsModel = $model;
	}

	/**
	 * Append ot a query where a tag is of type
	 *
	 * @param Builder $query
	 * @param array|string $tags
	 * @param string $type
	 * @return object
	 */
	public function scopeWhereTag(Builder $query, $tags, $type = 'slug')
	{
		if (is_string($tags) === true)
		{
			$tags = [$tags];
		}

		foreach ($tags as $tag)
		{
			$query->whereHas('tags', function (Builder $query) use ($type, $tag)
			{
				$query->where($type, $tag);
			});
		}

		return $query;
	}

	/**
	 * Append ot a query where object has tag
	 *
	 * @param Builder $query
	 * @param array|string $tags
	 * @param string $type
	 * @return object Builder
	 */
	public function scopeWithTag(Builder $query, $tags, $type = 'slug')
	{
		if (is_string($tags) === true)
		{
			$tags = [$tags];
		}

		return $query->whereHas('tags', function (Builder $query) use ($type, $tags)
		{
			$query->whereIn($type, $tags);
		});
	}

	/**
	 * Append ot a query where a tag is of type
	 *
	 * @param Builder $query
	 * @param string $domain
	 * @return object Builder
	 */
	public function scopeWhereDomain(Builder $query, $domain)
	{
		return $query->where('domain', $domain);
	}

	/**
	 * Get tags on object
	 *
	 * @return object
	 */
	public function tags()
	{
		return $this->morphToMany(static::$tagsModel, 'taggable', 'tags_tagged', 'taggable_id', 'tag_id');
	}

	/**
	 * Create an instance of the tag model
	 *
	 * @return Tag
	 */
	public static function createTagsModel()
	{
		return new static::$tagsModel;
	}

	/**
	 * Get a list of all tags for a domain
	 *
	 * @return array|Collection
	 */
	public static function allTags()
	{
		$instance = new static;

		return $instance->createTagsModel()->whereDomain($instance->getEntityClassName());
	}

	/**
	 * Set the list of tags
	 *
	 * @param  array  $tags
	 * @param  string $type
	 * @return bool
	 */
	public function setTags($tags, $type = 'slug')
	{
		if (empty($tags))
		{
			$tags = [];
		}

		// Get the current entity tags
		$entityTags = $this->tags->pluck($type)->all();

		// Prepare the tags to be added and removed
		$tagsToAdd = array_diff($tags, $entityTags);
		$tagsToDel = array_diff($entityTags, $tags);

		// Detach the tags
		if (count($tagsToDel) > 0)
		{
			$this->untag($tagsToDel);
		}

		// Attach the tags
		if (count($tagsToAdd) > 0)
		{
			$this->tag($tagsToAdd);
		}

		return true;
	}

	/**
	 * Add a list of tags
	 *
	 * @param  array $tags
	 * @return bool
	 */
	public function tag($tags)
	{
		foreach ($tags as $tag)
		{
			$this->addTag($tag);
		}

		return true;
	}

	/**
	 * Add a tag
	 *
	 * @param  string $name
	 * @return void
	 */
	public function addTag($name)
	{
		$model = $this->createTagsModel();

		$tag = $model
			//->where('domain', $this->getEntityClassName())
			->withTrashed()
			->where('slug', $model->normalize($name))
			->first();

		if ($tag === null)
		{
			$tag = new Tag([
				'domain' => $this->getEntityClassName(),
				'slug' => $model->normalize($name),
				'name' => $name,
				'created_by' => auth()->user() ? auth()->user()->id : 0
			]);
		}

		if ($tag->trashed())
		{
			$tag->restore();
		}

		if ($tag->exists === false)
		{
			$tag->save();
		}

		if ($this->tags->contains($tag->id) === false)
		{
			$this->tags()->attach($tag, [
				'created_at' => Carbon::now(),
				'created_by' => auth()->user() ? auth()->user()->id : 0
			]);
			$this->tags->push($tag);
		}
	}

	/**
	 * Remove a list of tags
	 *
	 * @param  array|null $tags
	 * @return bool
	 */
	public function untag($tags = null)
	{
		$tags = $tags ?: $this->tags->pluck('name')->all();

		foreach ($tags as $tag)
		{
			$this->removeTag($tag);
		}

		return true;
	}

	/**
	 * Remove a tag
	 *
	 * @param  string $name
	 * @return void
	 */
	public function removeTag($name)
	{
		$model = $this->createTagsModel();

		$tag = $model
			//->where('domain', $this->getEntityClassName())
			->where('slug', $model->normalize($name))
			->first();

		if ($tag)
		{
			$this->tags()->detach($tag);
		}
	}

	/**
	 * Check if the given string is a tag
	 * 
	 * @param  string $name
	 * @return bool|Tag
	 */
	public function isTag($name)
	{
		$model = $this->createTagsModel();

		$tag = $model
			//->where('domain', $this->getEntityClassName())
			->where('slug', $model->normalize($name))
			->first();

		if ($tag)
		{
			return $tag;
		}

		return false;
	}

	/**
	 * Check if the model has the specified tag
	 *
	 * @param  string $name
	 * @return bool
	 */
	public function hasTag($name)
	{
		$model = $this->createTagsModel();

		$tags = $this->tags->pluck('slug')->toArray();
		$slug = $model->normalize($name);

		return in_array($slug, $tags);
	}

	/**
	 * Get class name
	 *
	 * @return string
	 */
	protected function getEntityClassName()
	{
		if (isset(static::$entityNamespace))
		{
			return static::$entityNamespace;
		}

		return $this->tags()->getMorphClass();
	}
}
