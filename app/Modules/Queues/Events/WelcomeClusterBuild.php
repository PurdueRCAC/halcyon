<?php

namespace App\Modules\Queues\Events;

use App\Modules\Users\Models\User;

class WelcomeClusterBuild
{
	/**
	 * @var User
	 */
	public $user;

	/**
	 * @var array<int,\stdClass>
	 */
	public $activity;

	/**
	 * @var string
	 */
	public $path;

	/**
	 * Constructor
	 *
	 * @param  User $user
	 * @param  array<int,\stdClass> $activity
	 * @return void
	 */
	public function __construct(User $user, $activity)
	{
		$this->user = $user;
		$this->activity = $activity;
	}
}
