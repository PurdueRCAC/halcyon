<?php
/**
 * @package    framework
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Halcyon\Html\Toolbar\Button;

use App\Halcyon\Html\Toolbar\Button;

/**
 * Renders a help popup window button
 */
class Help extends Button
{
	/**
	 * Button type
	 *
	 * @var  string
	 */
	protected $_name = 'Help';

	/**
	 * Fetches the button HTML code.
	 *
	 * @param   string   $type    Unused string.
	 * @param   string   $url     The URL to open
	 * @param   integer  $width   The window width
	 * @param   integer  $height  The window height
	 * @return  string
	 */
	public function fetchButton($type = 'Help', $url = '#', $width = 700, $height = 500)
	{
		$text  = \trans('global.toolbar.HELP');
		$class = $this->fetchIconClass('help');
		$msg   = \trans('global.toolbar.HELP', true);

		if (!strstr('?', $url)
		 && !strstr('&', $url)
		 && substr($url, 0, 4) != 'http')
		{
			$url = route('admin.help.index', ['module' => request()->segment(1), 'page' => $url]);
		}
		else
		{
			$url = $this->_getCommand($ref = $type, $com = false, $override = false, $module = app('request')->segemnt(1));
		}

		$html  = '<a href="' . $url . '" data-title="' . $text . '" data-message="' . $msg. '" data-width="' . $width . '" data-height="' . $height . '" rel="help" class="btn btn-help toolbar toolbar-popup">' . "\n";
		$html .= '<span class="' . $class . '">' . "\n";
		$html .= $text . "\n";
		$html .= '</span>' . "\n";
		$html .= '</a>' . "\n";

		return $html;
	}

	/**
	 * Get the button id
	 *
	 * @return  string  Button CSS Id
	 */
	public function fetchId()
	{
		return $this->_parent->getName() . '-' . 'help';
	}

	/**
	 * Get the JavaScript command for the button
	 *
	 * @param   string   $ref        The name of the help screen (its key reference).
	 * @param   boolean  $com        Use the help file in the component directory.
	 * @param   string   $override   Use this URL instead of any other.
	 * @param   string   $component  Name of component to get Help (null for current component)
	 * @return  string   JavaScript command string
	 */
	protected function _getCommand($ref, $com, $override, $component)
	{
		// Get Help URL
		$url = self::createURL($ref, $com, $override, $component);
		$url = htmlspecialchars($url, ENT_QUOTES);
		//$cmd = "Halcyon.popupWindow('$url', '" . \trans('JHELP', true) . "', 700, 500, 1)";

		return $url; //$cmd;
	}

	/**
	 * Create a URL for a given help key reference
	 *
	 * @param   string   $ref           The name of the help screen (its key reference)
	 * @param   boolean  $useComponent  Use the help file in the component directory
	 * @param   string   $override      Use this URL instead of any other
	 * @param   string   $component     Name of component (or null for current component)
	 * @return  string
	 */
	public static function createURL($ref, $useComponent = false, $override = null, $component = null)
	{
		$local = false;

		//  Determine the location of the help file.  At this stage the URL
		//  can contain substitution codes that will be replaced later.

		if ($override)
		{
			$url = $override;
		}
		else
		{
			// Get the user help URL.
			$user = auth()->user();
			$url = $user->options->get('helpsite');

			// If user hasn't specified a help URL, then get the global one.
			if ($url == '')
			{
				$url = config('app.helpurl');
			}

			// Component help URL overrides user and global.
			if ($useComponent)
			{
				// Look for help URL in component parameters.
				$url = config('modules.' . $component . '.help_url');

				if ($url == '')
				{
					$local = true;
					$url = 'modules/{component}/help/{language}/{keyref}';
				}
			}

			// Set up a local help URL.
			if (!$url)
			{
				$local = true;
				$url = 'help/{language}/{keyref}';
			}
		}

		// If the URL is local then make sure we have a valid file extension on the URL.
		if ($local)
		{
			if (!preg_match('#\.html$|\.xml$#i', $ref))
			{
				$url .= '.html';
			}
		}

		//  Replace substitution codes in the URL.
		$lang    = app('language');
		$version = HVERSION;
		$hver    = explode('.', $version);
		$hlang   = explode('-', $lang->getTag());

		$debug  = $lang->setDebug(false);
		$keyref = trans($ref);
		$lang->setDebug($debug);

		// Replace substitution codes in help URL.
		$search = array(
			'{app}', // Application name (eg. 'Administrator')
			'{component}', // Component name (eg. 'com_content')
			'{keyref}', // Help screen key reference
			'{language}', // Full language code (eg. 'en-US')
			'{langcode}', // Short language code (eg. 'en')
			'{langregion}', // Region code (eg. 'GB')
			'{major}', // major version number
			'{minor}', // minor version number
			'{maintenance}'// maintenance version number
		);

		$replace = array(
			'admin', // {app}
			$component, // {component}
			$keyref, // {keyref}
			$lang->getTag(), // {language}
			$hlang[0], // {langcode}
			$hlang[1], // {langregion}
			$hver[0], // {major}
			$hver[1], // {minor}
			$hver[2]// {maintenance}
		);

		// If the help file is local then check it exists.
		// If it doesn't then fallback to English.
		if ($local)
		{
			$try = str_replace($search, $replace, $url);

			if (!file_exists(app_path() . '/' . $try))
			{
				$replace[3] = 'en-US';
				$replace[4] = 'en';
				$replace[5] = 'US';
			}
		}

		$url = str_replace($search, $replace, $url);

		return $url;
	}
}
