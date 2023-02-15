<?php

namespace App\Halcyon\Models\Casts;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class Params implements CastsAttributes
{
	/**
	 * Cast the given value.
	 *
	 * @param  Model  $model
	 * @param  string  $key
	 * @param  mixed  $value
	 * @param  array  $attributes
	 * @return Repository
	 */
	public function get($model, $key, $value, $attributes): Repository
	{
		$value = $value ? json_decode($value, true) : array();

		if (!is_array($value))
		{
			$value = array();
		}
		$value = array_filter(
			$value,
			function ($v, $k)
			{
				return $v !== null;
			},
			ARRAY_FILTER_USE_BOTH
		);

		return new Repository($value);
	}

	/**
	 * Prepare the given value for storage.
	 *
	 * @param  Model  $model
	 * @param  string  $key
	 * @param  array  $value
	 * @param  array  $attributes
	 * @return string
	 */
	public function set($model, $key, $value, $attributes): string
	{
		if ($value instanceof Repository)
		{
			$value = $value->all();
		}
		return json_encode($value);
	}
}
