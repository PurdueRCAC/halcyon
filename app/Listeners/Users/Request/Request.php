<?php
namespace App\Listeners\Users\Request;

use App\Modules\Users\Events\UserDisplay;
use App\Modules\Listeners\Models\Listener;

/**
 * Requestlistener for users
 */
class Request
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen(UserDisplay::class, self::class . '@handleUserDisplay');
	}

	/**
	 * Plugin that loads module positions within content
	 *
	 * @param   UserDisplay  $event
	 * @return  void
	 */
	public function handleUserDisplay(UserDisplay $event)
	{
		if (app('isAdmin'))
		{
			return;
		}

		$listener = Listener::query()
			->where('type', '=', 'listener')
			->where('folder', '=', 'users')
			->where('element', '=', 'Request')
			->get()
			->first();

		if (auth()->user() && !in_array($listener->access, auth()->user()->getAuthorisedViewLevels()))
		{
			return;
		}

		$content = null;
		$user = $event->getUser();

		if (!$user->enabled)
		{
			return;
		}

		$r = ['section' => 'request'];
		if (auth()->user()->id != $user->id)
		{
			$r['u'] = $user->id;
		}

		$ns = 'listener.users.request';
		app('translator')->addNamespace(
			$ns,
			__DIR__ . '/lang'
		);

		if ($event->getActive() == 'request')
		{
			app('view')->addNamespace(
				$ns,
				__DIR__ . '/views'
			);

			app('pathway')->append(
				trans($ns . '::request.request access'),
				route('site.users.account.section', $r)
			);

			$content = view($ns . '::profile', [
				'user' => $user,
			]);
		}

		$event->addSection(
			route('site.users.account.section', $r),
			trans($ns . '::request.request access'),
			($event->getActive() == 'request'),
			$content
		);
	}
}
