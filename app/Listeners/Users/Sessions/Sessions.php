<?php
namespace App\Listeners\Users\Sessions;

use Illuminate\Support\Facades\DB;
use App\Modules\Users\Events\UserDisplay;
use App\Modules\Users\Events\UserDeleted;

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
		//$events->listen(UserDisplay::class, self::class . '@handleUserDisplay');
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

	/**
	 * Display session data for a user
	 *
	 * @param   UserDisplay  $event
	 * @return  void
	 */
	public function handleUserDisplay(UserDisplay $event)
	{
		$content = null;
		$user = $event->getUser();

		$r = ['section' => 'sessions'];
		if (auth()->user()->id != $user->id)
		{
			$r['u'] = $user->id;
		}

		app('translator')->addNamespace(
			'listener.users.sessions',
			__DIR__ . '/lang'
		);

		if ($event->getActive() == 'tickets')
		{
			app('pathway')
				->append(
					trans('listener.users.sessions::sessions.sessions'),
					route('site.users.account.section', $r)
				);

			app('view')->addNamespace(
				'listener.users.sessions',
				__DIR__ . '/views'
			);

			$content = view('listener.users.sessions::profile', [
				'user' => $user,
			]);
		}

		$event->addSection(
			route('site.users.account.section', $r),
			trans('listener.users.sessions::sessions.sessions'),
			($event->getActive() == 'sessions'),
			$content
		);
	}
}
