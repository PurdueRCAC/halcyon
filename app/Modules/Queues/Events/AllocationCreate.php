<?php

namespace App\Modules\Queues\Events;

class AllocationCreate
{
	/**
	 * @var array<string,mixed>
	 */
	public $data;

	/**
	 * @var array
	 */
	public $response = array();

	/**
	 * Constructor
	 *
	 * @param  array<string,mixed> $data
	 * @return void
	 */
	public function __construct($data)
	{
		$this->data = $data;
	}
}
