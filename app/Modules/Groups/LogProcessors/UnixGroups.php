<?php
namespace App\Modules\Groups\LogProcessors;

use App\Modules\Groups\Models\UnixGroup;
use App\Modules\History\Models\Log;

/**
 * Unix Group log processor
 */
class UnixGroups
{
	/**
	 * @param  Log $record
	 * @return Log
	 */
	public function __invoke($record)
	{
		if ($record->classname == 'UnixGroupsController'
		 || $record->classname == 'unixgroup')
		{
			$payload = $record->jsonPayload;

			if ($record->targetobjectid <= 0 && $payload)
			{
				if (isset($record->jsonPayload->unixgroupid) && $record->jsonPayload->unixgroupid)
				{
					$record->targetobjectid = $record->jsonPayload->unixgroupid;
					$record->save();
				}
			}

			if ($payload && isset($payload->longname))
			{
				$groupname = $payload->longname;
			}
			else
			{
				$g = UnixGroup::find($record->targetobjectid);

				$groupname = '#' . $record->targetobjectid;
				if ($g)
				{
					$groupname = $g->longname;
				}
			}

			if ($record->classmethod == 'create')
			{
				$record->summary = 'Created Unix group ' . $groupname;
			}

			if ($record->classmethod == 'update')
			{
				$record->summary = 'Updated Unix group ' . $groupname;
			}

			if ($record->classmethod == 'delete')
			{
				$record->summary = 'Deleted Unix group ' . $groupname;
			}

			if ($record->user)
			{
				$record->summary .= ' by ' . $record->user->name;
			}
		}

		return $record;
	}
}
