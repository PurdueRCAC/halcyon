<?php

namespace App\Modules\Orders\Listeners;

use Illuminate\Events\Dispatcher;
use App\Modules\Orders\Events\NotifyApprover;
use App\Modules\Orders\Models\Account;
use App\Modules\Orders\Notifications\AccountApprovalNeeded;

/**
 * Account listener
 */
class NotifyAccountApprover
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Dispatcher  $events
	 * @return void
	 */
	public function subscribe(Dispatcher $events): void
	{
		$events->listen(NotifyApprover::class, self::class . '@handleAccountCreated');
	}

	/**
	 * Plugin that loads module positions within content
	 *
	 * @param   GroupReading  $event
	 * @return  void
	 */
	public function handleAccountCreated(NotifyApprover $event): void
	{
		$account = $event->account;
		$account->approver->notify(new AccountApprovalNeeded($event->account));
	}
}
