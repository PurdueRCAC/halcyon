<?php
namespace App\Modules\Users\Events;

class UserSync
{
	/**
	 * @var object
	 */
	public $user;

	/**
	 * @var bool
	 */
	public $authorized;

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct($user, $authorized = false)
	{
		$this->user = $user;
		$this->authorized = $authorized;
	}
}
