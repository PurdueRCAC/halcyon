<?php
/**
 * @package    framework
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

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
