<?php
namespace App\Widgets\Feed;

use App\Modules\Widgets\Entities\Widget;
use stdClass;

/**
 * Widget for displaying a feed
 */
class Feed extends Widget
{
	/**
	 * Display module
	 *
	 * @return  null|\Illuminate\View\View
	 */
	public function run()
	{
		$rssurl = $this->params->get('rssurl');
		$rssrtl = $this->params->get('rssrtl', 0);

		// Check if feed URL has been set
		if (empty($rssurl))
		{
			return view($this->getViewName('error'), [
				'message' => trans('widget.feed::feed.error.no url')
			]);
		}

		$feed = $this->getFeed($rssurl);
		$clss = htmlspecialchars($this->params->get('moduleclass_sfx'));

		$layout = $this->params->get('layout');
		$layout = $layout ?: 'index';

		return view($this->getViewName($layout), [
			'feed'   => $feed,
			'params' => $this->params,
			'clss'   => $clss
		]);
	}

	/**
	 * Get contents of a feed
	 *
	 * @param   string  $rssurl
	 * @return  bool|stdClass
	 */
	public function getFeed($rssurl)
	{
		// Get RSS parsed object
		$cache_time = 0;
		if ($this->params->get('cache'))
		{
			// The cache_time will get fed into Cache to initiate the feed_parser cache group and eventually
			// Cache Storage will multiply the value by 60 and use that for its lifetime. The only way to sync
			// the feed_parser cache (which caches with an empty dataset anyway) with the widget cache is to
			// first divide the widget's cache time by 60 then inject that forward, which once stored into the
			// Cache Storage object, will be the correct value in minutes.
			$cache_time = $this->params->get('cache_time', 15) / 60;
		}

		$rssDoc = new \SimplePie();
		//$rssDoc->set_cache_location($cache);
		//$rssDoc->set_cache_duration($app['config']->get('cachetime', 15));
		$rssDoc->enable_cache(false);
		$rssDoc->force_feed(true);
		$rssDoc->set_feed_url($rssurl);
		$rssDoc->set_cache_duration($cache_time);
		$rssDoc->init();

		$feed = new stdClass;

		if ($rssDoc != false)
		{
			// Channel header and link
			$feed->title        = $rssDoc->get_title();
			$feed->link         = $rssDoc->get_link();
			$feed->description  = $rssDoc->get_description();

			// Channel image if exists
			$feed->image = new stdClass;
			$feed->image->url   = $rssDoc->get_image_url();
			$feed->image->title = $rssDoc->get_image_title();

			// Items
			$items = $rssDoc->get_items();

			// Feed elements
			$feed->items = array_slice($items, 0, $this->params->get('rssitems', 5));
		}
		else
		{
			$feed = false;
		}

		return $feed;
	}
}
