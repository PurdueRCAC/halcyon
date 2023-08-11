<?php

namespace App\Modules\Tags\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
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
	public static function getTagsModel(): string
	{
		return static::$tagsModel;
	}

	/**
	 * Set the tags model class name
	 *
	 * @param string $model
	 * @return void
	 */
	public static function setTagsModel($model): void
	{
		static::$tagsModel = $model;
	}

	/**
	 * Append ot a query where a tag is of type
	 *
	 * @param Builder $query
	 * @param array<int,string>|string $tags
	 * @param string $type
	 * @return Builder
	 */
	public function scopeWhereTag(Builder $query, $tags, $type = 'slug'): Builder
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
	 * @param array<int,string>|string $tags
	 * @param string $type
	 * @return Builder
	 */
	public function scopeWithTag(Builder $query, $tags, $type = 'slug'): Builder
	{
		if (is_string($tags))
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
	 * @return Builder
	 */
	public function scopeWhereDomain(Builder $query, $domain): Builder
	{
		return $query->where('domain', $domain);
	}

	/**
	 * Get tags on object
	 *
	 * @return MorphToMany
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
	public static function createTagsModel(): Tag
	{
		return new static::$tagsModel;
	}

	/**
	 * Get a list of all tags for a domain
	 *
	 * @return Builder
	 */
	public static function allTags()
	{
		$instance = new static;

		return $instance->createTagsModel()->whereDomain($instance->getEntityClassName());
	}

	/**
	 * Set the list of tags
	 *
	 * @param  array<int,string>  $tags
	 * @param  string $type
	 * @return bool
	 */
	public function setTags($tags, $type = 'slug'): bool
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
	 * @param  array<int,string> $tags
	 * @return bool
	 */
	public function tag($tags): bool
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
	public function addTag(string $name): void
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
	 * @param  array<int,string>|null $tags
	 * @return bool
	 */
	public function untag($tags = null): bool
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
	public function removeTag(string $name): void
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
	public function hasTag(string $name): bool
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
	protected function getEntityClassName(): string
	{
		if (isset(static::$entityNamespace))
		{
			return static::$entityNamespace;
		}

		return $this->tags()->getMorphClass();
	}
}
