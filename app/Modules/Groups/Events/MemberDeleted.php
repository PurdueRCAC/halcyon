<?php

namespace App\Modules\Groups\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use App\Modules\Groups\Models\Member;

class MemberDeleted implements ShouldBroadcast
{
	use SerializesModels;

	/**
	 * @var Member
	 */
	public $member;

	/**
	 * Constructor
	 *
	 * @param  Member  $member
	 * @return void
	 */
	public function __construct(Member $member)
	{
		$this->member = $member;
	}

	/**
	 * Get the channels the event should broadcast on.
	 *
	 * @return Channel|array
	 */
	public function broadcastOn()
	{
		return new PrivateChannel('users.' . $this->member->userid);
	}
}
