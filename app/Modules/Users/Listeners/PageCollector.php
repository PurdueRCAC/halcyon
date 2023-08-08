<?php

namespace App\Modules\Users\Listeners;

use Illuminate\Events\Dispatcher;
use App\Modules\Core\Events\GenerateSitemap;
use Spatie\Sitemap\Tags\Url;

/**
 * Listener for sitemap generator
 */
class PageCollector
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Dispatcher  $events
	 * @return void
	 */
	public function subscribe(Dispatcher $events)
	{
		$events->listen(GenerateSitemap::class, self::class . '@handleGenerateSitemap');
	}

	/**
	 * Add items to the sitemap
	 *
	 * @param   GenerateSitemap $event
	 * @return  void
	 */
	public function handleGenerateSitemap(GenerateSitemap $event)
	{
		$event->map->add(
			Url::create(route('login'))
				->setChangeFrequency(Url::CHANGE_FREQUENCY_NEVER)
				->setPriority(0.5)
		);

		$event->map->add(
			Url::create(route('logout'))
				->setChangeFrequency(Url::CHANGE_FREQUENCY_NEVER)
				->setPriority(0.5)
		);

		$event->map->add(
			Url::create(route('site.users.account'))
				->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
				->setPriority(0.5)
		);
	}
}
