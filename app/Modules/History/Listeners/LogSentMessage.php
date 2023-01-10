<?php

namespace App\Modules\History\Listeners;

use Illuminate\Mail\Events\MessageSent;
use App\Modules\History\Models\Log;

/**
 * Listener for sent messages
 */
class LogSentMessage
{
	/**
	 * Plugin that loads module positions within content
	 *
	 * @param   MessageSent  $event
	 * @return  void
	 */
	public function handle(MessageSent $event)
	{
		$headers = $message->getHeaders();

		$cmd = '';
		foreach ($headers as $key => $value)
		{
			if ($key == 'X-Command')
			{
				$cmd = $value;
				break;
			}
		}

		Log::create([
			'ip'              => '127.0.0.1',
			'userid'          => (auth()->user() ? auth()->user()->id : 0),
			'status'          => 200,
			'transportmethod' => 'POST',
			'servername'      => 'localhost',
			'uri'             => $event->message->getTo(),
			'app'             => 'email',
			'payload'         => $event->message->getSubject(),
			'classname'       => $cmd,
			'classmethod'     => 'handle',
		]);
	}
}
