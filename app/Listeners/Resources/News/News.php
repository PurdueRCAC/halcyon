<?php
namespace App\Listeners\Resources\News;

use Illuminate\Events\Dispatcher;
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
	 * @param  Dispatcher  $events
	 * @return void
	 */
	public function subscribe(Dispatcher $events): void
	{
		$events->listen(AssetDisplaying::class, self::class . '@handleAssetDisplaying');
		$events->listen(StatusRetrieval::class, self::class . '@handleStatusRetrieval');
	}

	/**
	 * Find news to be listed on a resource Asset's overview
	 *
	 * @param   AssetDisplaying  $event
	 * @return  void
	 */
	public function handleAssetDisplaying(AssetDisplaying $event): void
	{
		if (app()->has('isAdmin') && app()->get('isAdmin'))
		{
			return;
		}

		app('translator')->addNamespace('listener.resources.news', __DIR__ . '/lang');

		$a = (new Article)->getTable();
		$r = (new Newsresource)->getTable();

		$now = Carbon::now()->toDateTimeString();

		$news = Article::query()
			->select($a . '.*')
			->join($r, $r . '.newsid', $a . '.id')
			->wherePublished()
			->where($a . '.newstypeid', '=', 1) // Outages and Maintenance
			->where($a . '.template', '=', 0)
			->where(function($where) use ($now, $a)
			{
				$where->whereNull($a . '.datetimenewsend')
					->orWhere($a . '.datetimenewsend', '>', $now);
			})
			->where($r . '.resourceid', '=', $event->getAsset()->id)
			->orderBy($a . '.datetimenews', 'desc')
			->get();

		$content = null;

		if (count($news) > 0)
		{
			$content = view('news::site.list', [
				'articles' => $news
			]);
		}

		$event->addSection(
			route('site.news.type', ['name' => $event->getAsset()->name]),
			trans('listener.resources.news::news.outages'),
			false,
			$content
		);
	}

	/**
	 * Look for outage events for a specific resource
	 *
	 * @param   StatusRetrieval  $event
	 * @return  void
	 */
	public function handleStatusRetrieval(StatusRetrieval $event): void
	{
		$resource = $event->asset;

		// If we have a manually set status
		if ($resource->status)
		{
			// Stop here
			$resource->news = array();
			$event->asset = $resource;
			return;
		}

		$a = (new Article)->getTable();
		$r = (new Newsresource)->getTable();

		$now = Carbon::now()->toDateTimeString();

		$news = Article::query()
			->select($a . '.*')
			->join($r, $r . '.newsid', $a . '.id')
			->wherePublished()
			->where($a . '.newstypeid', '=', 1) // Outages and Maintenance
			->where($a . '.template', '=', 0)
			->where($a . '.datetimenews', '<', $now)
			->where(function($where) use ($now, $a)
			{
				$where->whereNull($a . '.datetimenewsend')
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

			$resource->status = 'maintenance';

			if (stristr($article->headline, 'issue')
			|| stristr($article->headline, 'unavailable')
			|| stristr($article->headline, 'outage'))
			{
				$resource->status = 'down';
				break;
			}
		}

		$resource->news = $news;

		$event->updated_at = $statusUpdate;
		$event->asset = $resource;
	}
}
