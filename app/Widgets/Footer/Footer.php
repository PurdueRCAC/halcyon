<?php
namespace App\Widgets\Footer;

use App\Modules\Widgets\Entities\Widget;

/**
 * Widget for diplaying site footer
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
