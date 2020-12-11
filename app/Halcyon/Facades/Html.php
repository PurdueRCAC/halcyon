<?php

namespace App\Halcyon\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * HTML Helper facade
 *
 * @codeCoverageIgnore
 */
class Html extends Facade
{
	/**
	 * Get the registered name.
	 *
	 * @return  string
	 */
	protected static function getFacadeAccessor()
	{
		return 'html.builder';
	}
}
