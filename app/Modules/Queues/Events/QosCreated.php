<?php

namespace App\Modules\Queues\Events;

use App\Modules\Queues\Models\Qos;

class QosCreated
{
	/**
	 * @var Qos
	 */
	public $qos;

	/**
	 * Constructor
	 *
	 * @param  Qos $qos
	 * @return void
	 */
	public function __construct(Qos $qos)
	{
		$this->qos = $qos;
	}
}
