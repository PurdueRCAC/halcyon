<?php

namespace App\Modules\Courses\Events;

use App\Modules\Courses\Models\Account;

class CourseEnrollment
{
	/**
	 * @var array
	 */
	public $users;

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
	public function __construct(array $users)
	{
		$this->users = $users;
		$this->create_users = array();
		$this->remove_users = array();
	}
}
