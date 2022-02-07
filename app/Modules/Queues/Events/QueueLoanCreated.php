<?php

namespace App\Modules\Queues\Events;

use App\Modules\Queues\Models\Loan;

class QueueLoanCreated
{
	/**
	 * @var Loan
	 */
	public $loan;

	/**
	 * Constructor
	 *
	 * @param  Loan $loan
	 * @return void
	 */
	public function __construct(Loan $loan)
	{
		$this->loan = $loan;
	}
}
