<?php
namespace App\Modules\Users\LogProcessors;

use App\Modules\History\Models\Log;

/**
 * User roles log processor
 */
class Roles
{
	/**
	 * @param  Log $record
	 * @return Log
	 */
	public function __invoke($record)
	{
		if ($record->classname == 'RolesController')
		{
			if ($record->transportmethod == 'POST')
			{
				$record->summary = 'Created User Role';

				if (auth()->user() && auth()->user()->can('manage users.roles'))
				{
					$record->summary = 'Created <a href="' . route('admin.users.roles') . '">User Role</a>';
				}
			}
			elseif ($record->transportmethod == 'PUT')
			{
				$record->summary = 'Updated User Role';

				if (auth()->user() && auth()->user()->can('manage contactreports'))
				{
					$record->summary = 'Updated <a href="' . route('site.contactreports.index') . '">Contact Report</a> comment';
				}
			}
			elseif ($record->transportmethod == 'DELETE')
			{
				$record->summary = 'Removed Contact Report comment';

				if (auth()->user() && auth()->user()->can('manage contactreports'))
				{
					$record->summary = 'Removed <a href="' . route('site.contactreports.index') . '">Contact Report</a> comment';
				}
			}
		}

		return $record;
	}
}
