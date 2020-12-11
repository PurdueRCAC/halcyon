<?php

namespace App\Halcyon\Html\Toolbar\Button;

use App\Halcyon\Html\Toolbar\Button;

/**
 * Renders a custom button
 */
class Custom extends Button
{
	/**
	 * Button type
	 *
	 * @var  string
	 */
	protected $_name = 'Custom';

	/**
	 * Fetch the HTML for the button
	 *
	 * @param   string  $type  Button type, unused string.
	 * @param   string  $html  HTML strng for the button
	 * @param   string  $id    CSS id for the button
	 * @return  string  HTML string for the button
	 */
	public function fetchButton($type = 'Custom', $html = '', $id = 'custom')
	{
		return $html;
	}

	/**
	 * Get the button CSS Id
	 *
	 * @param   string  $type  Not used.
	 * @param   string  $html  Not used.
	 * @param   string  $id    The id prefix for the button.
	 * @return  string  Button CSS Id
	 */
	public function fetchId($type = 'Custom', $html = '', $id = 'custom')
	{
		return $this->_parent->getName() . '-' . $id;
	}
}
