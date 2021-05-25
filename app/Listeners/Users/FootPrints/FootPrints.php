<?php
namespace App\Listeners\Users\FootPrints;

use App\Modules\Users\Events\UserDisplay;
use App\Listeners\Users\FootPrints\Models\TicketAction;

/**
 * User listener for FootPrints
 */
class FootPrints
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
	 * Display data for a user
	 *
	 * @param   UserDisplay  $event
	 * @return  void
	 */
	public function handleUserDisplay(UserDisplay $event)
	{
		$content = null;
		$user = $event->getUser();

		$r = ['section' => 'tickets'];
		if (auth()->user()->id != $user->id)
		{
			$r['u'] = $user->id;
		}

		app('translator')->addNamespace(
			'listener.users.footprints',
			__DIR__ . '/lang'
		);

		if ($event->getActive() == 'tickets')
		{
			app('pathway')
				->append(
					trans('listener.users.footprints::footprints.tickets'),
					route('site.users.account.section', $r)
				);

			$tickets = TicketAction::query()
				->where('actoruserid', '=', $user->id)
				->where('submission', '=', 1)
				->orderBy('datetimesubmission', 'desc')
				->get();

			app('view')->addNamespace(
				'listener.users.footprints',
				__DIR__ . '/views'
			);

			$content = view('listener.users.footprints::profile', [
				'user'    => $user,
				'tickets' => $tickets,
			]);
		}

		$event->addSection(
			route('site.users.account.section', $r),
			trans('listener.users.footprints::footprints.tickets'),
			($event->getActive() == 'tickets'),
			$content
		);
	}
}
