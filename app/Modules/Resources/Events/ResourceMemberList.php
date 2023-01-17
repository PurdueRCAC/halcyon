<?php

namespace App\Modules\Resources\Events;

use App\Modules\Resources\Models\Asset;

class ResourceMemberList
{
	/**
	 * @var Asset
	 */
	public $resource;

	/**
	 * @var array<int,mixed>
	 */
	public $results = array();

	/**
	 * @var array<int,string>
	 */
	public $errors = array();

	/**
	 * Constructor
	 *
	 * @param Asset $resource
	 * @return void
	 */
	public function __construct(Asset $resource)
	{
		$this->resource = $resource;
	}
}
