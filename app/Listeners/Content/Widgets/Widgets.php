<?php
namespace App\Listeners\Content\Widgets;

use App\Modules\Pages\Events\PageContentIsRendering;
use App\Modules\Pages\Events\PageContentBeforeDisplay;
use App\Modules\Pages\Events\PageContentAfterDisplay;
use App\Modules\Pages\Events\PageTitleAfterDisplay;

/**
 * Content listener for Widgets
 */
class Widgets
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen(PageContentIsRendering::class, self::class . '@handlePageContentIsRendering');
	}

	/**
	 * Plugin that loads module positions within content
	 *
	 * @param   PageContentIsRendering  $event
	 * @return  void
	 */
	public function handlePageContentIsRendering(PageContentIsRendering $event)
	{
		$content = $event->getBody();

		// simple performance check to determine whether bot should process further
		if (strpos($content, '@widget') === false)
		{
			return;
		}

		// expression to search for
		$regex = "/@widget\(([^\)]*)\)/i";

		// find all instances of plugin and put in $matches
		preg_match_all($regex, $content, $matches, PREG_SET_ORDER);

		if ($matches)
		{
			foreach ($matches as $match)
			{
				$position = strtolower(trim($match[1]));
				$position = trim($position, '"\'');

				$text = app('widget')->byPosition($position);
				$text = $text ?: '';

				$content = str_replace($match[0], $text, $content);
			}
		}

		$event->setBody($content);
	}
}
