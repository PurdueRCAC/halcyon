<?php
namespace App\Widgets\Toolbar;

use App\Modules\Widgets\Entities\Widget;
use App\Halcyon\Facades\Toolbar;

/**
 * Widget for displaying module toolbar
 */
class Toolbar extends Widget
{
	/**
	 * Display contents
	 *
	 * @return  void
	 */
	public function run()
	{
		if (!app('isAdmin'))
		{
			return;
		}

		$layout = $this->params->get('layout');
		$layout = $layout ?: 'index';

		return view($this->getViewName($layout), [
			'toolbar' => Toolbar::render('toolbar')
		]);
	}
}
