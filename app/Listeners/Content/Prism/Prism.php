<?php
namespace App\Listeners\Content\Prism;

use App\Modules\Pages\Events\PageContentIsRendering;

/**
 * Prism Syntax Highlighter
 */
class Prism
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
	}

	/**
	 * Display the editor area.
	 *
	 * @param   PageContentIsRendering  $event
	 * @return  void
	 */
	public function handle(PageContentIsRendering $event)
	{
		app('view')->addNamespace(
			'listener.content.prism',
			__DIR__ . '/views'
		);

		$content  = $event->getBody();
		$content .= view('listener.content.prism::prism');

		$event->setBody($content);
	}
}
