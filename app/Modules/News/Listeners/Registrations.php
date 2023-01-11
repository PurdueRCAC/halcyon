<?php

namespace App\Modules\News\Listeners;

use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Mail;
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
	 * @param  Dispatcher  $events
	 * @return void
	 */
	public function subscribe(Dispatcher $events): void
	{
		$events->listen(AssociationCreated::class, self::class . '@handleAssociationCreated');
		$events->listen(AssociationDeleted::class, self::class . '@handleAssociationDeleted');
	}

	/**
	 * Send email to event registrations
	 *
	 * @param   AssociationCreated  $event
	 * @return  void
	 */
	public function handleAssociationCreated(AssociationCreated $event): void
	{
		$association = $event->association;

		$user = $association->associated;

		if (!$user || !$user->email)
		{
			return;
		}

		$message = new Registered($association);

		Mail::to($user->email)->send($message);

		$this->log($user->id, $association->id, $user->email, 'Emailed event reservation.');
	}

	/**
	 * Send email to event registration cancellations
	 *
	 * @param   AssociationDeleted  $event
	 * @return  void
	 */
	public function handleAssociationDeleted(AssociationDeleted $event): void
	{
		$association = $event->association;

		$user = $association->associated;

		if (!$user || !$user->email)
		{
			return;
		}

		$message = new Cancelled($association);

		Mail::to($user->email)->send($message);

		$this->log($user->id, $association->id, $user->email, 'Emailed event reservation cancellation.');
	}

	/**
	 * Log email
	 *
	 * @param   integer $targetuserid
	 * @param   integer $targetobjectid
	 * @param   string  $uri
	 * @param   string  $payload
	 * @return  void
	 */
	protected function log(int $targetuserid, int $targetobjectid, $uri = '', $payload = ''): void
	{
		Log::create([
			'ip'              => request()->ip(),
			'userid'          => (auth()->user() ? auth()->user()->id : 0),
			'status'          => 200,
			'transportmethod' => 'POST',
			'servername'      => request()->getHttpHost(),
			'uri'             => $uri,
			'app'             => 'email',
			'payload'         => $payload,
			'classname'       => 'listener:reservations',
			'classmethod'     => 'handle',
			'targetuserid'    => (int)$targetuserid,
			'targetobjectid'  => (int)$targetobjectid,
			'objectid'        => (int)$targetobjectid,
		]);
	}
}
