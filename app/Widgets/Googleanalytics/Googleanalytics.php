<?php
namespace App\Widgets\Googleanalytics;

use App\Modules\Widgets\Entities\Widget;

/**
 * Google Analytics widget
 */
class Googleanalytics extends Widget
{
	/**
	 * Display module
	 *
	 * @return  null|\Illuminate\View\View
	 */
	public function run()
	{
		$key = $this->params->get('key');

		if (!$key)
		{
			return;
		}

		$service = $this->params->get('service');
		$service = $service ?: 'ga';

		return view('widget.googleanalytics::' . $service, ['key' => $key]);
	}
}
