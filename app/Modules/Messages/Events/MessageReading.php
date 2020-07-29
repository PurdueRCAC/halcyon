<?php

namespace App\Modules\Messages\Events;

use App\Modules\Messages\Models\Message;

class MessageReading
{
	/**
	 * @var Message
	 */
	public $message;

	/**
	 * @var string
	 */
	public $target;

	/**
	 * Constructor
	 *
	 * @param Message $message
	 * @return void
	 */
	public function __construct(Message $message)
	{
		$this->message = $message;
	}
}
