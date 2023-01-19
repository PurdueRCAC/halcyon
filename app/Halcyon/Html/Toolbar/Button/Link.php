<?php

namespace App\Halcyon\Html\Toolbar\Button;

use App\Halcyon\Html\Toolbar\Button;

/**
 * Renders a link button
 */
class Link extends Button
{
	/**
	 * Button type
	 *
	 * @var  string
	 */
	protected $_name = 'Link';

	/**
	 * Fetch the HTML for the button
	 *
	 * @param   string  $type  Unused string.
	 * @param   string  $name  Name to be used as apart of the id
	 * @param   string  $text  Button text
	 * @param   string  $url   The link url
	 * @return  string  HTML string for the button
	 */
	public function fetchButton($type = 'Link', $name = 'back', $text = '', $url = null, $list = false)
	{
		$text   = trans($text);
		$class  = $this->fetchIconClass($name);
		$task = $this->_getCommand($url);

		/*$html  = "<a data-title=\"$text\" href=\"$task\" class=\"toolbar-btn\">\n";
		$html .= "<span class=\"$class\">\n";
		$html .= "$text\n";
		$html .= "</span>\n";
		$html .= "</a>\n";*/

		$cls = 'btn toolbar-btn btn-' . $name;

		$attr   = array();
		$attr[] = 'href="' . $task . '"';
		$attr[] = 'data-title="' . e($text) . '"';

		if ($list)
		{
			$cls .= ' toolbar-list';

			$attr[] = ' data-message=""';
		}

		$html   = array();
		$html[] = '<a class="' . $cls . '" ' . implode(' ', $attr) . '>';
		$html[] = '<span class="' . $class . '">';
		$html[] = $text;
		$html[] = '</span>';
		$html[] = '</a>';

		$html = implode("\n", $html);

		return $html;
	}

	/**
	 * Get the button CSS Id
	 *
	 * @param   string  $type  The button type.
	 * @param   string  $name  The name of the button.
	 * @return  string  Button CSS Id
	 */
	public function fetchId($type = 'Link', $name = '')
	{
		return $this->_parent->getName() . '-' . $name;
	}

	/**
	 * Get the JavaScript command for the button
	 *
	 * @param   string  $url
	 * @return  string  JavaScript command string
	 */
	protected function _getCommand($url)
	{
		return $url;
	}
}
