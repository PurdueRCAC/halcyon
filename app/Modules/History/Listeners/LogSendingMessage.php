<?php

namespace App\Modules\History\Listeners;

use Illuminate\Mail\Events\MessageSending;
use App\Modules\History\Models\Log;

/**
 * Listener for sent messages
 */
class LogSendingMessage
{
	/**
	 * Log sent messages
	 *
	 * @param   MessageSending  $event
	 * @return  void
	 */
	public function handle(MessageSending $event): void
	{
		$headers = $event->message->getHeaders();

		$classname = substr(strrchr(get_class($this), '\\'), 1);
		$targetuserid = 0;
		$targetobjectid = 0;
		$objectid = 0;

		if ($headers->has('x-command'))
		{
			$classname = $headers->get('x-command')->getValue();
			$event->message->getHeaders()->remove('x-command');
		}
		if ($headers->has('x-target-user'))
		{
			$targetuserid = $headers->get('x-target-user')->getValue();
			$event->message->getHeaders()->remove('x-target-user');
		}
		if ($headers->has('x-target-object'))
		{
			$targetobjectid = $headers->get('x-target-object')->getValue();
			$event->message->getHeaders()->remove('x-target-object');
		}
		if ($headers->has('x-object'))
		{
			$objectid = $headers->get('x-object')->getValue();
			$event->message->getHeaders()->remove('x-object');
		}

		foreach ($event->message->getTo() as $address)
		{
			Log::create([
				'ip'              => '127.0.0.1',
				'userid'          => (auth()->user() ? auth()->user()->id : 0),
				'status'          => 200,
				'transportmethod' => 'POST',
				'servername'      => 'localhost',
				'uri'             => $address->getAddress(),
				'app'             => 'email',
				'payload'         => $event->message->getSubject(),
				'classname'       => $classname,
				'classmethod'     => 'handle',
				'targetuserid'    => $targetuserid,
				'targetobjectid'  => $targetobjectid,
				'objectid'        => $objectid,
			]);
		}
	}
}
