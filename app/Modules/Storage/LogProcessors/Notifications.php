<?php
namespace App\Modules\Storage\LogProcessors;

use App\Modules\History\Models\Log;
use App\Modules\Storage\Models\Directory;

/**
 * Storage notifications log processor
 */
class Notifications
{
	/**
	 * @param  Log $record
	 * @return Log
	 */
	public function __invoke($record)
	{
		if ($record->classname == 'NotificationsController')
		{
			if ($record->transportmethod == 'POST')
			{
				$route = route('site.users.account.section', [
					'section' => 'qoutas',
				]);

				$record->summary = 'Created <a href="' . $route . '">Storage Alert</a>';

				$payload = $record->jsonPayload;
				if ($payload && isset($payload->storagedirid))
				{
					$d = Directory::find($payload->storagedirid);

					$record->summary .= ' for directory ' . $d->fullPath;
				}
			}
			elseif ($record->transportmethod == 'DELETE')
			{
				$record->summary = 'Removed storage alert';
			}
		}

		return $record;
	}
}
