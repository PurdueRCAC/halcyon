<?php

namespace App\Modules\Widgets\Entities;

use Exception;

class InvalidWidgetClassException extends Exception
{
	/**
	 * Exception message.
	 *
	 * @var string
	 */
	protected $message = 'Widget class must extend App\Modules\Widgets\Entities\Widget class';
}
