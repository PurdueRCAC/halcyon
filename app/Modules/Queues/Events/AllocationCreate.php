<?php

namespace App\Modules\Queues\Events;

class AllocationCreate
{
	/**
	 * @var array
	 */
	public $data;

	/**
	 * @var array
	 */
	public $response = array();

	/**
	 * Constructor
	 *
	 * @param  array $data
	 * @return void
	 */
	public function __construct($data)
	{
		$this->data = $data;
	}
}
