<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Widgets\Custom;

use App\Modules\Widgets\Entities\Widget;

/**
 * Module class for diplaying custom content
 */
class Custom extends Widget
{
	/**
	 * Display module
	 *
	 * @return  void
	 */
	public function run()
	{
		return view($this->getViewName(), [
			'content' => $this->model->content,
			'model' => $this->model
		]);
	}
}
