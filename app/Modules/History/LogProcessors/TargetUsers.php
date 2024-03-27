<?php
namespace App\Modules\History\LogProcessors;

use App\Modules\History\Models\Log;

/**
 * Target User log processor
 */
class TargetUsers
{
	/**
	 * Try to determine target user from payload data
	 */
	public function __invoke(Log $record): Log
	{
		if ($record->targetuserid <= 0 && $record->payload)
		{
			$userid = $record->getExtraProperty('userid');

			if ($userid && is_numeric($userid))
			{
				$record->targetuserid = $userid;
				$record->saveQuietly();
			}
		}

		return $record;
	}
}
