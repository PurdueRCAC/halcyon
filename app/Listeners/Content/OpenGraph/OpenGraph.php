<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Listeners\Content\OpenGraph;

use App\Modules\Pages\Events\PageMetadata;

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
	public function handlePageMetadata($page)
	{
		if (!app()->has('isAdmin')
		 || app()->get('isAdmin'))
		{
			return;
		}

		// We need help variables as we cannot change the $page variable - such then will influence global settings
		$suffix    = '';
		$thisDesc  = $page->metadesc;
		$thisTitle = $page->title;
		$tags = array();

		// Title
		if ($title = $page->params->get('title' . $suffix, $thisTitle))
		{
			$tags['og:title'] = htmlspecialchars($title);
		}

		// Type
		$tags['og:type'] = $page->params->get('type' . $suffix, 'article');

		// Image
		if ($img = $page->params->get('image' . $suffix, ''))
		{
			$tags['og:image'] = url('/') . htmlspecialchars($img);
		}
		else
		{
			// Try to find image in article
			$img = 0;
			$fulltext = '';
			if (isset($page->fulltext) && $page->fulltext != '')
			{
				$fulltext = $page->fulltext;
			}
			$introtext = '';
			if (isset($page->introtext) && $page->introtext != '')
			{
				$fulltext = $page->introtext;
			}
			$content = $introtext . $fulltext;

			preg_match('/< *img[^>]*src *= *["\']?([^"\']*)/i', $content, $src);
			if (isset($src[1]) && $src[1] != '')
			{
				$tags['og:image'] = url('/') . htmlspecialchars($src[1]);
				$img = 1;
			}

			// Try to find image in images/opengraph folder
			if ($img == 0)
			{
				if (isset($page->id) && (int)$page->id > 0)
				{
					$imgPath = '';
					$path = storage_path() . '/images/opengraph/';
					if (file_exists($path . '/' . (int)$page->id . '.jpg'))
					{
						$imgPath = asset('images/opengraph/' . (int)$page->id . '.jpg');
					}
					else if (file_exists($path . '/' . (int)$page->id . '.png'))
					{
						$imgPath = asset('images/opengraph/' . (int)$page->id . '.png');
					}
					else if (file_exists($path . '/' . (int)$page->id . '.gif'))
					{
						$imgPath = asset('images/opengraph/' . (int)$page->id . '.gif');
					}

					if ($imgPath != '')
					{
						$tags['og:image'] = $imgPath;
					}
				}
			}
		}

		// URL
		if ($url = $page->params->get('url' . $suffix, Request::current()))
		{
			$tags['og:url'] = htmlspecialchars($url);
		}

		// Site Name
		if ($sitename = $page->params->get('site_name' . $suffix, config('app.sitename')))
		{
			$tags['og:site_name'] = htmlspecialchars($sitename);
		}

		// Description
		if ($desc = $page->params->get('description' . $suffix, $thisDesc))
		{
			$tags['og:description'] = htmlspecialchars($desc);
		}
		else if ($desc = config('app.MetaDesc'))
		{
			$tags['og:description'] = htmlspecialchars($desc);
		}

		// FB App ID - COMMON
		if ($app_id = $page->params->get('app_id', ''))
		{
			$tags['fb:app_id'] = htmlspecialchars($app_id);
		}

		// Other
		if ($other = $page->params->get('other', ''))
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

		return View::make('listeners.content.opengraph::metadata', ['tags' => $tags])->render();
	}
}
