<?php

namespace App\Modules\Courses\Events;

use App\Modules\Courses\Models\Member;

class MemberDeleted
{
	/**
	 * @var Member
	 */
	public $member;

	/**
	 * Constructor
	 *
	 * @param  Member
	 * @return void
	 */
	public function __construct(Member $member)
	{
		$this->member = $member;
	}
}
