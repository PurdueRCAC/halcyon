<?php
namespace App\Listeners\Search\News;

use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\DB;
use App\Modules\News\Models\Article;
use App\Modules\News\Models\Type;
use App\Modules\News\Models\Stemmedtext;
use App\Halcyon\Utility\PorterStemmer;
use App\Modules\Search\Events\Searching;
use App\Modules\Search\Models\Result;

/**
 * Search listener
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
		$events->listen(Searching::class, self::class . '@handle');
	}

	/**
	 * Add records to search results
	 *
	 * @param   Searching $event
	 * @return  void
	 */
	public function handle(Searching $event): void
	{
		if (!$event->search)
		{
			return;
		}

		$n = (new Article)->getTable();

		$query = Article::query()
			->select($n . '.*')
			->with('type')
			->where($n . '.template', '=', 0);

		$keywords = explode(' ', $event->search);

		$from_sql = array();
		foreach ($keywords as $keyword)
		{
			// Trim extra garbage
			$keyword = preg_replace('/[^A-Za-z0-9]/', ' ', $keyword);

			// Calculate stem for the word
			$stem = PorterStemmer::stem($keyword);
			$stem = substr($stem, 0, 1) . $stem;

			$from_sql[] = "+" . $stem;
		}

		$s = (new Stemmedtext)->getTable();

		$query->join($s, $s . '.id', $n . '.id');
		$query->select($n . '.*', DB::raw("(MATCH($s.stemmedtext) AGAINST ('" . implode(' ', $from_sql) . "') * 10 + 2 * (1 / (ABS(DATEDIFF(NOW(), $n.datetimenews)) + 1))) AS weight"));
		$query->whereRaw("MATCH($s.stemmedtext) AGAINST ('" . implode(' ', $from_sql) . "' IN BOOLEAN MODE)");
		$rows = $query->where($n . '.published', '=', 1)
			->orderBy($event->order, $event->order_dir)
			->limit($event->limit * $event->page)
			->get();

		foreach ($rows as $page)
		{
			$event->add(new Result(
				trans('news::news.news'),
				$page->weight,
				$page->headline,
				$page->toHtml(),
				route('site.news.show', ['id' => $page->id]),
				$page->datetimecreated,
				$page->datetimeedited
			));
		}
	}
}
