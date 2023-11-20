<?php

namespace App\Modules\Knowledge\Listeners;

use Illuminate\Events\Dispatcher;
use App\Modules\Knowledge\Models\Page;
use App\Modules\Knowledge\Models\Associations;
use App\Modules\Core\Events\GenerateSitemap;
use Spatie\Sitemap\Tags\Url;

/**
 * Listener for sitemap generator
 */
class PageCollector
{
	/**
	 * @var bool
	 */
	private $allow_all = false;

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
	public function handleGenerateSitemap(GenerateSitemap $event): void
	{
		$this->allow_all = config('module.knowledge.allow_all');

		$this->pages(0, $event);
	}

	/**
	 * Recursively get pages
	 *
	 * @param int $parent_id
	 * @param GenerateSitemap $event
	 * @return void
	 */
	private function pages(int $parent_id, GenerateSitemap $event): void
	{
		$p = (new Page)->getTable();
		$a = (new Associations)->getTable();

		$options = $lists = Page::query()
			->join($a, $a . '.page_id', $p . '.id')
			->select($p . '.*', $a . '.level', $a . '.lft', $a . '.rgt', $a . '.id AS assoc_id', $a . '.path AS assoc_path')
			->where($a . '.state', '=', 1)
			->whereIn($a . '.access', [1])
			->where($a . '.parent_id', '=', $parent_id)
			->orderBy($a . '.lft', 'asc')
			->get();

		foreach ($options as $page)
		{
			if ($page->assoc_path == '-separator-')
			{
				continue;
			}

			$priority = 0.5;

			if ($page->level == 0)
			{
				$route = route('site.knowledge.index');
				$priority = 0.8;
			}
			else
			{
				if ($page->level = 1)
				{
					$priority = 0.7;
				}
				$route = route('site.knowledge.page', ['uri' => $page->assoc_path]);
			}

			$event->map->add(
				Url::create($route)
					->setLastModificationDate($page->updated_at)
					->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
					->setPriority($priority)
			);

			if ($this->allow_all)
			{
				$event->map->add(
					Url::create($route . '?all=true')
						->setLastModificationDate($page->updated_at)
						->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
						->setPriority($priority)
				);
			}

			$this->pages($page->assoc_id, $event);
		}
	}
}
