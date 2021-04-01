<?php

namespace App\Modules\Resources\Events;

use App\Modules\Resources\Models\Subresource;

class SubresourceCreated
{
	/**
	 * @var Subresource
	 */
	public $subresource;

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
	 * Return the entity
	 *
	 * @return \Illuminate\Database\Eloquent\Model
	 */
	public function getSubresource()
	{
		return $this->subresource;
	}
}
