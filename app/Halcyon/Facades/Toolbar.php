<?php

namespace App\Halcyon\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Toolbar facade
 */
class Toolbar extends Facade
{
	/**
	 * Get the registered name.
	 *
	 * @return  string
	 */
	protected static function getFacadeAccessor()
	{
		return 'toolbar';
	}
}
