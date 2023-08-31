<?php
namespace App\Modules\ContactReports\LogProcessors;

use App\Modules\ContactReports\Models\Report;
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

				if (auth()->user() && auth()->user()->can('manage contactreports'))
				{
					$record->summary = 'Created <a href="' . route('site.contactreports.index') . '">Contact Report</a>';
				}

				$datetimecontact = $record->getExtraProperty('datetimecontact');
				$entry = null;

				if ($datetimecontact)
				{
					$query = Report::query()
						->where('datetimecontact', '=', $datetimecontact)
						->where('userid', '=', $record->userid);

					if ($report = $record->getExtraProperty('report'))
					{
						$query->where('report', '=', $report);
					}

					$entry = $query->first();

					if ($entry && auth()->user() && auth()->user()->can('manage contactreports'))
					{
						$record->summary = 'Created <a href="' . route('site.contactreports.index', ['id' => $entry->id]) . '">Contact Report #' . $entry->id . '</a>';
					}
				//}

				//if ($datetimecontact = $record->getExtraProperty('datetimecontact'))
				//{
					$dt = Carbon::parse($datetimecontact);
					$record->summary .= ' for contact on <time datetime="' . $dt->toDateTimeLocalString() . '">' . $dt->format('F j, Y') . '</time>';
				}

				if ($userids = $record->getExtraProperty('users'))
				{
					$users = array();
					foreach ($userids as $userid)
					{
						$u = User::find($userid);
						if ($u)
						{
							$users[] = $u->name . ' (' . $u->username . ')';
						}
					}
					if (count($users) > 0)
					{
						$record->summary .= ' with ' . implode(', ', $users);
					}
				}
			}

			if ($record->transportmethod == 'DELETE')
			{
				$record->summary = 'Removed Contact Report';

				if (auth()->user() && auth()->user()->can('manage contactreports'))
				{
					$record->summary = 'Removed <a href="' . route('site.contactreports.index') . '">Contact Report</a>';
				}
			}
		}

		return $record;
	}
}
