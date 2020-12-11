<?php
namespace App\Modules\Users\Events;

class UserBeforeDisplay
{
	/**
	 * The user
	 *
	 * @var string
	 */
	private $user;

	/**
	 * Constructor
	 *
	 * @param  object $user
	 * @return void
	 */
	public function __construct($user)
	{
		$this->user = $user;
	}

	/**
	 * Get the user
	 *
	 * @return string
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * Set the body
	 *
	 * @param string $body
	 * @return void
	 */
	public function setUser($user)
	{
		$this->user = $user;
	}
}
