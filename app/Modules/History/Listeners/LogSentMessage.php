<?php

namespace App\Modules\History\Listeners;

use Illuminate\Mail\Events\MessageSent;
use App\Modules\Models\Log;

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
		Log::create([
			'ip'              => request()->ip(),
			'userid'          => (auth()->user() ? auth()->user()->id : 0),
			'status'          => 200,
			'transportmethod' => 'email',
			'servername'      => request()->getHttpHost(),
			'uri'             => $message->getTo(),
			'app'             => 'cli',
			'payload'         => $message,
		]);
	}
}
