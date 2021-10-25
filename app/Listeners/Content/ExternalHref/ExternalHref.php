<?php
namespace App\Listeners\Content\ExternalHref;

use App\Modules\ContactReports\Events\ReportPrepareContent;
use App\Modules\ContactReports\Events\CommentPrepareContent;
use App\Modules\News\Events\ArticlePrepareContent;
use App\Modules\News\Events\UpdatePrepareContent;
use App\Modules\Pages\Events\PageContentIsRendering;
use Illuminate\Support\Fluent;

/**
 * External HREF processor
 */
class ExternalHref
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		// Contact Reports
		$events->listen(ReportPrepareContent::class, self::class . '@handle');
		$events->listen(CommentPrepareContent::class, self::class . '@handle');
		// News
		$events->listen(ArticlePrepareContent::class, self::class . '@handle');
		$events->listen(UpdatePrepareContent::class, self::class . '@handle');
		// Pages
		$events->listen(PageContentIsRendering::class, self::class . '@handle');
	}

	/**
	 * Prepare external links
	 *
	 * @param   object  $event
	 * @return  void
	 */
	public function handle($event)
	{
		$content = $event->getBody();
		$content = ltrim($content);

		if (!$content)
		{
			return;
		}

		$params = new Fluent(config()->get('listener.externalhref', []));

		$mode   = $params->get('mode');
		$target = $params->get('target');
		$classes = array();

		if ($cls = $params->get('classes'))
		{
			$classes = explode(',', preg_replace('/\s*/', '', $cls));
			$classes = array_map('strtolower', $classes);
		}

		//$content = preg_replace_callback('/<a\s[^>]*href\s*=\s*(?:[\"\']??)(http[^\\1 >]*?)\\1[^>]*>(.*)<\/a>/uiUs', array(&$this, 'nofollow'), $content);

		$tags = array(
			'a'    => '/<a\s+([^>]*)>/i',
			'area' => '/<area\s+([^>]*)>/i'
		);
		foreach ($tags as $tag => $pattern)
		{
			$links = array();
			preg_match_all($pattern, $content, $links, PREG_SET_ORDER);

			foreach ($links as $link)
			{
				// Get attributes
				$pattern = "/(\w+)(\s*=\s*(?:\".*?\"|'.*?'|[^'\">\s]+))?/i";
				$attribs = array();
				preg_match_all($pattern, $link[1], $attribs, PREG_SET_ORDER);

				$list = array();
				foreach ($attribs as $attrib)
				{
					if (!isset($attrib[2]))
					{
						// something wrong, may be js in email cloaking plugin
						continue;
					}

					$att = strtolower(trim($attrib[1]));
					$list[$att] = preg_replace("/=\s*[\"']?([^'\"]*)[\"']?/", "$1", $attrib[2]);
					$list[$att] = trim($list[$att]);
				}

				// Skip if non http link or anchor
				if (!isset($list['href']))
				{
					continue;
				}

				if (stripos($list['href'], 'http') !== 0)
				{
					continue;
				}

				$href = preg_replace("/https?:\/\//i", '', $list['href']);

				// Skip if internal link
				if (isset($_SERVER['SERVER_NAME']) && stripos($href, $_SERVER['SERVER_NAME']) === 0)
				{
					continue;
				}

				// Get classes
				if (!empty($list['class']))
				{
					$linkClasses = preg_split('/\s+/', $list['class']);
					$linkClasses = array_map('strtolower', $linkClasses);
				}
				else
				{
					$linkClasses = array();
				}

				if (array_intersect($linkClasses, $classes))
				{
					// Link classes are present in the ignored classes list
					continue;
				}

				if ($mode == 0 && !isset($list['rel']))
				{
					$list['rel'] = 'nofollow';
				}
				else if ($mode == 1)
				{
					$list['rel'] = 'nofollow';
				}
				else if ($mode == 2)
				{
					unset($list['rel']);
				}

				if ($target == 0 && !isset($list['target']))
				{
					$list['target'] = '_blank';
					$list['rel'] = (isset($list['rel']) ? $list['rel'] . ' ' : '') . 'noreferrer';
				}
				else if ($target == 1)
				{
					$list['target'] = '_blank';
					$list['rel'] = (isset($list['rel']) ? $list['rel'] . ' ' : '') . 'noreferrer';
				}
				else if ($target == 2)
				{
					$list['target'] = '_parent';
				}

				$ahref = "<$tag ";
				foreach ($list as $k => $v)
				{
					$ahref .= "{$k}=\"{$v}\" ";
				}
				$ahref .= '>';

				$content = str_replace($link[0], $ahref, $content);
			}
		}

		$event->setBody($content);
	}

	/**
	 * Create a link tag with specified attributes for external links
	 *
	 * @param   array   $matches
	 * @return  string
	 */
	/*private function nofollow($matches)
	{
		static $rel;

		if (!$rel)
		{
			$rel = array('external');
			if ($this->params->get('nofollow', 1))
			{
				$rel[] = 'nofollow';
			}
			if ($this->params->get('noreferrer', 0))
			{
				$rel[] = 'noreferrer';
			}
			if ($other = $this->params->get('other'))
			{
				$other = explode(' ', $other);
				$other = array_map('trim', $other);
				$rel = array_merge($rel, $other);
			}
			$rel = array_filter($rel);
			$rel = array_unique($rel);
			$rel = implode(' ', $rel);
		}

		return '<a href="' . $matches[1] . '" rel="' . $rel . '">' . $matches[2] . '</a>';
	}*/
}
