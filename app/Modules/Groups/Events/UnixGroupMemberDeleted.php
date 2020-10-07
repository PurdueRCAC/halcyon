<?php

namespace App\Modules\Groups\Events;

use App\Modules\Groups\Models\UnixGroupMember;

class UnixGroupMemberDeleted
{
	/**
	 * @var Member
	 */
	public $member;

	/**
	 * Constructor
	 *
	 * @param  Group $group
	 * @return void
	 */
	public function __construct(UnixGroupMember $member)
	{
		$this->member = $member;
	}
}
