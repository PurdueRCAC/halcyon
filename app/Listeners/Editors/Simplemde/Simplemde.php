<?php
namespace App\Listeners\Editors\Simplemde;

use App\Modules\Core\Events\EditorIsRendering;
use Illuminate\Config\Repository;
use stdClass;

/**
 * Simplemde
 */
class Simplemde
{
	/**
	 * Number of instances, used to ensure assets only get added once
	 *
	 * @var  int
	 */
	private static $instances = 0;

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
		if ($editor->getFormatting() != 'markdown')
		{
			return;
		}

		self::$instances++;

		$content = $editor->getValue();
		$name    = $editor->getName();
		$attr    = $editor->getAttributes();

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
			$attr['id'] = str_replace(['[', ']'], ['-', ''], $name);
		}

		if (!isset($attr['class']))
		{
			$attr['class'] = '';
		}
		$attr['class'] .= ' form-control md simplemde';
		$attr['class'] = trim($attr['class']);

		$attributes = '';
		foreach ($attr as $k => $v)
		{
			$attributes .= ' ' . $k . '="' . e($v) . '"';
		}

		$cls = explode(' ', $attr['class']);
		$cls = array_map('trim', $cls);

		app('view')->addNamespace(
			'listener.editor.simplemde',
			__DIR__ . '/views'
		);

		$editor->setContent(view('listener.editor.simplemde::textarea', [
			'name'   => $name, //$editor->getName(),
			'id'     => $attr['id'],
			'value'  => $content, //$editor->getValue(),
			'atts'   => $attributes,
			'assets' => self::$instances > 1 ? false : true,
		]));

		return false;
	}
}
