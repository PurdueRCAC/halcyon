<?php

namespace App\Modules\Queues\Events;

use App\Modules\Queues\Models\User;

class UserUpdating
{
	/**
	 * @var User
	 */
	private $user;

	public function __construct(User $user)
	{
		$this->user = $user;
	}

	/**
	 * @return User
	 */
	public function getUser()
	{
		return $this->user;
	}
}
