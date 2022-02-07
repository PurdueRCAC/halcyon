<?php

namespace App\Modules\Queues\Events;

use App\Modules\Queues\Models\Size;

class QueueSizeUpdated
{
	/**
	 * @var Size
	 */
	public $size;

	/**
	 * Constructor
	 *
	 * @param  Size $size
	 * @return void
	 */
	public function __construct(Size $size)
	{
		$this->size = $size;
	}
}
