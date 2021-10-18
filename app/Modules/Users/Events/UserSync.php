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
	 * @var string
	 */
	public $rolename;

	/**
	 * Constructor
	 *
	 * @param  object  $user
	 * @param  bool    $authorized
	 * @param  string  $rolename
	 * @return void
	 */
	public function __construct($user, $authorized = false, $rolename = null)
	{
		$this->user = $user;
		$this->authorized = $authorized;
		$this->rolename = $rolename;
	}
}
