<?php

namespace App\Modules\Queues\Events;

use App\Modules\Queues\Models\UserRequest;

class UserRequestDeleted
{
	/**
	 * @var UserRequest
	 */
	public $userrequest;

	/**
	 * Constructor
	 *
	 * @param  UserRequest $userrequest
	 * @return void
	 */
	public function __construct(UserRequest $userrequest)
	{
		$this->userrequest = $userrequest;
	}
}
