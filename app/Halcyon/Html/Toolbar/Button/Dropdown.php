<?php

namespace App\Halcyon\Html\Toolbar\Button;

use App\Halcyon\Html\Toolbar\Button;

/**
 * Renders a dropdown button
 */
class Dropdown extends Button
{
	/**
	 * Button type
	 *
	 * @var  string
	 */
	protected $_name = 'Dropdown';

	/**
	 * Fetch the HTML for the button
	 *
	 * @param   string   $type     Unused string, formerly button type.
	 * @param   string   $name     Button name
	 * @param   string   $text     The link text
	 * @param   array<string,string>    $items
	 * @return  string   HTML string for the button
	 */
	public function fetchButton($type = 'Dropdown', $name = '', $text = '', $items = array())
	{
		$text  = trans($text);
		$class = $this->fetchIconClass($name);

		$html  = '<div class="dropdown btn-group">';
			$html .= '<button class="btn toolbar-btn dropdown-toggle" type="button" id="' . $name . '" data-toggle="dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
				$html .= '<span class="' . $class . '">' . $text . '</span>';
			$html .= '</button>';
			$html .= '<div class="dropdown-menu dropdown-menu-right" aria-labelledby="' . $name . '">';
			foreach ($items as $url => $title)
			{
				$html .= '<a href="' . $url . '" class="dropdown-item">' . $title . '</a>';
			}
			$html .= '</div>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Get the button id
	 *
	 * @param   string  $type  Button type
	 * @param   string  $name  Button name
	 * @return  string  Button CSS Id
	 */
	public function fetchId($type, $name)
	{
		return $this->_parent->getName() . '-dropdown-' . $name;
	}
}
