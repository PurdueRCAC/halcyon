<?php
namespace App\Modules\Users\Events;

class UserSyncing
{
	/**
	 * @var string
	 */
	public $uid;

	/**
	 * @var object|array
	 */
	public $user;

	/**
	 * Constructor
	 *
	 * @param  string  $uid
	 * @return void
	 */
	public function __construct($uid)
	{
		$this->uid = $uid;
	}
}
