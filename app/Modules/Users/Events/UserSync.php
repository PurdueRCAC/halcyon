<?php
namespace App\Modules\Users\Events;

use App\Modules\Users\Models\User;

class UserSync
{
	/**
	 * @var User
	 */
	public $user;

	/**
	 * @var bool
	 */
	public $authorized;

	/**
	 * @var string|null
	 */
	public $rolename;

	/**
	 * Constructor
	 *
	 * @param  User  $user
	 * @param  bool  $authorized
	 * @param  string|null  $rolename
	 * @return void
	 */
	public function __construct(User $user, $authorized = false, $rolename = null)
	{
		$this->user = $user;
		$this->authorized = $authorized;
		$this->rolename = $rolename;
	}
}
