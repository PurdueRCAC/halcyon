<?php
namespace App\Listeners\Content\AssetPath;

use App\Modules\Pages\Events\PageContentIsRendering;

/**
 * Content listener for asset paths
 */
class AssetPath
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
	 * Prepend file paths in content with the site's base path
	 *
	 * @param   object  $event  PageContentIsRendering
	 * @return  void
	 */
	public function handlePageContentIsRendering(PageContentIsRendering $event)
	{
		// Target image elements as iframes can also have a `src` attribute
		$content = preg_replace_callback('/<img([^>]+)>/i', function($match)
		{
			$html = preg_replace_callback('/\ssrc="(.*?)"/i', function($matches)
			{
				if (substr($matches[1], 0, 4) == 'http')
				{
					return ' src="' . $matches[1] . '"';
				}

				if (substr($matches[1], 0, 7) == '/files/')
				{
					$matches[1] = substr($matches[1], 7);
				}

				if (substr($matches[1], 0, 6) == 'files/')
				{
					$matches[1] = substr($matches[1], 6);
				}

				return ' src="' . asset("files/" . ltrim($matches[1], '/')) . '"';
			}, $match[0]);

			return $html;
		}, $event->getBody());
		$content = preg_replace('/ src="\/include\/images\/(.*?)"/i', ' src="' . asset("files/$1") . '"', $content);

		$content = preg_replace('/href="\/(.*?)"/i', 'href="' . url("$1") . '"', $content);

		$event->setBody($content);
	}
}
