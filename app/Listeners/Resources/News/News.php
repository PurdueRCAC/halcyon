<?php
namespace App\Listeners\Resources\News;

use App\Modules\Status\Events\StatusRetrieval;
use App\Modules\Resources\Events\AssetDisplaying;
use App\Modules\News\Models\Type;
use App\Modules\News\Models\Article;
use App\Modules\News\Models\Newsresource;
use Carbon\Carbon;

/**
 * News listener for Resources
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
		$events->listen(AssetDisplaying::class, self::class . '@handleAssetDisplaying');
		$events->listen(StatusRetrieval::class, self::class . '@handleStatusRetrieval');
	}

	/**
	 * Plugin that loads module positions within content
	 *
	 * @param   AssetDisplaying  $event
	 * @return  void
	 */
	public function handleAssetDisplaying(AssetDisplaying $event)
	{
		if (app()->has('isAdmin') && app()->get('isAdmin'))
		{
			return;
		}

		app('translator')->addNamespace('listener.resources.news', __DIR__ . '/lang');

		$event->addSection(
			route('site.news.type', ['name' => $event->getAsset()->name]),
			trans('listener.resources.news::news.outages')
		);
	}

	/**
	 * Plugin that loads module positions within content
	 *
	 * @param   StatusRetrieval  $event
	 * @return  void
	 */
	public function handleStatusRetrieval(StatusRetrieval $event)
	{
		$resource = $event->asset;

		$a = (new Article)->getTable();
		$r = (new Newsresource)->getTable();

		$now = Carbon::now()->toDateTimeString();

		$news = Article::query()
			->select($a . '.*')
			->join($r, $r . '.newsid', $a . '.id')
			->wherePublished()
			->where($a . '.newstypeid', '=', 1)
			->where($a . '.template', '=', 0)
			->where($a . '.datetimenews', '<', $now)
			->where(function($where) use ($now, $a)
			{
				$where->whereNull($a . '.datetimenewsend')
					->orWhere($a . '.datetimenewsend', '=', '0000-00-00 00:00:00')
					->orWhere($a . '.datetimenewsend', '>', $now);
			})
			->where($r . '.resourceid', '=', $resource->id)
			->orderBy($a . '.datetimenews', 'desc')
			->get();

		$statusUpdate = null;

		foreach ($news as $article)
		{
			if (!$statusUpdate)
			{
				$statusUpdate = $article->datetimenews;
			}

			foreach ($article->updates as $update)
			{
				$statusUpdate = ($update->datetimecreated > $statusUpdate) ? $update->datetimecreated : $statusUpdate;
			}

			$resource->isHappening = true;

			//$thisnews[] = $article;
			//echo $article->datetimenews . ' ' . $article->datetimenewsend . ' - ' . $article->id . '<br />';
			$resource->status = 'maintenance';

			if (stristr($article->headline, 'issue')
				|| stristr($article->headline, 'unavailable')
				|| stristr($article->headline, 'outage'))
			{
				$resource->status = 'down';
			}
			break;
		}

		$resource->news = $news;

		if ($statusUpdate)
		{
			$resource->statusUpdate = $statusUpdate;
		}

		$event->asset = $resource;
	}
}
