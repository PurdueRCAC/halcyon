<?php
namespace App\Modules\History\LogProcessors;

use App\Modules\History\Models\Log;

/**
 * Emails log processor
 */
class Emails
{
	/**
	 * @param  Log $record
	 * @return Log
	 */
	public function __invoke($record)
	{
		if ($record->app == 'email')
		{
			$record->summary = $record->payload;
		}

		return $record;
	}
}
