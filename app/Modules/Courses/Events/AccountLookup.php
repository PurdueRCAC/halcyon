<?php

namespace App\Modules\Courses\Events;

use App\Modules\Courses\Models\Account;

class AccountLookup
{
	/**
	 * @var Account
	 */
	public $account;

	/**
	 * Constructor
	 *
	 * @param  $account
	 * @return void
	 */
	public function __construct(Account $account)
	{
		$this->account = $account;
	}
}
