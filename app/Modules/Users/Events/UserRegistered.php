<?php
namespace App\Modules\Users\Events;

use App\Modules\Users\Models\User;

class UserRegistered
{
	/**
	 * @var User
	 */
	public $user;

	/**
	 * Constructor
	 *
	 * @param User $user
	 * @return void
	 */
	public function __construct(User $user)
	{
		$this->user = $user;
	}
}
