<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Widgets\Submenu;

use App\Modules\Widgets\Entities\Widget;

/**
 * Module class for rendering a submenu
 */
class Submenu extends Widget
{
	/**
	 * Get the items of the submenu and display them.
	 *
	 * @return  void
	 */
	public function run()
	{
		// Initialise variables.
		$list = array(); //app('submenu')->all();

		if (!is_array($list) || !count($list))
		{
			return;
		}

		$layout = $this->params->get('layout');
		$layout = $layout ?: 'index';

		return view($this->getViewName($layout), [
			'list' => $list
		]);
	}
}
