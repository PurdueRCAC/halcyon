<?php

namespace App\Modules\Courses\Events;

use App\Modules\Courses\Models\Account;
use App\Modules\Users\Models\User;

class AccountInstructorLookup
{
	/**
	 * @var Account
	 */
	public $account;

	/**
	 * @var User
	 */
	public $instructor;

	/**
	 * Constructor
	 *
	 * @param  $account
	 * @return void
	 */
	public function __construct(Account $account, User $instructor)
	{
		$this->account = $account;
		$this->instructor = $instructor;
	}
}
