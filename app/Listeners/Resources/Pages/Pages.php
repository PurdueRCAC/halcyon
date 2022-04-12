<?php
namespace App\Listeners\Resources\Pages;

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
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen(AssetDisplaying::class, self::class . '@handleAssetDisplaying');
	}

	/**
	 * Find pages to be listed on a resource Asset's overview
	 *
	 * @param   AssetDisplaying  $event
	 * @return  void
	 */
	public function handleAssetDisplaying(AssetDisplaying $event)
	{
		if (app()->has('isAdmin') && app()->get('isAdmin'))
		{
			return;
		}

		$ids = config()->get('listener.resources.pages.display', [8]); // 8 = "Help" page

		if (empty($ids))
		{
			return;
		}

		$access = [1];

		if (auth()->user())
		{
			$access = auth()->user()->getAuthorisedViewLevels();
		}

		$pages = Page::query()
			->whereIn('id', $ids)
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
