<?php

namespace App\Modules\Queues\Events;

class AllocationUpdate
{
	/**
	 * @var mixed
	 */
	public $id;

	/**
	 * @var array<string,mixed>
	 */
	public $data;

	/**
	 * @var array<string,mixed>
	 */
	public $response = array();

	/**
	 * Constructor
	 *
	 * @param  mixed  $id
	 * @param  array<string,mixed> $data
	 * @return void
	 */
	public function __construct($id, $data)
	{
		$this->id = $id;
		$this->data = $data;
	}
}
