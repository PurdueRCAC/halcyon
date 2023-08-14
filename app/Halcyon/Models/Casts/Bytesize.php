<?php

namespace App\Halcyon\Models\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsInboundAttributes;

class Bytesize implements CastsInboundAttributes
{
	/**
	 * Transform the attribute to its underlying model values.
	 *
	 * @param  \Illuminate\Database\Eloquent\Model  $model
	 * @param  string  $key
	 * @param  mixed  $value
	 * @param  array<string,mixed>  $attributes
	 * @return mixed
	 */
	public function set($model, $key, $value, $attributes)
	{
		if (!preg_match('/^[0-9]+[BKMGTP]$/', $value))
		{
			$value = 0;
		}

		return $value;
	}
}
