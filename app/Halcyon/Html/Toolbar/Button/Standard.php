<?php

namespace App\Halcyon\Html\Toolbar\Button;

use App\Halcyon\Html\Toolbar\Button;

/**
 * Renders a standard button
 */
class Standard extends Button
{
	/**
	 * Button type
	 *
	 * @var  string
	 */
	protected $_name = 'Standard';

	/**
	 * Fetch the HTML for the button
	 *
	 * @param   string   $type  Unused string.
	 * @param   string   $name  The name of the button icon class.
	 * @param   string   $text  Button text.
	 * @param   string   $task  Task associated with the button.
	 * @param   bool  $list  True to allow lists
	 * @return  string   HTML string for the button
	 */
	public function fetchButton($type = 'Standard', $name = 'secondary', $text = '', $task = '', $list = true)
	{
		$text = trans($text);
		$class = $this->fetchIconClass($name);
		$message = $this->_getCommand($text, $task, $list);

		$cls = 'btn toolbar-btn toolbar-submit btn-' . $name;

		$attr   = array();
		$attr[] = 'data-title="' . e($text) . '"';
		$attr[] = 'href="' . $task . '"';

		if ($list)
		{
			$cls .= ' toolbar-list';

			$attr[] = ' data-message="' . e($message) . '"';
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
	 * @param   string   $type      Unused string.
	 * @param   string   $name      Name to be used as apart of the id
	 * @param   string   $text      Button text
	 * @param   string   $task      The task associated with the button
	 * @param   bool  $list      True to allow use of lists
	 * @param   bool  $hideMenu  True to hide the menu on click
	 * @return  string   Button CSS Id
	 */
	public function fetchId($type = 'Standard', $name = '', $text = '', $task = '', $list = true, $hideMenu = false)
	{
		return $this->_parent->getName() . '-' . $name;
	}

	/**
	 * Get the JavaScript command for the button
	 *
	 * @param   string   $name  The task name as seen by the user
	 * @param   string   $task  The task used by the application
	 * @param   bool  $list  True is requires a list confirmation.
	 * @return  string
	 */
	protected function _getCommand($name, $task, $list)
	{
		return trans('global.please make a selection from the list');
	}
}
