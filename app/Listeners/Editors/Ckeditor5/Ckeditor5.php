<?php
namespace App\Listeners\Editors\Ckeditor5;

use App\Modules\Core\Events\EditorIsRendering;
use Illuminate\Config\Repository;
use stdClass;

/**
 * CKEditor v5
 */
class Ckeditor5
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

		$params = new Repository(config('listeners.editors.ckeditor5', []));
		$params->set('class', $cls);
		$params->set('height', (18 * intval($attr['rows'])) . 'px');

		$config = json_encode($this->buildConfig($params));

		app('view')->addNamespace(
			'listener.editor.ckeditor5',
			app_path() . '/Listeners/Editors/Ckeditor5/views'
		);

		$editor->setContent(view('listener.editor.ckeditor5::textarea', [
			'name'  => $editor->getName(),
			'id'    => $attr['id'],
			'value' => $editor->getValue(),
			'atts'  => $attributes,
			'config' => $config,
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
		/*$config->autoParagraph = false;
		$config->startupMode                   = 'wysiwyg';
		$config->tabSpaces                     = 4;
		$config->height                        = '200px';
		$config->toolbarCanCollapse            = false;
		$config->extraPlugins                  = 'tableresize,iframedialog,halcyonhighlight';
		$config->removePlugins                 = '';
		$config->resize_enabled                = true;
		$config->emailProtection               = '';
		$config->protectedSource               = array('/@widget(.*)}/gi', '/<map[^>]*>(.|\n)*<\/map>/ig', '/<area([^>]*)\/?>/ig');
		$config->extraAllowedContent           = 'img(*)[*]; style(*)[*]; mark(*)[*]; span(*)[*]; map(*)[*]; area(*)[*]; *(*)[*]{*}';
		$config->specialChars                  = array('!', '&quot;', '#', '$', '%', '&amp;', "'", '(', ')', '*', '+', '-', '.', '/', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', ':', ';', '&lt;', '=', '&gt;', '?', '@', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '[', ']', '^', '_', '`', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '{', '|', '}', '~', "&euro;", "&lsquo;", "&rsquo;", "&ldquo;", "&rdquo;", "&ndash;", "&mdash;", "&iexcl;", "&cent;", "&pound;", "&curren;", "&yen;", "&brvbar;", "&sect;", "&uml;", "&copy;", "&ordf;", "&laquo;", "&not;", "&reg;", "&macr;", "&deg;", "&sup2;", "&sup3;", "&acute;", "&micro;", "&para;", "&middot;", "&cedil;", "&sup1;", "&ordm;", "&raquo;", "&frac14;", "&frac12;", "&frac34;", "&iquest;", "&Agrave;", "&Aacute;", "&Acirc;", "&Atilde;", "&Auml;", "&Aring;", "&AElig;", "&Ccedil;", "&Egrave;", "&Eacute;", "&Ecirc;", "&Euml;", "&Igrave;", "&Iacute;", "&Icirc;", "&Iuml;", "&ETH;", "&Ntilde;", "&Ograve;", "&Oacute;", "&Ocirc;", "&Otilde;", "&Ouml;", "&times;", "&Oslash;", "&Ugrave;", "&Uacute;", "&Ucirc;", "&Uuml;", "&Yacute;", "&THORN;", "&szlig;", "&agrave;", "&aacute;", "&acirc;", "&atilde;", "&auml;", "&aring;", "&aelig;", "&ccedil;", "&egrave;", "&eacute;", "&ecirc;", "&euml;", "&igrave;", "&iacute;", "&icirc;", "&iuml;", "&eth;", "&ntilde;", "&ograve;", "&oacute;", "&ocirc;", "&otilde;", "&ouml;", "&divide;", "&oslash;", "&ugrave;", "&uacute;", "&ucirc;", "&uuml;", "&yacute;", "&thorn;", "&yuml;", "&OElig;", "&oelig;", "&#372;", "&#374", "&#373", "&#375;", "&sbquo;", "&#8219;", "&bdquo;", "&hellip;", "&trade;", "&#9658;", "&bull;", "&rarr;", "&rArr;", "&hArr;", "&diams;", "&asymp;", "&Omega;");
		$config->disableNativeSpellChecker     = false;
		$config->scayt_autoStartup             = false;
		$config->scayt_contextCommands         = 'all';
		$config->scayt_maxSuggestions          = 5;
		$config->scayt_moreSuggestions         = 'off';
		$config->bodyClass                     = 'ckeditor-body';
		$config->contentsCss                   = [
			asset('modules/core/vendor/bootstrap/bootstrap.min.css') . '?v=' . filemtime(public_path() . '/modules/core/vendor/bootstrap/bootstrap.min.css'),
			asset('listeners/editors/ckeditor5/css/ckeditor5.css') . '?v=' . filemtime(public_path() . '/listeners/editors/ckeditor5/css/ckeditor5.css')
		];
		//$config->templates                     = array('halcyon');
		//$config->templates_files               = array(public_path() . '/listeners/editors/ckeditor/assets/templates/halcyon.js');
		$config->templates_replaceContent      = false;
		$config->filebrowserBrowseUrl          = '';
		$config->filebrowserImageBrowseUrl     = '';
		$config->filebrowserImageBrowseLinkUrl = '';
		$config->filebrowserUploadUrl          = '';
		$config->filebrowserWindowWidth        = 400;
		$config->filebrowserWindowHeight       = 600;
		$config->toolbar = array(
			//array('Find', 'Replace', '-', 'Scayt', 'Maximize', 'ShowBlocks'),
			array('Image','Table','HorizontalRule', 'Smiley', 'SpecialChar', 'Iframe'), //'PageBreak', 
			array('Link', 'Unlink'), //, 'Anchor'
			'/',
			array('Format', 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript'),
			array('NumberedList', 'BulletedList', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock')
		);

		$tlbr = array();
		if ($params->get('colorButton'))
		{
			$config->extraPlugins .= ',colorbutton';
			$tlbr[] = 'TextColor';
			$tlbr[] = 'BGColor';
		}
		if ($params->get('fontSize'))
		{
			$config->extraPlugins .= ',font';
			$tlbr[] = 'FontSize';
		}
		if (!empty($tlbr))
		{
			$config->toolbar[] = $tlbr;
		}

		//$config->toolbar[] = array('HalcyonMacro');

		// If minimal toolbar
		if (in_array('minimal', $params->get('class')))
		{
			$config->toolbar = array(
				array('Link', 'Unlink', 'Anchor'),
				array('Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript'),
				array('NumberedList', 'BulletedList')
			);
			$config->toolbarCanCollapse = false;

			//$config->resize_enabled = false;
			//$config->halcyonAutogrow_autoStart = false;
		}

		// Image plugin if in minimal mode
		if (in_array('minimal', $params->get('class'))
		 && in_array('images', $params->get('class')))
		{
			// push after links section
			$config->toolbar = array_merge(array_splice($config->toolbar, 0, 1), array(array('Image')), $config->toolbar);
		}

		// If no footer
		//if (in_array('no-footer', $params->get('class')))
		//{
			$config->removePlugins = 'elementspath';
		//}

		// Setup codemirror
		$config->codemirror = new stdClass;
		$config->codemirror->autoFormatOnModeChange = false;
		$config->codemirror->autoCloseTags          = false;
		$config->codemirror->autoCloseBrackets      = false;

		// Startup mode
		if (in_array($params->get('startupMode'), array('wysiwyg','source')))
		{
			$config->startupMode = $params->get('startupMode');
		}

		// Show source button
		if ($params->get('sourceViewButton', 1))
		{
			array_unshift($config->toolbar[0], 'Source', '-');
			$config->extraPlugins .= ',codemirror';
		}

		// Height
		if ($params->get('height'))
		{
			$config->height = $params->get('height', '200px');
		}

		// // Autogrow auto-start
		// if (is_bool($params->get('autoGrowAutoStart')))
		// {
		// 	$config->halcyonAutogrow_autoStart = $params->get('autoGrowAutoStart');
		// }

		// // Auto grow min height
		// if (is_numeric($params->get('autoGrowMinHeight')))
		// {
		// 	$config->halcyonAutogrow_minHeight = $params->get('autoGrowMinHeight');
		// }

		// // Autogrow max height
		// if (is_numeric($params->get('autoGrowMaxHeight')))
		// {
		// 	$config->halcyonAutogrow_maxHeight = $params->get('autoGrowMaxHeight');
		// }

		// Auto start spell check
		//if (is_bool($params->get('spellCheckAutoStart')))
		//{
			$config->scayt_autoStartup = false; //$params->get('spellCheckAutoStart');
		//}

		// Spell check max suggesstions
		//if (is_numeric($params->get('spellCheckMaxSuggesstions')))
		//{
			$config->scayt_maxSuggestions = false;//$params->get('spellCheckMaxSuggesstions');
		//}

		// Class to add to ckeditor body
		if ($params->get('contentBodyClass'))
		{
			$config->bodyClass = $params->get('contentBodyClass');
		}

		// add stylesheets to ckeditor content
		// Otherwise, do not style
		if (is_array($params->get('contentCss')) && count($params->get('contentCss')))
		{
			$config->contentsCss = $params->get('contentCss');
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

		// Allow php
		if ($params->get('allowPhpTags'))
		{
			$config->protectedSource[] = '/<\?[\s\S]*?\?>/g';
			$config->codemirror->mode = 'application/x-httpd-php';
		}

		// Set editor skin
		//$config->skin = $params->get('skin', 'moono');

		// Let the global filters handle what HTML tags are or aren't allowed
		$config->allowedContent = true;*/

		return $config;
	}
}
