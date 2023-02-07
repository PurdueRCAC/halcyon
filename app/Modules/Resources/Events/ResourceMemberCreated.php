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
	 * No status for the provided resource and user
	 *
	 * @return bool
	 */
	public function noStatus(): bool
	{
		return ($this->status == 1);
	}

	/**
	 * Status is pending
	 *
	 * @return bool
	 */
	public function isPending(): bool
	{
		return ($this->status == 2);
	}

	/**
	 * Status is ready
	 *
	 * @return bool
	 */
	public function isReady(): bool
	{
		return ($this->status == 3);
	}

	/**
	 * Status is pending removal
	 *
	 * @return bool
	 */
	public function isPendingRemoval(): bool
	{
		return ($this->status == 4);
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
