<?php

namespace App\Halcyon\Html\Toolbar\Button;

use App\Halcyon\Html\Toolbar\Button;
//use App\Halcyon\Html\Builder\Behavior;

/**
 * Renders a popup window button
 */
class Popup extends Button
{
	/**
	 * Button type
	 *
	 * @var  string
	 */
	protected $_name = 'Popup';

	/**
	 * Fetch the HTML for the button
	 *
	 * @param   string   $type     Unused string, formerly button type.
	 * @param   string   $name     Button name
	 * @param   string   $text     The link text
	 * @param   string   $url      URL for popup
	 * @param   int  $width    Width of popup
	 * @param   int  $height   Height of popup
	 * @param   int  $top      Top attribute.
	 * @param   int  $left     Left attribute
	 * @param   string   $onClose  JavaScript for the onClose event.
	 * @return  string   HTML string for the button
	 */
	public function fetchButton($type = 'Popup', $name = '', $text = '', $url = '', $width = 640, $height = 480, $top = 0, $left = 0, $onClose = '')
	{
		//Behavior::modal();

		$text  = trans($text);
		$class = $this->fetchIconClass($name);
		$url   = $this->_getCommand($name, $url, $width, $height, $top, $left);

		$html = [];
		$html[] = '<a data-title="' . $text . '" class="btn popup btn-' . $name . '" href="' . $url . '" data-toggle="modal" data-bs-toggle="modal" data-target="#modal-' . $name . '" data-bs-target="#modal-' . $name . '" data-width="' . $width . '" data-height="' . $height . '" data-close="function() {' . $onClose . '}">';
		$html[] = '<span class="' . $class . '">';
		$html[] = $text;
		$html[] = '</span>';
		$html[] = '</a>';
		$html[] = '<div class="modal fade" id="modal-' . $name . '" tabindex="-1" aria-labelledby="modal-' . $name . '-title" aria-hidden="true">
			<div class="modal-dialog modal-dialog-scrollable modal-dialog-slideout"><!-- modal-dialog-centered -->
				<div class="modal-content shadow-sm">
					<div class="modal-header">
						<div class="modal-title" id="modal-' . $name . '-title">' . $text . '</div>
						<button type="button" class="btn-close close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
							<span class="visually-hidden" aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
					</div>
				</div>
			</div>
		</div>';

		return implode("\n", $html);
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
		return $this->_parent->getName() . '-popup-' . $name;
	}

	/**
	 * Get the JavaScript command for the button
	 *
	 * @param   string   $name    Button name
	 * @param   string   $url     URL for popup
	 * @param   int  $width   Unused formerly width.
	 * @param   int  $height  Unused formerly height.
	 * @param   int  $top     Unused formerly top attribute.
	 * @param   int  $left    Unused formerly left attribure.
	 * @return  string   Command string
	 */
	protected function _getCommand($name, $url, $width, $height, $top, $left)
	{
		if (substr($url, 0, 4) !== 'http')
		{
			$root = rtrim(request()->root(), '/');
			if (substr($url, 0, strlen($root)) != $root)
			{
				$url = $root . '/' . ltrim($url, '/');
			}
		}

		return $url;
	}
}
