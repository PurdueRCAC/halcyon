<?php
namespace App\Listeners\Content\FootPrints;

use App\Modules\ContactReports\Events\ReportPrepareContent;
use App\Modules\ContactReports\Events\CommentPrepareContent;
use App\Modules\News\Events\ArticlePrepareContent;
use App\Modules\News\Events\UpdatePrepareContent;
use App\Modules\Pages\Events\PageContentIsRendering;

/**
 * Content listener for FootPrints
 */
class FootPrints
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
	 * Handle content rendering
	 *
	 * @param   object  $event
	 * @return  void
	 */
	public function handle($event)
	{
		$content = $event->getBody();

		$regex = '((foot\s*prints?)|(FP))(\s+ticket)?\s*#?(\d+)';
		$replc = "<a target=\"_blank\" rel=\"noopener noreferrer\" href=\"https://support.purdue.edu/MRcgi/MRlogin.pl?DL=$5DA17\">Footprints #$5</a>";

		$content = preg_replace("/$regex/i", $replc, $content);

		$event->setBody($content);
	}
}
