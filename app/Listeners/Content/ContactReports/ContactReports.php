<?php
namespace App\Listeners\Content\ContactReports;

use App\Modules\ContactReports\Events\ReportPrepareContent;
use App\Modules\ContactReports\Events\CommentPrepareContent;
use App\Modules\News\Events\ArticlePrepareContent;
use App\Modules\News\Events\UpdatePrepareContent;
use App\Modules\Pages\Events\PageContentIsRendering;

/**
 * Content listener for Contact Reports
 */
class ContactReports
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

		//$root  = app('request')->root();
		$regex = '(contact|CRM?)(\s+report)?\s*#?(\d+)';
		$replc = '<a href="' . route('site.contactreports.show', ['id' => "$3"]) . '">Contact Report #$3</a>';

		$content = preg_replace("/$regex/i", $replc, $content);

		$event->setBody($content);
	}
}
