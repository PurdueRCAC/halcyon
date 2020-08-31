<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Listeners\Content\OpenGraph;

use App\Modules\Pages\Events\PageMetadata;
use Illuminate\Config\Repository;

/**
 * Content Plugin class for OpenGraph meta tags
 *
 * Inspired by work from Jan Pavelka (www.phoca.cz)
 */
class OpenGraph
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen(PageMetadata::class, self::class . '@handlePageMetadata');
	}

	/**
	 * Event after content has been displayed
	 *
	 * @param   object  $page  The article object. Note $page->text is also available
	 * @return  string
	 */
	public function handlePageMetadata(PageMetadata $event)
	{
		if (!app()->has('isAdmin')
		 || app()->get('isAdmin'))
		{
			return;
		}

		$page = $event->page;
		$params = new Repository(config('listeners.content.opengraph', []));

		// We need help variables as we cannot change the $page variable - such then will influence global settings
		$thisDesc  = $page->metadesc;
		$tags = array();

		// Title
		if ($title = $params->get('title', $page->title))
		{
			$tags['og:title'] = htmlspecialchars($title);
		}

		// Type
		$tags['og:type'] = $params->get('type', 'article');

		// Image
		if ($img = $params->get('image'))
		{
			$tags['og:image'] = url('/') . htmlspecialchars($img);
		}
		else
		{
			// Try to find image in article
			$img = 0;
			$content = $page->content;

			preg_match('/< *img[^>]*src *= *["\']?([^"\']*)/i', $content, $src);
			if (isset($src[1]) && $src[1] != '')
			{
				$tags['og:image'] = url('/') . '/' .  htmlspecialchars($src[1]);
				$img = 1;
			}

			// Try to find image in images/opengraph folder
			if (!$img)
			{
				if ($page->id)
				{
					$imgPath = '';
					$path = storage_path() . '/opengraph/';
					if (file_exists($path . '/' . (int)$page->id . '.jpg'))
					{
						$imgPath = asset('files/opengraph/' . (int)$page->id . '.jpg');
					}
					else if (file_exists($path . '/' . (int)$page->id . '.png'))
					{
						$imgPath = asset('files/opengraph/' . (int)$page->id . '.png');
					}
					else if (file_exists($path . '/' . (int)$page->id . '.gif'))
					{
						$imgPath = asset('files/opengraph/' . (int)$page->id . '.gif');
					}

					if ($imgPath != '')
					{
						$tags['og:image'] = $imgPath;
					}
				}
			}
		}

		// URL
		if ($url = $params->get('url', url()->current()))
		{
			$tags['og:url'] = htmlspecialchars($url);
		}

		// Site Name
		if ($sitename = $params->get('site_name', config('app.name')))
		{
			$tags['og:site_name'] = htmlspecialchars($sitename);
		}

		// Description
		$thisDesc ?: $params->get('description');
		if ($thisDesc)
		{
			$tags['og:description'] = htmlspecialchars($desc);
		}

		// FB App ID - COMMON
		if ($app_id = $params->get('app_id'))
		{
			$tags['fb:app_id'] = htmlspecialchars($app_id);
		}

		// Other
		if ($other = $params->get('other'))
		{
			$other = explode (';', $other);
			if (!empty($other))
			{
				foreach ($other as $v)
				{
					if ($v != '')
					{
						$vother = explode('=', $v);
						if (!empty($vother))
						{
							if (isset($vother[0]) && isset($vother[1]))
							{
								$tags[htmlspecialchars(strip_tags($vother[0]))] = htmlspecialchars($vother[1]);
							}
						}
					}
				}
			}
		}

		foreach ($tags as $key => $val)
		{
			$page->metadata->set($key, $val);
		}

		$event->page = $page;

		//return View::make('listeners.content.opengraph::metadata', ['tags' => $tags])->render();
	}
}
