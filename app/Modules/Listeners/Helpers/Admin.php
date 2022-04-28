<?php

namespace App\Modules\Listeners\Helpers;

use Illuminate\Support\Facades\DB;
use App\Halcyon\Html\Builder\Select;
use App\Modules\Listeners\Models\Listener;

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
		$options = Listener::query()
			->select([DB::raw('DISTINCT(folder) AS value'), 'folder AS text'])
			->where('type', '=', 'listener')
			->orderBy('folder', 'asc')
			->get();

		return $options;
	}
}
