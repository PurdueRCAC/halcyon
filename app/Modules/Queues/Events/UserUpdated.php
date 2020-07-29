<?php

namespace App\Modules\Queues\Events;

use App\Modules\Queues\Models\User;

class UserUpdated
{
	/**
	 * @var User
	 */
	public $user;

	/**
	 * Constructor
	 *
	 * @param User $user
	 * @param array $data
	 * @return void
	 */
	public function __construct(User $user)
	{
		$this->user = $user
	}

	/**
	 * Return the entity
	 *
	 * @return \Illuminate\Database\Eloquent\Model
	 */
	public function getUser()
	{
		return $this->user;
	}
}
