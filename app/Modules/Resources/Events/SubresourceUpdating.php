<?php

namespace App\Modules\Resources\Events;

use App\Modules\Resources\Entities\Subresource;

class SubresourceUpdating
{
	/**
	 * @var Subresource
	 */
	private $subresource;

	/**
	 * Constructor
	 *
	 * @param Subresource $subresource
	 * @return void
	 */
	public function __construct(Subresource $subresource)
	{
		$this->subresource = $subresource;
	}

	/**
	 * @return User
	 */
	public function getSubresource()
	{
		return $this->subresource;
	}
}
