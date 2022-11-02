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
	 * @return  null|\Illuminate\View\View
	 */
	public function run()
	{
		return view('widgets.footer::index');
	}
}
