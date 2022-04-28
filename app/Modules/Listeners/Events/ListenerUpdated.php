<?php

namespace App\Modules\Listeners\Events;

use App\Modules\Listeners\Models\Listener;

class ListenerUpdated
{
	/**
	 * @var Listener
	 */
	public $listener;

	/**
	 * Constructor
	 *
	 * @param Listener $listener
	 * @return void
	 */
	public function __construct(Listener $listener)
	{
		$this->listener = $listener;
	}
}
