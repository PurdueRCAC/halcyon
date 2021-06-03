<?php
namespace App\Listeners\Content\News;

use App\Modules\Pages\Events\PageContentIsRendering;
use App\Modules\News\Models\Article;
use App\Modules\News\Events\ArticlePrepareContent;
use App\Modules\News\Events\UpdatePrepareContent;

/**
 * Content listener for News
 */
class News
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen(PageContentIsRendering::class, self::class . '@handle');

		// News
		$events->listen(ArticlePrepareContent::class, self::class . '@handle');
		$events->listen(UpdatePrepareContent::class, self::class . '@handle');
	}

	/**
	 * Plugin that loads module positions within content
	 *
	 * @param   object   $event  The article object.  Note $article->text is also available
	 * @return  void
	 */
	public function handle($event)
	{
		$content = $event->getBody();

		$content = preg_replace_callback("/(news)\s*(story|item)?\s*#?(\d+)(\{.+?\})?/i", array($this, 'matchNews'), $content);

		$event->setBody($content);
	}

	/**
	 * Match news
	 *
	 * @param   array  $match
	 * @return  string
	 */
	private function matchNews($match)
	{
		$title = 'News Story #' . $match[3];

		$article = Article::find($match[3]);

		if ($article)
		{
			$title = $article->headline;
		}

		if (isset($match[4]))
		{
			$title = preg_replace("/[{}]+/", '', $match[4]);
		}

		return '<a href="' . route('site.news.show', ['id' => $match[3]]) . '">' . $title . '</a>';
	}
}
