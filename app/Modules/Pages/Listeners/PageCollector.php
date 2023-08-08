<?php

namespace App\Modules\Pages\Listeners;

use Illuminate\Events\Dispatcher;
use App\Modules\Pages\Models\Page;
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
	public function subscribe(Dispatcher $events): void
	{
		$events->listen(GenerateSitemap::class, self::class . '@handleGenerateSitemap');
	}

	/**
	 * Add items to the sitemap
	 *
	 * @param   GenerateSitemap $event
	 * @return  void
	 */
	public function handleGenerateSitemap(GenerateSitemap $event): void
	{
		$this->pages(0, $event);
	}

	/**
	 * Recursively add items to the sitemap
	 *
	 * @param   int $parent_id
	 * @param   GenerateSitemap $event
	 * @return  void
	 */
	public function pages(int $parent_id, GenerateSitemap $event): void
	{
		$pages = Page::query()
			->select('id', 'level', 'path', 'updated_at')
			->where('parent_id', '=', $parent_id)
			->where('state', '=', 1)
			->whereIn('access', [1])
			->orderBy('path', 'asc')
			->get();

		foreach ($pages as $page)
		{
			$priority = 0.5;

			if ($page->level == 0)
			{
				$priority = 1.0;
				$route = route('home');
			}
			else
			{
				$route = route('page', ['uri' => $page->path]);
			}

			$event->map->add(
				Url::create($route)
					->setLastModificationDate($page->updated_at)
					->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
					->setPriority($priority)
			);

			$this->pages($page->id, $event);
		}
	}
}
