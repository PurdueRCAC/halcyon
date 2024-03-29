<?php

namespace App\Halcyon\Html\Toolbar\Button;

use App\Halcyon\Html\Toolbar\Button;

/**
 * Renders a button separator
 */
class Separator extends Button
{
	/**
	 * Button type
	 *
	 * @var  string
	 */
	protected $_name = 'Separator';

	/**
	 * Get the HTML for a separator in the toolbar
	 *
	 * @param   array<int,mixed>  &$definition  Class name and custom width
	 * @return  string The HTML for the separator
	 */
	public function render(&$definition)
	{
		// Initialise variables.
		$class = null;
		$style = null;

		// Separator class name
		$class = (empty($definition[1])) ? 'spacer' : $definition[1];

		// Custom width
		$style = (empty($definition[2])) ? null : ' style="width:' . intval($definition[2]) . 'px;"';

		return '<li class="button ' . $class . '"' . $style . ">\n</li>\n";
	}

	/**
	 * Empty implementation (not required for separator)
	 *
	 * @return  string
	 */
	public function fetchButton()
	{
		return '';
	}
}
