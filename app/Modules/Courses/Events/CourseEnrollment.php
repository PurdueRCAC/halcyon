<?php

namespace App\Modules\Courses\Events;

use App\Modules\Courses\Models\Account;

class CourseEnrollment
{
	/**
	 * @var Account
	 */
	public $account;

	/**
	 * @var array
	 */
	public $enrollments;

	/**
	 * Constructor
	 *
	 * @param  $account
	 * @return void
	 */
	public function __construct(Account $account)
	{
		$this->account = $account;
		$this->enrollments = array();
	}
}
