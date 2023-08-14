<?php

namespace App\Modules\Queues\Events;

use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use App\Modules\Queues\Models\Queue;

class AllocationList
{
	/**
	 * @var string
	 */
	public $hostname = '';

	/**
	 * @var array<int,Queue>|Collection<int,Queue>
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
	 * @param  array<int,Queue>|Collection<int,Queue>  $queues
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
