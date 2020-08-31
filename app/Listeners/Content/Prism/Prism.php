<?php
namespace App\Listeners\Content\Prism;

use App\Modules\Pages\Events\PageContentIsRendering;

/**
 * Prism Plugin
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
	 * @param   string   $name     The control name.
	 * @param   string   $content  The contents of the text area.
	 * @param   string   $id       An optional ID for the textarea (note: since 1.6). If not supplied the name is used.
	 * @param   int      $col      The number of columns for the textarea.
	 * @param   int      $row      The number of rows for the textarea.
	 * @param   array    $params   Associative array of editor parameters.
	 * @return  string
	 */
	public function handle(PageContentIsRendering $event)
	{
		app('view')->addNamespace(
			'listener.content.prism',
			__DIR__ . '/views'
		);

		$content = $event->getBody();
		$content .= view('listener.content.prism::prism');

		$event->setBody($content);

		//$editor->addJs('ckeditor.js');
		//$editor->setEditorClass('ckeditor');
		//view()->share('activeEditor', 'ckeditor');
	}
}
