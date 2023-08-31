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
				if ($unixgroupid = $record->getExtraProperty('unixgroupid'))
				{
					$record->targetobjectid = $unixgroupid;
					$record->save();
				}
			}

			$groupname = '';
			if ($longname = $record->getExtraProperty('longname'))
			{
				$groupname = $longname;
			}
			elseif ($record->targetobjectid > 0)
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

			/*if ($record->user)
			{
				if (auth()->user() && auth()->user()->can('manage users'))
				{
					$record->summary .= ' by <a href="' . route('site.users.account', ['u' => $record->user->id]) . '">' . $record->user->name . '</a>';
				}
				else
				{
					$record->summary .= ' by ' . $record->user->name;
				}
			}*/
		}

		return $record;
	}
}
