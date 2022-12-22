<?php
namespace App\Modules\Groups\LogProcessors;

use App\Modules\Groups\Models\Group;
use App\Modules\Groups\Models\UnixGroupMember;
use App\Modules\Groups\Models\UnixGroup;
use App\Modules\History\Models\Log;

/**
 * Group membership log processor
 */
class GroupMemberships
{
	/**
	 * @param  Log $record
	 * @return Log
	 */
	public function __invoke($record)
	{
		if (!in_array($record->classname, ['groupowner', 'groupviewer', 'MembersController']) || $record->summary)
		{
			return $record;
		}

		$group = '#' . $record->groupid;

		if ($record->groupid)
		{
			$g = Group::query()
				->withTrashed()
				->where('id', '=', $record->groupid)
				->first();

			$group = $g ? $g->name : $group;

			$route = route('site.users.account.section.show.subsection', [
				'section' => 'groups',
				'id' => $record->groupid,
				'subsection' => 'members',
			]);

			$group = '<a href="' . $route . '">' . $group . '</a>';
		}

		switch ($record->classname)
		{
			case 'groupowner':
				if ($record->classmethod == 'create')
				{
					$record->summary = 'Promoted to manager in group  ' . $group;
				}

				if ($record->classmethod == 'delete')
				{
					$record->summary = 'Demoted as manager in group  ' . $group;
				}
			break;

			case 'groupviewer':
				if ($record->classmethod == 'create')
				{
					$record->summary = 'Promoted to usage viewer in group  ' . $group;
				}

				if ($record->classmethod == 'delete')
				{
					$record->summary = 'Demoted as usage viewer in group  ' . $group;
				}
			break;

			case 'MembersController':
				if ($record->classmethod == 'update')
				{
					$record->summary = 'Membership status changed in group ' . $group;

					if ($membertype = $record->getExtraProperty('membertype'))
					{
						if ($membertype == 1)
						{
							$record->summary = 'Status set to member ' . $group;
						}
						if ($membertype == 2)
						{
							$record->summary = 'Promoted to manager ' . $group;
						}
						if ($membertype == 3)
						{
							$record->summary = 'Promoted to usage viewer in group ' . $group;
						}
					}
				}

				if ($record->classmethod == 'create')
				{
					$record->summary = 'Added to group ' . $group;

					if ($membertype = $record->getExtraProperty('membertype'))
					{
						if ($membertype == 2)
						{
							$record->summary .= ' as a manager';
						}
						if ($membertype == 1)
						{
							$record->summary .= ' as a member';
						}
						if ($membertype == 4)
						{
							$record->summary = 'Submitted request to join group ' . $group;
							$record->targetuserid = 0;
						}
					}
				}

				if ($record->classmethod == 'delete')
				{
					$user = trans('global.unknown');
					$parts = explode('/', $record->uri);
					$id = end($parts);

					$membership = Member::query()
						->withTrashed()
						->where('id', '=', $id)
						->first();

					if ($membership)
					{
						$record->targetuserid = $membership->userid;
						$user = $membership->user ? $membership->user->username : $user;
					}

					$record->summary = 'Removed ' . $user . ' from group ' . $group;
				}
			break;

			if ($record->user)
			{
				$record->summary .= ' by ' . $record->user->name;
			}
		}

		return $record;
	}
}
