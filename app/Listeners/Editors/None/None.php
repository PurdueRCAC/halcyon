<?php
namespace App\Listeners\Editors\None;

use App\Modules\Core\Events\EditorIsRendering;

/**
 * Plain Textarea Editor
 */
class None
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen(EditorIsRendering::class, self::class . '@handle');
	}

	/**
	 * Display the editor area.
	 *
	 * @param   EditorIsRendering  $editor
	 * @return  string
	 */
	public function handle(EditorIsRendering $editor)
	{
		$content = e($editor->getValue());

		$attr = $editor->getAttributes();
		$attr['name'] = $editor->getName();

		if (!isset($attr['cols']))
		{
			$attr['cols'] = 35;
		}

		if (!isset($attr['rows']))
		{
			$attr['rows'] = 5;
		}

		if (!isset($attr['id']))
		{
			$attr['id'] = str_replace(['[', ']'], ['-', ''], $attr['name']);
		}

		if (!isset($attr['class']))
		{
			$attr['class'] = '';
		}
		$attr['class'] .= ' form-control ckeditor';
		$attr['class'] = trim($attr['class']);

		$attributes = '';
		foreach ($attr as $k => $v)
		{
			$attributes .= ' ' . $k . '="' . e($v) . '"';
		}

		$editor->setContent("<textarea $attributes>$content</textarea>");

		return false;
	}
}
