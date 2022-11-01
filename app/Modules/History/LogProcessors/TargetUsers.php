<?php
namespace App\Modules\History\LogProcessors;

use App\Modules\History\Models\Log;

/**
 * Target User log processor
 */
class TargetUsers
{
	/**
	 * @param  Log $record
	 * @return Log
	 */
	public function __invoke($record)
	{
		if ($record->targetuserid <= 0 && $record->payload)
		{
			if (isset($record->jsonPayload->userid)
			 && $record->jsonPayload->userid
			 && is_numeric($record->jsonPayload->userid))
			{
				$record->targetuserid = $record->jsonPayload->userid;
				$record->save();
			}
		}

		return $record;
	}
}
