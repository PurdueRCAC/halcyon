<?php

namespace App\Listeners\ContactReports\News;

use Illuminate\Events\Dispatcher;
use App\Modules\ContactReports\Events\ReportFrom;
use App\Modules\ContactReports\Models\User;
use App\Modules\ContactReports\Models\Report;
use App\Modules\News\Models\Article;
use Carbon\Carbon;

/**
 * News listener for contact reports
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
		$events->listen(ReportFrom::class, self::class . '@handleReportFrom');
	}

	/**
	 * Generate a contact report from an article
	 *
	 * @param   ReportFrom  $event
	 * @return  void
	 */
	public function handleReportFrom(ReportFrom $event): void
	{
		if ($event->from_type != 'news')
		{
			return;
		}

		$article = Article::find($event->from_id);

		foreach ($article->associations as $association)
		{
			$user = $association->associated;

			if (!$user)
			{
				continue;
			}

			$event->report->users->push(new User(['userid' => $association->associd]));
		}

		$event->report->userid = auth()->user()->id;
		$event->report->datetimecontact = Carbon::parse($article->datetimenews->format('Y-m-d') . ' 00:00:00');

		if ($association->comment)
		{
			$comment = explode("\n", $association->comment);
			$comment = array_map(function ($val)
			{
				return '> ' . $val;
			}, $comment);
			$comment = implode("\n", $comment);

			$event->report->report = $comment;
		}

		$event->report->contactreporttypeid = 1;
	}
}
