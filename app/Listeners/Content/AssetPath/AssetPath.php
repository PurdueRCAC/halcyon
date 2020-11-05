<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Listeners\Content\AssetPath;

use App\Modules\Pages\Events\PageContentIsRendering;

/**
 * Content listener for Widgets
 */
class AssetPath
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

		$article->current->content = preg_replace('/src="(.*?)"/i', 'src="' . asset("storage/$1") . '"', $article->current->content);
	}*/

	/**
	 * Plugin that loads module positions within content
	 *
	 * @param   string   $context  The context of the content being passed to the plugin.
	 * @param   object   $article  The article object.  Note $article->text is also available
	 * @return  void
	 */
	public function handlePageContentIsRendering(PageContentIsRendering $event)
	{
		$content = preg_replace('/src="(.*?)"/i', 'src="' . asset("files/$1") . '"', $event->getBody());
		$content = preg_replace('/src="\/include\/images\/(.*?)"/i', 'src="' . asset("files/$1") . '"', $content);

		$content = preg_replace('/href="\/(.*?)"/i', 'href="' . url("$1") . '"', $content);

		$event->setBody($content);
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
	}
}
