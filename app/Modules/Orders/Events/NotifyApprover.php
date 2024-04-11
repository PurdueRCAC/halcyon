<?php

namespace App\Modules\Orders\Events;

use App\Modules\Orders\Models\Account;

class NotifyApprover
{
	/**
	 * @var Account
	 */
	public $account;

	/**
	 * Constructor
	 *
	 * @param  Account $account
	 * @return void
	 */
	public function __construct(Account $account)
	{
		$this->account = $account;
	}
}
