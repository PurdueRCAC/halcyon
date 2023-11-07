<?php

namespace App\Modules\News\Listeners;

use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Mail;
//use App\Modules\History\Models\Log;
use App\Modules\News\Events\AssociationCreated;
use App\Modules\News\Events\AssociationDeleted;
//use App\Modules\News\Mail\Registered;
//use App\Modules\News\Mail\Cancelled;
use App\Modules\News\Notifications\ArticleRegistered;
use App\Modules\News\Notifications\ArticleCancelled;

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

		$user->notify(new ArticleRegistered($association));
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

		$user->notify(new ArticleCancelled($association));
	}
}
