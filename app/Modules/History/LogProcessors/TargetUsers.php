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
			$userid = $record->getExtraProperty('userid');

			if ($userid && is_numeric($userid))
			{
				$record->targetuserid = $userid;
				$record->save();
			}
		}

		return $record;
	}
}
