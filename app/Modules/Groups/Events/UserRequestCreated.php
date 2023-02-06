<?php

namespace App\Modules\Groups\Events;

use App\Modules\Groups\Models\UserRequest;

class UserRequestCreated
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
