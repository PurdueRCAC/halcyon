<?php

namespace App\Modules\News\Listeners;

use Illuminate\Events\Dispatcher;
use App\Modules\News\Models\Article;
use App\Modules\News\Models\Type;
use App\Modules\Core\Events\GenerateSitemap;
use Spatie\Sitemap\Tags\Url;
use Carbon\Carbon;

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
			Url::create(route('site.news.index'))
				->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
				->setPriority(0.7)
		);

		$event->map->add(
			Url::create(route('site.news.search'))
				->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
				->setPriority(0.5)
		);

		$event->map->add(
			Url::create(route('site.news.rss'))
				->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
				->setPriority(0.5)
		);

		$options = Type::query()
			//->where('name', 'NOT LIKE', 'coffee%')
			->where('parentid', '=', 0)
			->orderBy('ordering', 'asc')
			->orderBy('name', 'asc')
			->get();

		foreach ($options as $type)
		{
			$event->map->add(
				Url::create(route('site.news.type', ['name' => $type->alias]))
					->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
					->setPriority(0.5)
			);

			$children = $type->children()
				->orderBy('ordering', 'asc')
				->orderBy('name', 'asc')
				->get();

			foreach ($children as $child)
			{
				$event->map->add(
					Url::create($route = route('site.news.type', ['name' => $child->alias]))
						->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
						->setPriority(0.5)
				);
			}
		}

		$yearago = Carbon::now()->modify('-1 year');

		$articles = Article::query()
			->select('id', 'datetimenews', 'datetimenewsend', 'datetimecreated', 'datetimeedited')
			->where('template', '=', 0)
			->where('published', '=', 1)
			->get();

		foreach ($articles as $article)
		{
			$priority = 0.5;
			$frequency = Url::CHANGE_FREQUENCY_YEARLY;

			if ($article->hasEnd())
			{
				$frequency = Url::CHANGE_FREQUENCY_MONTHLY;

				if ($article->ended())
				{
					$priority = 0.3;
					$frequency = Url::CHANGE_FREQUENCY_YEARLY;

					// If older than a year...
					if ($article->datetimenewsend < $yearago)
					{
						$priority = 0.1;
						$frequency = Url::CHANGE_FREQUENCY_NEVER;
					}
				}
			}
			else
			{
				if ($article->datetimenews < $yearago)
				{
					$priority = 0.1;
					$frequency = Url::CHANGE_FREQUENCY_NEVER;
				}
			}

			$route = route('site.news.show', ['id' => $article->id]);

			$updated_at = $article->datetimeedited ? $article->datetimeedited : $article->datetimecreated;

			$event->map->add(
				Url::create($route)
					->setLastModificationDate($updated_at)
					->setChangeFrequency($frequency)
					->setPriority($priority)
			);
		}
	}
}
