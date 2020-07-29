<?php

namespace App\Modules\Messages\Events;

use App\Modules\Messages\Models\Message;

class MessageUpdated
{
	/**
	 * @var Message
	 */
	public $message;

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
