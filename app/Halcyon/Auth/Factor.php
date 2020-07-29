<?php
/**
 * @package    framework
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Halcyon\Auth;

use App\Halcyon\Database\Relational;

/**
 * Factors database model
 *
 * @uses \App\Halcyon\Database\Relational
 */
class Factor extends Relational
{
	/**
	 * Gets one result or fails by domain and user_id
	 *
	 * @param   string  $domain  The domain of interest
	 * @return  mixed   static|bool
	 */
	public static function currentOrFailByDomain($domain)
	{
		$factor = static::query()
			->where('user_id', '=', auth()->user()->id)
			->where('domain', '=', $domain)
			->first();

		return ($factor->id) ? $factor : false;
	}
}
