<?php

namespace App\Modules\Listeners\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use App\Halcyon\Html\Builder\Select;
use App\Modules\Listeners\Models\Listener;

/**
 * Select options helper.
 */
abstract class Admin
{
	/**
	 * Returns an array of standard published state filter options.
	 *
	 * @return  array<int,\stdClass>
	 */
	public static function stateOptions(): array
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
	 * @return  Collection
	 */
	public static function folderOptions(): Collection
	{
		$options = Listener::query()
			->select([DB::raw('DISTINCT(folder) AS value'), 'folder AS text'])
			->where('type', '=', 'listener')
			->orderBy('folder', 'asc')
			->get();

		return $options;
	}
}
