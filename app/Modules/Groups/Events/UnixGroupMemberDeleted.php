<?php

namespace App\Modules\Groups\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use App\Modules\Groups\Models\UnixGroupMember;

class UnixGroupMemberDeleted implements ShouldBroadcast
{
	use SerializesModels;

	/**
	 * @var UnixGroupMember
	 */
	public $member;

	/**
	 * Constructor
	 *
	 * @param  UnixGroupMember $member
	 * @return void
	 */
	public function __construct(UnixGroupMember $member)
	{
		$this->member = $member;
	}

	/**
	 * @inheritdoc
	 */
	public function broadcastOn()
	{
		return new PrivateChannel('users.' . $this->member->userid);
	}
}
