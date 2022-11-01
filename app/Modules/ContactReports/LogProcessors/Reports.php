<?php
namespace App\Modules\ContactReports\LogProcessors;

use App\Modules\History\Models\Log;
use App\Modules\Users\Models\User;
use Carbon\Carbon;

/**
 * Contact Reports log processor
 */
class Reports
{
	/**
	 * @param  Log $record
	 * @return Log
	 */
	public function __invoke($record)
	{
		if ($record->classname == 'ReportsController')
		{
			if ($record->transportmethod == 'POST')
			{
				$record->summary = 'Created Contact Report';

				$payload = $record->jsonPayload;

				if ($payload && isset($payload->datetimecontact))
				{
					$dt = Carbon::parse($payload->datetimecontact);
					$record->summary .= ' for contact on ' . $dt->format('F j, Y');
				}

				if ($payload && isset($payload->users))
				{
					$users = array();
					foreach ($payload->users as $userid)
					{
						$u = User::find($userid);
						if ($u)
						{
							$users[] = $u->name . ' (' . $u->username . ')';
						}
					}
					$record->summary .= ' with ' . implode(', ', $users);
				}
			}

			if ($record->transportmethod == 'DELETE')
			{
				$record->summary = 'Removed Contact Report';
			}
		}

		return $record;
	}
}
