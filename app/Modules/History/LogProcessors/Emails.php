<?php
namespace App\Modules\History\LogProcessors;

use App\Modules\History\Models\Log;

/**
 * Emails log processor
 */
class Emails
{
	/**
	 * Set the log summary to the email's subject,
	 * stored in the payload, if logging an email
	 */
	public function __invoke(Log $record): Log
	{
		if ($record->app == 'email')
		{
			$record->summary = $record->payload;
		}

		return $record;
	}
}
