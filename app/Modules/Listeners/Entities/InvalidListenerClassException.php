<?php

namespace App\Modules\Listeners\Entities;

use Exception;

class InvalidListenerClassException extends Exception
{
	/**
	 * Exception message.
	 *
	 * @var string
	 */
	protected $message = 'Listener class must extend App\Modules\Listeners\Entities\Listener class';
}
