<?php

namespace App\Modules\Queues\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use App\Modules\Queues\Models\User;

class UserCreated implements ShouldBroadcast
{
	use SerializesModels;

	/**
	 * @var User
	 */
	public $user;

	/**
	 * Constructor
	 *
	 * @param  User $user
	 * @return void
	 */
	public function __construct(User $user)
	{
		$this->user = $user;
	}

	/**
	 * @inheritdoc
	 */
	public function broadcastOn()
	{
		return new PrivateChannel('users.' . $this->user->userid);
	}
}
