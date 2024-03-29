<?php

namespace App\Modules\Queues\Events;

class AllocationDelete
{
	/**
	 * @var int
	 */
	public $id;

	/**
	 * @var array<string,mixed>
	 */
	public $response = array();

	/**
	 * Constructor
	 *
	 * @param  int $id
	 * @return void
	 */
	public function __construct($id)
	{
		$this->id = $id;
	}
}
