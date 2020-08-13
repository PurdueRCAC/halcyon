<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Listeners\Resources\Pages;

use App\Modules\Resources\Events\AssetDisplaying;
use App\Modules\Pages\Models\Page;

/**
 * Content listener for Resources
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
	 * Plugin that loads module positions within content
	 *
	 * @param   string   $context  The context of the content being passed to the plugin.
	 * @param   object   $article  The article object.  Note $article->text is also available
	 * @return  void
	 */
	public function handleAssetDisplaying(AssetDisplaying $event)
	{
		$ids = config()->get('listener.resources.pages.display', [8]);

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
