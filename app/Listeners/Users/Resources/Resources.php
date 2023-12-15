<?php
namespace App\Listeners\Users\Resources;

use Illuminate\Events\Dispatcher;
use App\Modules\Users\Events\UserDisplay;
use App\Modules\Resources\Models\Asset;

/**
 * User listener for Resources
 */
class Resources
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Dispatcher  $events
	 * @return void
	 */
	public function subscribe(Dispatcher $events): void
	{
		$events->listen(UserDisplay::class, self::class . '@handleUserDisplay');
	}

		/**
	 * Display session data for a user
	 *
	 * @param   UserDisplay  $event
	 * @return  void
	 */
	public function handleUserDisplay(UserDisplay $event): void
	{
		if (!auth()->user() || !auth()->user()->can('manage users'))
		{
			return;
		}

		$user = $event->getUser();

		$resources = Asset::query()
			->where('rolename', '!=', '')
			->where('listname', '!=', '')
			->orderBy('name', 'asc')
			->get();

		$content = view('resources::site.profile', [
			'user' => $user,
			'resources' => $resources,
		]);

		$event->addPart(
			$content
		);
	}
}
