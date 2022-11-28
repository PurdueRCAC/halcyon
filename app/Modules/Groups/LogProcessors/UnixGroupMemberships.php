<?php
namespace App\Modules\Groups\LogProcessors;

use App\Modules\Groups\Models\UnixGroupMember;
use App\Modules\Groups\Models\UnixGroup;
use App\Modules\History\Models\Log;

/**
 * Unix Group membership log processor
 */
class UnixGroupMemberships
{
	/**
	 * @param  Log $record
	 * @return Log
	 */
	public function __invoke($record)
	{
		if ($record->classname == 'UnixGroupMembersController'
		 || $record->classname == 'unixgroupmember')
		{
			if ($record->targetobjectid <= 0 && $record->payload)
			{
				if ($unixgroupid = $record->getExtraProperty('unixgroupid'))
				{
					$record->targetobjectid = $unixgroupid;
					$record->save();
				}
			}

			// Some fiddling here. Delete events are only to a URL /api/unixgroups/members/####
			// So we need to parse out the record's ID to look up its unix group and user.
			if ($record->targetobjectid <= 0 && $record->classmethod == 'delete')
			{
				$parts = explode('/', $record->uri);
				$mid = end($parts);
				$mid = intval($mid);

				if ($mid)
				{
					$m = UnixGroupMember::query()->withTrashed()->where('id', '=', $mid)->first();

					$record->targetobjectid = $m ? $m->unixgroupid : $record->targetobjectid;
					$record->targetuserid = $m ? $m->userid : $record->targetuserid;
				}
			}

			$g = UnixGroup::find($record->targetobjectid);
			$groupname = '#' . $record->targetobjectid;
			if ($g)
			{
				$groupname = $g->longname;
			}

			if ($record->classmethod == 'create')
			{
				$record->summary = 'Added to Unix group <strong>' . $groupname . '</strong>';
			}

			if ($record->classmethod == 'delete')
			{
				$record->summary = 'Removed from Unix group <strong>' . $groupname . '</strong>';
			}

			if ($record->user)
			{
				$record->summary .= ' by ' . $record->user->name;
			}
		}

		return $record;
	}
}
