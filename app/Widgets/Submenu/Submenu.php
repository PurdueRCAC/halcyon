<?php
namespace App\Widgets\Submenu;

use App\Modules\Widgets\Entities\Widget;

/**
 * Widget for rendering a submenu
 */
class Submenu extends Widget
{
	/**
	 * Get the items of the submenu and display them.
	 *
	 * @return  null|\Illuminate\View\View
	 */
	public function run()
	{
		// Initialise variables.
		$list = array(); //app('submenu')->all();

		if (empty($list))
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
