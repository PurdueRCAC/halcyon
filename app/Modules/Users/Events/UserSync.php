<?php
namespace App\Modules\Users\Events;

class UserSync
{
	/**
	 * @var object
	 */
	public $user;

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct($user)
	{
		$this->user = $user;
	}
}
