<?php

namespace App\Modules\Resources\Events;

use App\Modules\Resources\Entities\Subresource;

class SubresourceDeleted
{
	/**
	 * @var Subresource
	 */
	public $subresource;

	/**
	 * Constructor
	 *
	 * @param  Asset $subresource
	 * @return void
	 */
	public function __construct(Subresource $subresource)
	{
		$this->subresource = $subresource;
	}
}
