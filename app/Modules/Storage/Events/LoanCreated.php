<?php

namespace App\Modules\Storage\Events;

use App\Modules\Storage\Models\Loan;

class LoanCreated
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
