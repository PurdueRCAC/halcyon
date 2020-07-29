<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Widgets\Footer;

use App\Modules\Widgets\Entities\Widget;

/**
 * Module class for diplaying site footer
 */
class Footer extends Widget
{
	/**
	 * Display module
	 *
	 * @return  void
	 */
	public function run()
	{
		return view('widgets.footer::index');
	}
}
