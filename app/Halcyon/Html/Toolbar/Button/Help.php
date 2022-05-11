<?php

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
	public function fetchButton($type = 'Help', $url = '#')
	{
		$text  = \trans('global.toolbar.help');
		$class = $this->fetchIconClass('help');

		$id = str_replace(['::', '.'], ['_', '-'], $url);

		$html  = '<a href="#' . $id . '" data-toggle="modal" data-title="' . e($text) . '" rel="help" class="btn btn-help toolbar toolbar-popup">' . "\n";
		$html .= '<span class="' . $class . '">' . "\n";
		$html .= $text . "\n";
		$html .= '</span>' . "\n";
		$html .= '</a>' . "\n";
		$html .= '<div class="modal modal-help dialog" id="' . $id . '" tabindex="-1" aria-labelledby="' . $id . '-title" aria-hidden="true" title="' . e($text) . '">
		<div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
			<div class="modal-content dialog-content shadow-sm">
				<div class="modal-header">
					<div class="modal-title" id="' . $id . '-title">' . e($text) . '</div>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body dialog-body">
					<article>' . view($url) . '</article>
				</div>
			</div>
		</div>';

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
}
