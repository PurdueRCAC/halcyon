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
	 * @param   string   $name     The control name.
	 * @param   string   $content  The contents of the text area.
	 * @param   string   $id       An optional ID for the textarea (note: since 1.6). If not supplied the name is used.
	 * @param   int      $col      The number of columns for the textarea.
	 * @param   int      $row      The number of rows for the textarea.
	 * @param   array    $params   Associative array of editor parameters.
	 * @return  string
	 */
	public function handle(EditorIsRendering $editor)
	{
		$content = $editor->getValue();

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
		//return "<textarea $attributes>$content</textarea>";

		//$editor->addJs('ckeditor.js');
		//$editor->setEditorClass('ckeditor');
		//view()->share('activeEditor', 'ckeditor');
	}
}
