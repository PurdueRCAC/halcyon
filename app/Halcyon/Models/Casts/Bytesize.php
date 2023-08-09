<?php

namespace App\Halcyon\Models\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsInboundAttributes;

class Bytesize implements CastsInboundAttributes
{
	/**
	 * @inheritdoc
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
