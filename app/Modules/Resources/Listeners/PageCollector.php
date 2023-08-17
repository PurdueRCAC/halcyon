<?php

namespace App\Modules\Resources\Listeners;

use Illuminate\Events\Dispatcher;
use App\Modules\Resources\Models\Type;
use App\Modules\Resources\Events\AssetDisplaying;
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
		$types = Type::all();

		foreach ($types as $type)
		{
			$event->map->add(
				Url::create(route('site.resources.type.' . $type->alias))
					->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
					->setPriority(0.5)
			);

			$event->map->add(
				Url::create(route('site.resources.' . $type->alias . '.retired'))
					->setChangeFrequency(Url::CHANGE_FREQUENCY_NEVER)
					->setPriority(0.1)
			);

			// Active
			$rows = $type->resources()
				->where('display', '>', 0)
				->where(function ($where)
				{
					$where->whereNotNull('listname')
						->where('listname', '!=', '');
				})
				->whereNotNull('description')
				->orderBy('display', 'desc')
				->get();

			foreach ($rows as $row)
			{
				$event->map->add(
					Url::create(route('site.resources.' . $type->alias . '.show', ['name' => $row->listname]))
						->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
						->setPriority(0.5)
				);

				/*$section = null;
				event($e = new AssetDisplaying($row, $section));
				$sections = collect($e->getSections());

				foreach ($sections as $sec)
				{
					$event->map->add(
						Url::create($sec['route'])
							->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
							->setPriority(0.4)
					);
				}*/
			}

			// Retired
			$rows = $type->resources()
				->where('display', '>', 0)
				->onlyTrashed()
				->where(function($where)
				{
					$where->whereNotNull('listname')
						->where('listname', '!=', '');
				})
				->whereNotNull('description')
				->orderBy('display', 'desc')
				->get();

			foreach ($rows as $row)
			{
				$event->map->add(
					Url::create(route('site.resources.' . $type->alias . '.show', ['name' => $row->listname]))
						->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
						->setPriority(0.2)
				);
			}
		}
	}
}
