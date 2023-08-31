<?php
namespace App\Modules\ContactReports\LogProcessors;

use App\Modules\History\Models\Log;

/**
 * Contact Reports log processor
 */
class Comments
{
	/**
	 * @param  Log $record
	 * @return Log
	 */
	public function __invoke($record)
	{
		if ($record->classname == 'CommentsController')
		{
			if ($record->transportmethod == 'POST')
			{
				$record->summary = 'Created Contact Report comment';

				if (auth()->user() && auth()->user()->can('manage contactreports'))
				{
					$record->summary = 'Created <a href="' . route('site.contactreports.index') . '">Contact Report</a> comment';
				}

				$contactreportid = $record->getExtraProperty('contactreportid');
				$entry = null;

				if ($contactreportid && auth()->user() && auth()->user()->can('manage contactreports'))
				{
					$record->summary = 'Created <a href="' . route('site.contactreports.index', ['id' => $contactreportid]) . '">Contact Report #' . $contactreportid . ' comment</a>';
				}
			}
			elseif ($record->transportmethod == 'PUT')
			{
				$record->summary = 'Updated Contact Report comment';

				if (auth()->user() && auth()->user()->can('manage contactreports'))
				{
					$record->summary = 'Updated <a href="' . route('site.contactreports.index') . '">Contact Report</a> comment';
				}

				$contactreportid = $record->getExtraProperty('contactreportid');
				$entry = null;

				if ($contactreportid && auth()->user() && auth()->user()->can('manage contactreports'))
				{
					$record->summary = 'Updated <a href="' . route('site.contactreports.index', ['id' => $contactreportid]) . '">Contact Report #' . $contactreportid . ' comment</a>';
				}
			}
			elseif ($record->transportmethod == 'DELETE')
			{
				$record->summary = 'Removed Contact Report comment';

				if (auth()->user() && auth()->user()->can('manage contactreports'))
				{
					$record->summary = 'Removed <a href="' . route('site.contactreports.index') . '">Contact Report</a> comment';

					if ($contactreportid = $record->getExtraProperty('contactreportid'))
					{
						$record->summary = 'Removed <a href="' . route('site.contactreports.index', ['id' => $contactreportid]) . '">Contact Report #' . $contactreportid . ' comment</a>';
					}
				}
			}
		}

		return $record;
	}
}
