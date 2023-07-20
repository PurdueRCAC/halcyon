<?php
namespace App\Listeners\Search\Pages;

use Illuminate\Events\Dispatcher;
use App\Modules\Pages\Models\Page;
use App\Modules\Search\Events\Searching;
use App\Modules\Search\Models\Result;

/**
 * Search listener
 */
class Pages
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

		$rows = Page::query()
			->with('viewlevel')
			->whereState('published')
			->whereAccess([0, 1], auth()->user())
			->whereSearch($event->search)
			->limit($event->limit * $event->page)
			->get();

		foreach ($rows as $page)
		{
			$page->content = preg_replace_callback(
				'/@widget\(([^\)]+\))/',
				function ($matches)
				{
					$expression = trim($matches[1], '"\'');
					return app('widget')->byPosition($expression);
				},
				$page->content
			);

			$event->add(new Result(
				trans('pages::pages.pages'),
				$page->weight,
				$page->title,
				$page->content,
				route('page', ['uri' => $page->path]),
				$page->created_at,
				$page->updated_at
			));
		}
	}
}
