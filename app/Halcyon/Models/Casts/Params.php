<?php

namespace App\Halcyon\Models\Casts;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class Params implements CastsAttributes
{
	/**
	 * Cast the given value.
	 *
	 * @param  \Illuminate\Database\Eloquent\Model  $model
	 * @param  string  $key
	 * @param  mixed  $value
	 * @param  array  $attributes
	 * @return array
	 */
	public function get($model, $key, $value, $attributes)
	{
		$value = $value ? json_decode($value, true) : array();

		if (!is_array($value))
		{
			$value = array();
		}
		$value = array_filter($value, function($v, $k)
			{
				return $v !== null;
			}, ARRAY_FILTER_USE_BOTH);

		return new Repository($value);
	}

	/**
	 * Prepare the given value for storage.
	 *
	 * @param  \Illuminate\Database\Eloquent\Model  $model
	 * @param  string  $key
	 * @param  array  $value
	 * @param  array  $attributes
	 * @return string
	 */
	public function set($model, $key, $value, $attributes)
	{
		if ($value instanceof Repository)
		{
			$value = $value->all();
		}
		return json_encode($value); //toString('json');
	}
}
