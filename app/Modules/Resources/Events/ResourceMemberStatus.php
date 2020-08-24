<?php

namespace App\Modules\Resources\Events;

use App\Modules\Resources\Entities\Asset;
use App\Modules\Users\Models\User;

class ResourceMemberStatus
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
	public $status = -1;

	/**
	 * Constructor
	 *
	 * @param Subresource $subresource
	 * @return void
	 */
	public function __construct(Asset $resource, User $user)
	{
		$this->resource = $resource;
		$this->user = $user;
	}
}
