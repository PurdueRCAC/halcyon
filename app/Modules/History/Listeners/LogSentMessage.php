<?php

namespace App\Modules\History\Listeners;

use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Str;
use App\Modules\History\Models\Log;

/**
 * Listener for sent messages
 */
class LogSentMessage
{
	/**
	 * Plugin that loads module positions within content
	 *
	 * @param   object   $event
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
			'uri'             => Str::limit($event->message->getTo(), 128, ''),
			'app'             => Str::limit('email', 20, ''),
			'payload'         => Str::limit($event->message->getSubject(), 2000, ''),
			'classname'       => Str::limit($cmd, 32, ''),
			'classmethod'     => Str::limit('handle', 16, ''),
		]);
	}
}
