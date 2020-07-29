<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

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
	 * @return  void
	 */
	public function run()
	{
		if (!$this->params->get('key'))
		{
			return '';
		}

		return view($this->getViewName(), ['key' => $this->params->get('key')]);
	}
}
