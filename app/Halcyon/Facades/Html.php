<?php
/**
 * @package    framework
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

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
