<?php

namespace App\Modules\Courses\Events;

use App\Modules\Courses\Models\Member;

class MemberCreated
{
	/**
	 * @var Member
	 */
	public $member;

	/**
	 * Constructor
	 *
	 * @param Member $member
	 * @return void
	 */
	public function __construct(Member $member)
	{
		$this->member = $member;
	}
}
