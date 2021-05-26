<?php

namespace App\Modules\Queues\Events;

class AllocationUpdate
{
	/**
	 * @var mixed
	 */
	public $id;

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
	public function __construct($id, $data)
	{
		$this->id = $id;
		$this->data = $data;
	}
}
