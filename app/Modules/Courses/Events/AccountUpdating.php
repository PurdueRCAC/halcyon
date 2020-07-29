<?php

namespace App\Modules\Courses\Events;

use App\Modules\Courses\Models\Account;

class AccountUpdating
{
	/**
	 * @var Account
	 */
	public $account;

	/**
	 * Constructor
	 *
	 * @param Account $account
	 * @return void
	 */
	public function __construct(Account $account)
	{
		$this->account = $account;
	}
}
