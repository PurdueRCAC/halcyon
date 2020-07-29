<?php

namespace App\Modules\Groups\Events;

use App\Modules\Groups\Models\Member;

class MemberDeleted
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
	public function __construct(Member $member)
	{
		$this->member = $member;
	}
}
