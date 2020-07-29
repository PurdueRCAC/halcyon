<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University
 * @license    http://opensource.org/licenses/MIT MIT
 */

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
	 * Plugin that loads module positions within content
	 *
	 * @param   string   $context  The context of the content being passed to the plugin.
	 * @param   object   $article  The article object.  Note $article->text is also available
	 * @return  void
	 */
	/*public function handleOnContentPrepare($context, &$article)
	{
		if ($context != 'pages.article')
		{
			return;
		}

		// simple performance check to determine whether bot should process further
		if (strpos($article->content, '@widget') === false)
		{
			return;
		}

		// expression to search for
		$regex = "/@widget\(([^\)]*)\)/i";

		// find all instances of plugin and put in $matches
		preg_match_all($regex, $article->content, $matches, PREG_SET_ORDER);

		if ($matches)
		{
			foreach ($matches as $match)
			{
				$position = strtolower(trim($match[1]));
				$position = trim($position, '"\'');

				$text = app('widget')->byPosition($position);
				$text = $text ?: '(none)';

				$article->content = str_replace($match[0], $text, $article->content);
			}
		}
	}*/

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
				$text = $text ?: '(none)';

				$content = str_replace($match[0], $text, $content);
			}
		}

		$event->setBody($content);
	}

	public function handlePageContentBeforeDisplay(PageContentBeforeDisplay $event)
	{
		$content = $event->getOriginal();

		$event->setBody('Before content ... ' . $content);
	}

	public function handlePageContentAfterDisplay(PageContentAfterDisplay $event)
	{
		$content = $event->getOriginal();

		$event->setBody($content . ' ... After content');
	}

	public function handlePageTitleAfterDisplay(PageTitleAfterDisplay $event)
	{
		//$page = $event->getPage();

		$event->setContent(' ... After title');
	}

	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen(PageContentIsRendering::class, self::class . '@handlePageContentIsRendering');
		//$events->listen(PageContentBeforeDisplay::class, self::class . '@handlePageContentBeforeDisplay');
		//$events->listen(PageContentAfterDisplay::class, self::class . '@handlePageContentAfterDisplay');
		//$events->listen(PageTitleAfterDisplay::class, self::class . '@handlePageTitleAfterDisplay');
	}
}
