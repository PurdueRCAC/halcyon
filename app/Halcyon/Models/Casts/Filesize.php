<?php

namespace App\Halcyon\Model\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsInboundAttributes;

class Filesize implements CastsInboundAttributes
{
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
		if (!preg_match('/^[0-9]+[BKMGTP]$/', $value))
		{
			$value = 0;
		}

		return $value;
	}
}
