<?php

namespace App\Modules\Courses\Events;

use App\Modules\Users\Models\User;

class InstructorLookup
{
	/**
	 * @var Account
	 */
	public $courses = array();

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
	public function __construct(User $instructor)
	{
		$this->courses = array();
		$this->instructor = $instructor;
	}
}
