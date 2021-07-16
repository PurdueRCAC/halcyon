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
	 * @var bool
	 */
	private $enrollment;

	/**
	 * Constructor
	 *
	 * @param  $account
	 * @return void
	 */
	public function __construct(User $instructor, $enrollment = true)
	{
		$this->courses = array();
		$this->instructor = $instructor;
		$this->enrollment = (bool)$enrollment;
	}

	/**
	 * Include enrollment data
	 *
	 * @return bool
	 */
	public function includeEnrollment()
	{
		return $this->enrollment;
	}
}
