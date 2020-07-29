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
		$value = $value ?: '[]';

		return new Repository(json_decode($value, true));
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
		return json_encode($value->all()); //toString('json');
	}
}
