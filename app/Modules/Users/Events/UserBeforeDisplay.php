<?php
namespace App\Modules\Users\Events;

use App\Modules\Users\Models\User;

class UserBeforeDisplay
{
	/**
	 * The user
	 *
	 * @var User
	 */
	private $user;

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
	 * Get the user
	 *
	 * @return User
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * Set the body
	 *
	 * @param  User $user
	 * @return void
	 */
	public function setUser(User $user)
	{
		$this->user = $user;
	}
}
