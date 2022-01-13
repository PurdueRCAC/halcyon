<?php

namespace App\Modules\News\Listeners;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Modules\History\Models\Log;
use App\Modules\News\Events\AssociationCreated;
use App\Modules\News\Events\AssociationDeleted;
use App\Modules\News\Mail\Registered;
use App\Modules\News\Mail\Cancelled;

/**
 * News listener for registrations
 */
class Registrations
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen(AssociationCreated::class, self::class . '@handleAssociationCreated');
		$events->listen(AssociationDeleted::class, self::class . '@handleAssociationDeleted');
	}

	/**
	 * Send email to event registrations
	 *
	 * @param   object  $event  AssociationCreated
	 * @return  void
	 */
	public function handleAssociationCreated(AssociationCreated $event)
	{
		$association = $event->association;

		$user = $association->associated;

		if (!$user || !$user->email)
		{
			return;
		}

		$message = new Registered($association);

		//echo $message->render();

		Mail::to($user->email)->send($message);

		$this->log($user->id, $association->id, $user->email, 'Emailed event reservation.');
	}

	/**
	 * Send email to event registration cancellations
	 *
	 * @param   object  $event  AssociationDeleted
	 * @return  void
	 */
	public function handleAssociationDeleted(AssociationDeleted $event)
	{
		$association = $event->association;

		$user = $association->associated;

		if (!$user || !$user->email)
		{
			return;
		}

		$message = new Cancelled($association);

		//echo $message->render();

		Mail::to($user->email)->send($message);

		$this->log($user->id, $association->id, $user->email, 'Emailed event reservation cancellation.');
	}

	/**
	 * Log email
	 *
	 * @param   integer $targetuserid
	 * @param   integer $targetobjectid
	 * @param   string  $uri
	 * @param   mixed   $payload
	 * @return  null
	 */
	protected function log($targetuserid, $targetobjectid, $uri = '', $payload = '')
	{
		Log::create([
			'ip'              => request()->ip(),
			'userid'          => (auth()->user() ? auth()->user()->id : 0),
			'status'          => 200,
			'transportmethod' => 'POST',
			'servername'      => request()->getHttpHost(),
			'uri'             => Str::limit($uri, 128, ''),
			'app'             => Str::limit('email', 20, ''),
			'payload'         => Str::limit($payload, 2000, ''),
			'classname'       => Str::limit('listener:reservations', 32, ''),
			'classmethod'     => Str::limit('handle', 16, ''),
			'targetuserid'    => (int)$targetuserid,
			'targetobjectid'  => (int)$targetobjectid,
			'objectid'        => (int)$targetobjectid,
		]);
	}
}
