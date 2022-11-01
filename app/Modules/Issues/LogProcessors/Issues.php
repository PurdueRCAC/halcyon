<?php
namespace App\Modules\Issues\LogProcessors;

use App\Modules\History\Models\Log;
use App\Modules\Resources\Models\Asset;

/**
 * Issues log processor
 */
class Issues
{
	/**
	 * @param  Log $record
	 * @return Log
	 */
	public function __invoke($record)
	{
		if ($record->classname == 'IssuesController')
		{
			if ($record->transportmethod == 'POST')
			{
				$record->summary = 'Created <a href="' . route('site.issues.index') . '">Issue</a>';

				$payload = $record->jsonPayload;
				if ($payload && isset($payload->resources))
				{
					$resources = array();
					foreach ($payload->resources as $resourceid)
					{
						$u = Asset::find($resourceid);
						if ($u)
						{
							$resources[] = $u->name;
						}
					}
					$record->summary .= ' for resources ' . implode(', ', $resources);
				}
			}
			elseif ($record->transportmethod == 'DELETE')
			{
				$record->summary = 'Removed Issue';
			}
		}

		return $record;
	}
}
