<?php
namespace App\Listeners\Users\Sessions;

use App\Modules\Users\Events\UserDeleted;
use Illuminate\Support\Facades\DB;

/**
 * User listener for sessions
 */
class Sessions
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen(UserDeleted::class, self::class . '@handleUserDeleted');
	}

	/**
	 * Remove sessions when a User is deleted
	 *
	 * @param   UserDeleted  $event
	 * @return  void
	 */
	public function handleUserDeleted(UserDeleted $event)
	{
		DB::table('sessions')
			->where('user_id', '=', $event->user->id)
			->delete();
	}
}
