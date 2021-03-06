<?php

namespace App\Modules\Resources\Events;

use App\Modules\Resources\Models\Asset;
use App\Modules\Users\Models\User;

class ResourceMemberCreated
{
	/**
	 * @var Asset
	 */
	public $resource;

	/**
	 * @var User
	 */
	public $user;

	/**
	 * @var integer
	 */
	public $status;

	/**
	 * @var array
	 */
	public $errors = array();

	/**
	 * Constructor
	 *
	 * @param Asset $resource
	 * @param User $user
	 * @return void
	 */
	public function __construct(Asset $resource, User $user)
	{
		$this->resource = $resource;
		$this->user = $user;
	}
}
