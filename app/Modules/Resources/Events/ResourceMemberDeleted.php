<?php

namespace App\Modules\Resources\Events;

use App\Modules\Resources\Models\Asset;
use App\Modules\Users\Models\User;

class ResourceMemberDeleted
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
	 * @var int
	 */
	public $status = 1;

	/**
	 * @var array<int,string>
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

	/**
	 * An error occurred determining status
	 *
	 * @return bool
	 */
	public function hasError(): bool
	{
		return ($this->status == -1 || !empty($this->errors));
	}
}
