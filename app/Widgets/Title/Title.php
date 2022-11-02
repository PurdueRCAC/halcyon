<?php
namespace App\Widgets\Title;

use App\Modules\Widgets\Entities\Widget;

/**
 * Display active widget title
 */
class Title extends Widget
{
	/**
	 * Display title
	 *
	 * @return  null|\Illuminate\View\View
	 */
	public function run()
	{
		$title = null;
		if (app()->has('ModuleTitle'))
		{
			$title = app()->get('ModuleTitle');
		}

		$layout = (string)$this->params->get('layout', 'index');

		return view($this->getViewName($layout), [
			'title' => $title
		]);
	}
}
