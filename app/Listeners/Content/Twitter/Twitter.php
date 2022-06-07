<?php
namespace App\Listeners\Content\Twitter;

use App\Modules\Pages\Events\PageMetadata;
use App\Modules\Knowledge\Events\PageMetadata as KnowledgeMetadata;
use App\Modules\News\Events\ArticleMetadata;
use App\Modules\Resources\Events\AssetDisplaying;
use Illuminate\Config\Repository;
use Illuminate\Support\Str;

/**
 * Content Plugin class for Twitter meta tags
 */
class Twitter
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
		$events->listen(KnowledgeMetadata::class, self::class . '@handlePageMetadata');
		$events->listen(ArticleMetadata::class, self::class . '@handlePageMetadata');
		$events->listen(AssetDisplaying::class, self::class . '@handleAssetDisplaying');
	}

	/**
	 * Event after content has been displayed
	 *
	 * @param   PageMetadata|KnowledgeMetadata|ArticleMetadata  $event
	 * @return  string
	 */
	public function handlePageMetadata($event)
	{
		if (!app()->has('isAdmin')
		 || app()->get('isAdmin'))
		{
			return;
		}

		$page = $event->page;

		$event->page = $this->buildTags($page, [
			'title' => $page->title,
			'description' => ($page->formattedBody ? $page->formattedBody : $page->body)
		]);
	}

	/**
	 * Event after content has been displayed
	 *
	 * @param   AssetDisplaying  $event
	 * @return  string
	 */
	public function handleAssetDisplaying($event)
	{
		if (!app()->has('isAdmin')
		 || app()->get('isAdmin'))
		{
			return;
		}

		$asset = $event->asset;

		$event->asset = $this->buildTags($asset, [
			'title' => $asset->name,
			'description' => $asset->description,
			'image' => $asset->picture
		]);
	}

	/**
	 * Build meta tags
	 *
	 * @param   object $page
	 * @param   array  $attrs
	 * @return  string
	 */
	protected function buildTags($page, $attrs = array())
	{
		$params = new Repository(config('listener.content.twitter', []));

		foreach (['title', 'image', 'description'] as $key)
		{
			if (!isset($attrs[$key]))
			{
				$attrs[$key] = null;
			}
		}

		$tags = array();

		// Title
		$tags['twitter:title'] = htmlspecialchars(Str::limit(strip_tags($attrs['title']), 40));

		// Type
		$tags['twitter:card'] = $params->get('type', isset($attrs['type']) ? $attrs['type'] : 'summary');

		// Image
		if ($img = $params->get('image', $attrs['image']))
		{
			if (substr($img, 0, 4) != 'http')
			{
				$img = url($img);
			}
			$tags['twitter:image'] = htmlspecialchars($img);
		}
		else
		{
			// Try to find image in article
			$img = 0;
			$content = $attrs['description'];

			preg_match('/< *img[^>]*src *= *["\']?([^"\']*)/i', $content, $src);
			if (isset($src[1]) && $src[1] != '')
			{
				if (substr($src[1], 0, 4) != 'http')
				{
					$tags['twitter:image'] = url('/') . '/' .  htmlspecialchars($src[1]);
				}
				else
				{
					$tags['twitter:image'] = htmlspecialchars($src[1]);
				}

				$img = 1;
			}

			// Try to find image in images/twitter folder
			if (!$img)
			{
				if ($page->id)
				{
					$imgPath = '';
					$path = storage_path() . '/twitter/';
					if (file_exists($path . '/' . (int)$page->id . '.jpg'))
					{
						$imgPath = asset('files/twitter/' . (int)$page->id . '.jpg');
					}
					else if (file_exists($path . '/' . (int)$page->id . '.png'))
					{
						$imgPath = asset('files/twitter/' . (int)$page->id . '.png');
					}
					else if (file_exists($path . '/' . (int)$page->id . '.gif'))
					{
						$imgPath = asset('files/twitter/' . (int)$page->id . '.gif');
					}

					if ($imgPath != '')
					{
						$tags['twitter:image'] = $imgPath;
					}
				}
			}
		}

		// URL
		if ($url = $params->get('url', url()->current()))
		{
			$tags['twitter:url'] = htmlspecialchars($url);
		}

		// Site Name
		if ($sitename = $params->get('site_name', config('app.name')))
		{
			$tags['twitter:site'] = '@' . htmlspecialchars($sitename);
		}

		// Description
		$desc = $page->metadesc;
		$desc = $desc ?: $params->get('description');
		if (!$desc)
		{
			// Clean up cases where content may be just encoded whitespace
			$content = str_replace(['&amp;', '&nbsp;'], ['&', ' '], $attrs['description']);
			$content = strip_tags($content);
			$content = str_replace(array("\n", "\t", "\r"), ' ', $content);
			$content = preg_replace("/\s+/", ' ', $content);
			$content = Str::limit($content, 140);
			$content = trim($content);
			if (!$content)
			{
				$content = $attrs['title'];
			}
			$desc = $content;
		}

		if ($desc)
		{
			$page->metadesc = $desc;
			$tags['twitter:description'] = htmlspecialchars($desc);
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

		if (!$page->metadata)
		{
			$page->metadata = new Repository;
		}
		$page->metadata->set('twitter_comment', '<!-- Twitter -->');
		foreach ($tags as $key => $val)
		{
			$page->metadata->set($key, '<meta name="' . e($key) . '" content="' . e($val) . '" />');
		}

		return $page;
	}
}
