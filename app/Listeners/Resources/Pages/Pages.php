<?php
namespace App\Listeners\Resources\Pages;

use Illuminate\Events\Dispatcher;
use App\Modules\Resources\Events\AssetDisplaying;
use App\Modules\Pages\Models\Page;

/**
 * Pages listener for Resources
 */
class Pages
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Dispatcher  $events
	 * @return void
	 */
	public function subscribe(Dispatcher $events): void
	{
		$events->listen(AssetDisplaying::class, self::class . '@handleAssetDisplaying');
	}

	/**
	 * Find pages to be listed on a resource Asset's overview
	 *
	 * @param   AssetDisplaying  $event
	 * @return  void
	 */
	public function handleAssetDisplaying(AssetDisplaying $event): void
	{
		if (app()->has('isAdmin') && app()->get('isAdmin'))
		{
			return;
		}

		$paths = config()->get('listener.pages.display', []);

		if (empty($paths))
		{
			return;
		}

		foreach ($paths as $i => $path)
		{
			$paths[$i] = trim($path, '/');
		}

		$access = [1];

		if (auth()->user())
		{
			$access = auth()->user()->getAuthorisedViewLevels();
		}

		$pages = Page::query()
			->whereIn('path', $paths)
			->whereIn('access', $access)
			->get();

		foreach ($pages as $page)
		{
			if ((!auth()->user() || !auth()->user()->can('manage pages')) && !$page->isPublished())
			{
				continue;
			}

			$event->addSection(
				route('page', ['uri' => $page->path]),
				$page->title
			);
		}
	}
}
