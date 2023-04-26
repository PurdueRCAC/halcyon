<?php
namespace App\Listeners\Content\Widgets;

use Illuminate\Events\Dispatcher;
use App\Modules\Pages\Events\PageContentIsRendering;
use App\Modules\Knowledge\Events\PageContentIsRendering as KbContentIsRendering;

/**
 * Content listener for Widgets
 */
class Widgets
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Dispatcher  $events
	 * @return void
	 */
	public function subscribe(Dispatcher $events): void
	{
		$events->listen(PageContentIsRendering::class, self::class . '@handlePageContentIsRendering');
		$events->listen(KbContentIsRendering::class, self::class . '@handlePageContentIsRendering');
	}

	/**
	 * Plugin that loads module positions within content
	 *
	 * @param   PageContentIsRendering|KbContentIsRendering  $event
	 * @return  void
	 */
	public function handlePageContentIsRendering($event): void
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
