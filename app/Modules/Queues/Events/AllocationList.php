<?php

namespace App\Modules\Queues\Events;

use Illuminate\Http\Response;

class AllocationList
{
	/**
	 * @var string
	 */
	public $hostname = '';

	/**
	 * @var array
	 */
	public $queues;

	/**
	 * @var string
	 */
	public $format = '';

	/**
	 * @var Response
	 */
	public $response;

	/**
	 * Constructor
	 *
	 * @param  string $hostname
	 * @param  array  $queues
	 * @param  string $format
	 * @return void
	 */
	public function __construct($hostname, $queues, $format = '')
	{
		$this->hostname = $hostname;
		$this->queues = $queues;
		$this->format = $format;
	}
}
