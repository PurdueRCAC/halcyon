<?php

namespace App\Halcyon\Html\Builder;

/**
 * Utility class for Tabs elements.
 */
class Tabs
{
	/**
	 * Flag for if a pane is currently open
	 *
	 * @var  bool
	 */
	public static $open = false;

	/**
	 * Creates a panes and creates the JavaScript object for it.
	 *
	 * @param   string  $group   The pane identifier.
	 * @param   array   $params  An array of option.
	 * @return  string
	 */
	public static function start($group = 'tabs', $params = array())
	{
		//self::behavior($group, $params);
		self::$open = false;

		return '<div class="tab-content" id="' . $group . '">';
	}

	/**
	 * Close the current pane
	 *
	 * @return  string  HTML to close the pane
	 */
	public static function end()
	{
		self::$open = false;

		return '</div>';
	}

	/**
	 * Begins the display of a new panel.
	 *
	 * @param   string  $text  Text to display.
	 * @param   string  $id    Identifier of the panel.
	 * @return  string  HTML to start a new panel
	 */
	public static function panel($text, $id)
	{
		$content = '';

		if (self::$open)
		{
			$content .= '</div>';
		}
		else
		{
			self::$open = true;
		}
		$content .= '<div class="tab-pane" role="tabpanel" id="tab' . $id . '" aria-labelledby="' . $id . '-tab"><a href="#tab' . $id . '">' . $text . '</a>';

		return $content;
	}
}
