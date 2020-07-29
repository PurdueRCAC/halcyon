<?php

namespace App\Modules\Queues\Events;

use App\Modules\Queues\Models\User;

class UserDeleted
{
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
}
