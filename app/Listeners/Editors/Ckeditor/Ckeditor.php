<?php
namespace App\Listeners\Editors\Ckeditor;

use App\Modules\Core\Events\EditorIsRendering;
use Illuminate\Config\Repository;
use stdClass;

/**
 * CKEditor
 */
class Ckeditor
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
		if ($editor->getFormatting() != 'html')
		{
			return;
		}

		self::$instances++;

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

		$params = new Repository(config('listener.editors.ckeditor', []));
		$params->set('class', $cls);
		$params->set('height', (18 * intval($attr['rows'])) . 'px');

		$config = json_encode($this->buildConfig($params));

		app('view')->addNamespace(
			'listener.editor.ckeditor',
			app_path() . '/Listeners/Editors/Ckeditor/views'
		);

		$editor->setContent(view('listener.editor.ckeditor::textarea', [
			'name'   => $name,
			'id'     => $attr['id'],
			'value'  => $content,
			'atts'   => $attributes,
			'config' => $config,
			'assets' => self::$instances > 1 ? false : true,
		]));

		return false;
	}

	/**
	 * Build a config object
	 *
	 * @param   array   $params
	 * @return  object  stdClass
	 */
	private function buildConfig($params)
	{
		// Object to hold our final config
		$config = new stdClass;
		$config->autoParagraph = false;
		$config->startupMode                   = 'wysiwyg';
		$config->tabSpaces                     = 4;
		//$config->height                        = $params->get('height', '200px');
		$config->toolbarCanCollapse            = false;
		$config->resize_enabled                = true;
		$config->emailProtection               = '';
		$config->protectedSource               = array('/@widget(.*)}/gi', '/<map[^>]*>(.|\n)*<\/map>/ig', '/<area([^>]*)\/?>/ig');
		$config->extraAllowedContent           = 'img(*)[*]; style(*)[*]; mark(*)[*]; span(*)[*]; map(*)[*]; area(*)[*]; *(*)[*]{*}';
		$config->bodyClass                     = 'ckeditor-body';
		$config->contentsCss                   = [];
		if ($css = $params->get('contentCss'))
		{
			$css = explode("\n", $css);
			foreach ($css as $c)
			{
				$c = trim($c);
				$config->contentsCss[] = asset($c) . '?t=' . time();
			}
		}
		$config->templates_replaceContent      = false;
		$config->filebrowserBrowseUrl          = route('admin.media.index');
		$config->filebrowserImageBrowseUrl     = '';
		$config->filebrowserImageBrowseLinkUrl = '';
		$config->filebrowserUploadUrl          = route('api.media.upload');
		$config->uploadUrl                     = route('api.media.upload');
		$config->filebrowserWindowWidth        = 400;
		$config->filebrowserWindowHeight       = 600;
		$config->toolbarGroups = [
			['name' => 'document', 'groups' => [ 'mode' ]], //, 'document', 'doctools' 
			['name' => 'clipboard', 'groups' => ['clipboard', 'undo']],
			['name' => 'editing', 'groups' => [ 'find', 'selection', 'editing' ]],
			['name' => 'links', 'groups' => [ 'links' ]],
			['name' => 'insert', 'groups' => [ 'insert' ]],
			//['name' => 'forms', 'groups' => [ 'forms' ]],
			['name' => 'tools', 'groups' => [ 'tools' ]],
			['name' => 'others', 'groups' => [ 'others' ]],
			'/',
			['name' => 'basicstyles', 'groups' => [ 'basicstyles', 'cleanup' ]],
			['name' => 'paragraph', 'groups' => [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ]],
			//['name' => 'styles', 'groups' => [ 'styles' ]],
			//['name' => 'colors', 'groups' => [ 'colors' ]],
			//['name' => 'about', 'groups' => [ 'about' ]],
		];
		$config->removeButtons = 'NewPage,Preview,Print,Save,Scayt,About,Styles,Flash,PageBreak,Language';

		// If minimal toolbar
		/*if (in_array('minimal', $params->get('class')))
		{
			$config->toolbar = array(
				array('Link', 'Unlink', 'Anchor'),
				array('Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript'),
				array('NumberedList', 'BulletedList')
			);
			$config->toolbarCanCollapse = false;

			//$config->resize_enabled = false;
			//$config->halcyonAutogrow_autoStart = false;
		}*/

		// If no footer
		if (in_array('no-footer', $params->get('class')))
		{
			$config->removePlugins = 'elementspath';
		}

		// Startup mode
		if (in_array($params->get('startupMode'), array('wysiwyg','source')))
		{
			$config->startupMode = $params->get('startupMode');
		}

		// Show source button
		if (!$params->get('sourceViewButton', 1))
		{
			$config->removeButtons .= ',Source';
		}

		// Height
		if ($h = $params->get('height'))
		{
			$config->height = $h;
		}

		// Class to add to ckeditor body
		if ($params->get('contentBodyClass'))
		{
			$config->bodyClass = $params->get('contentBodyClass');
		}

		// Add stylesheets to ckeditor content
		$css = $params->get('contentCss');
		$css = explode("\n", $css);
		if (is_array($css) && count($css))
		{
			$config->contentsCss = $css;
		}

		// File browsing
		if ($params->get('fileBrowserBrowseUrl'))
		{
			$config->filebrowserBrowseUrl = $params->get('fileBrowserBrowseUrl');
		}

		// Image browsing
		if ($params->get('fileBrowserImageBrowseUrl'))
		{
			$config->filebrowserImageBrowseUrl = $params->get('fileBrowserImageBrowseUrl');
		}

		// File upload
		if ($params->get('fileBrowserUploadUrl'))
		{
			$config->filebrowserUploadUrl = $params->get('fileBrowserUploadUrl');
		}

		// File browse popup size
		if ($params->get('fileBrowserWindowWidth'))
		{
			$config->filebrowserWindowWidth = $params->get('fileBrowserWindowWidth');
		}
		if ($params->get('fileBrowserWindowHeight'))
		{
			$config->filebrowserWindowHeight = $params->get('fileBrowserWindowHeight');
		}

		// Page templates
		if ($params->get('templates_files') && is_object($params->get('templates_files')))
		{
			foreach ($params->get('templates_files') as $name => $template)
			{
				// Make sure templates exists
				if (file_exists(app_path() . $template))
				{
					// Do we want to replace original ones
					if ($params->get('templates_replace'))
					{
						$config->templates = array();
						$config->templates_files = array();
					}

					array_push($config->templates, $name);
					array_push($config->templates_files, $template);
				}
			}
			// Make template definition a string
			$config->templates = implode(',', $config->templates);
		}

		// Allow scripts
		if ($params->get('allowScriptTags'))
		{
			$config->protectedSource[] = '/<script[^>]*>(.|\n)*<\/script>/ig';
		}

		// Let the global filters handle what HTML tags are or aren't allowed
		$config->allowedContent = true;

		return $config;
	}
}
