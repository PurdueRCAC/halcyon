<?php

namespace App\Halcyon\Html\Toolbar\Button;

use App\Halcyon\Html\Toolbar\Button;
use App\Halcyon\Html\Builder\Behavior;

/**
 * Renders a standard button with a confirm dialog
 */
class Confirm extends Button
{
	/**
	 * Button type
	 *
	 * @var  string
	 */
	protected $_name = 'Confirm';

	/**
	 * Fetch the HTML for the button
	 *
	 * @param   string   $type      Unused string.
	 * @param   string   $msg       Message to render
	 * @param   string   $name      Name to be used as apart of the id
	 * @param   string   $text      Button text
	 * @param   string   $task      The task associated with the button
	 * @param   boolean  $list      True to allow use of lists
	 * @param   boolean  $hideMenu  True to hide the menu on click
	 * @return  string   HTML string for the button
	 */
	public function fetchButton($type = 'Confirm', $msg = '', $name = '', $text = '', $task = '', $list = true, $hideMenu = false)
	{
		$text   = \trans($text);
		//$msg    = \trans($msg);
		$class  = $this->fetchIconClass($name);
		$message = $this->_getCommand($msg, $name, $task, $list);

		$cls = 'btn toolbar-btn toolbar-confirm btn-' . $name;

		$attr   = array();
		$attr[] = 'href="' . $task . '"';
		$attr[] = 'data-title="' . e($text) . '"';
		$attr[] = 'data-confirm="' . e($msg) . '"';

		if ($list)
		{
			$cls .= ' toolbar-list';

			$attr[] = ' data-message="' . $message . '"';
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
	 * @param   string   $type      Button type
	 * @param   string   $name      Name to be used as apart of the id
	 * @param   string   $text      Button text
	 * @param   string   $task      The task associated with the button
	 * @param   boolean  $list      True to allow use of lists
	 * @param   boolean  $hideMenu  True to hide the menu on click
	 * @return  string  Button CSS Id
	 */
	public function fetchId($type = 'Confirm', $name = '', $text = '', $task = '', $list = true, $hideMenu = false)
	{
		return $this->_parent->getName() . '-' . $text;
	}

	/**
	 * Get the JavaScript command for the button
	 *
	 * @param   object   $msg   The message to display.
	 * @param   string   $name  Not used.
	 * @param   string   $task  The task used by the application
	 * @param   boolean  $list  True is requires a list confirmation.
	 * @return  string
	 */
	protected function _getCommand($msg, $name, $task, $list)
	{
		//Behavior::framework();

		$message = trans('global.toolbar.please first make a selection from the list');
		//$message = str_replace('"', '&quot;', $message);

		return $message;
	}
}
