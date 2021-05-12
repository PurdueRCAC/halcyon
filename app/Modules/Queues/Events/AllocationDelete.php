<?php

namespace App\Modules\Queues\Events;

class AllocationDelete
{
	/**
	 * @var array
	 */
	public $id;

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
	public function __construct($id)
	{
		$this->id = $id;
	}
}
