<?php

namespace App\Modules\Queues\Events;

use App\Modules\Queues\Models\UserRequest;

class UserRequestCreated
{
	/**
	 * @var User
	 */
	public $userrequest;

	/**
	 * Constructor
	 *
	 * @param  User $user
	 * @return void
	 */
	public function __construct(UserRequest $user)
	{
		$this->userrequest = $userrequest;
	}
}
