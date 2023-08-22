<?php
namespace App\Listeners\Editors\Ckeditor5;

use App\Modules\Core\Events\EditorIsRendering;
use Illuminate\Config\Repository;
use Illuminate\Events\Dispatcher;
use stdClass;

/**
 * CKEditor v5
 */
class Ckeditor5
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Dispatcher  $events
	 * @return void
	 */
	public function subscribe(Dispatcher $events): void
	{
		$events->listen(EditorIsRendering::class, self::class . '@handle');
	}

	/**
	 * Display the editor area.
	 *
	 * @param   EditorIsRendering  $editor
	 * @return  void|bool
	 */
	public function handle(EditorIsRendering $editor)
	{
		if ($editor->getFormatting() != 'html')
		{
			return;
		}

		if (auth()->user())
		{
			$editorsetting = auth()->user()->facet('editor');

			if ($editorsetting && $editorsetting != 'ckeditor5')
			{
				return;
			}
		}

		$content = $editor->getValue();
		$name = $editor->getName();
		$attr = $editor->getAttributes();

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
		$attr['class'] .= ' form-control ckeditor-content';
		$attr['class'] = trim($attr['class']);

		$attributes = '';
		foreach ($attr as $k => $v)
		{
			$attributes .= ' ' . $k . '="' . e($v) . '"';
		}

		$cls = explode(' ', $attr['class']);
		$cls = array_map('trim', $cls);

		$params = new Repository(config('listener.editors.ckeditor5', []));
		$params->set('class', $cls);
		$params->set('height', (18 * intval($attr['rows'])) . 'px');

		$config = json_encode($this->buildConfig($params));

		app('view')->addNamespace(
			'listener.editor.ckeditor5',
			app_path() . '/Listeners/Editors/Ckeditor5/views'
		);

		$editor->setContent(view('listener.editor.ckeditor5::textarea', [
			'name'   => $editor->getName(),
			'id'     => $attr['id'],
			'value'  => $editor->getValue(),
			'atts'   => $attributes,
			'config' => $config,
		]));

		return false;
	}

	/**
	 * Build a config object
	 *
	 * @param   Repository  $params
	 * @return  stdClass
	 */
	private function buildConfig(Repository $params)
	{
		// Object to hold our final config
		$config = new stdClass;

		$allow = new stdClass;
		$allow->name = '/^.*$/';
		$allow->styles = true;
		$allow->attributes = true;
		$allow->classes = true;

		$config->htmlSupport = [
			'allow' => [
				$allow
			]
		];

		return $config;
	}
}
