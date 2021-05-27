<?php

namespace App\Modules\Groups\Events;

use App\Modules\Groups\Models\UnixGroupMember;

class UnixGroupMemberCreating
{
	/**
	 * @var UnixGroupMember
	 */
	public $member;

	/**
	 * @var bool
	 */
	public $restricted = true;

	/**
	 * Constructor
	 *
	 * @param UnixGroupMember $member
	 * @return void
	 */
	public function __construct(UnixGroupMember $member)
	{
		$this->member = $member;
	}
}
