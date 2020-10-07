<?php

namespace App\Modules\Groups\Events;

use App\Modules\Groups\Models\UnixGroupMember;

class UnixGroupMemberCreated
{
	/**
	 * @var UnixGroupMember
	 */
	public $member;

	/**
	 * Constructor
	 *
	 * @param Member $member
	 * @return void
	 */
	public function __construct(UnixGroupMember $member)
	{
		$this->member = $member;
	}
}
