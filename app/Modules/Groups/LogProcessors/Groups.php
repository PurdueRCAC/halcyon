<?php
namespace App\Modules\Groups\LogProcessors;

use App\Modules\History\Models\Log;

/**
 * Groups log processor
 */
class Groups
{
	/**
	 * @param  Log $record
	 * @return Log
	 */
	public function __invoke($record)
	{
		if (!in_array($record->classname, ['GroupsController']) || $record->summary)
		{
			return $record;
		}

		if ($record->classmethod == 'create')
		{
			$payload = $record->jsonPayload;

			$group = '';
			if ($payload && isset($payload->name))
			{
				$group = $payload->name;
			}

			$record->summary = 'Created group ' . $group;
		}

		if ($record->classmethod == 'update')
		{
			$record->summary = 'Updated group ' . $group;
		}

		if ($record->classmethod == 'delete')
		{
			$record->summary = 'Removed group ' . $group;
	}

		return $record;
	}
}
