<?php

namespace App\Halcyon\Traits;

trait Sluggable
{
	/**
	 * List of sluggable attributes
	 *    source => result
	 *
	 * @var  array<string,string>
	 */
	public $sluggable = [
		'title' => 'alias'
	];

	/**
	 * Boot trait
	 *
	 * @return  void
	 */
	public static function bootSluggable(): void
	{
		static::saving(function ($model)
		{
			foreach ($model->sluggable as $source => $dest)
			{
				$model->{$dest} = $model->generateSlug($model->{$source});
			}
		});
	}

	/**
	 * Generate a slug
	 *
	 * @param   string  $str
	 * @return  string
	 */
	public function generateSlug($str): string
	{
		return strtolower(preg_replace(
			['/[^\w\s]+/', '/\s+/'],
			['', '-'],
			$str
		));
	}
}
