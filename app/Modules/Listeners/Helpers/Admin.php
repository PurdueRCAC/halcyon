<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Modules\Listeners\Helpers;

use App\Halcyon\Html\Builder\Select;
use Illuminate\Support\Facades\DB;

/**
 * Modules component helper.
 */
abstract class Admin
{
	/**
	 * Returns an array of standard published state filter options.
	 *
	 * @return  array
	 */
	public static function stateOptions()
	{
		// Build the active state filter options.
		$options = array();
		$options[] = Select::option('1', 'global.published');
		$options[] = Select::option('0', 'global.unpublished');

		return $options;
	}

	/**
	 * Returns an array of standard published state filter options.
	 *
	 * @return  array
	 */
	public static function folderOptions()
	{
		$options = DB::table('extensions')
			->select([DB::raw('DISTINCT(folder) AS value'), 'folder AS text'])
			->where('type', '=', 'listener')
			->orderBy('folder', 'asc')
			->get();

		return $options;
	}
}
