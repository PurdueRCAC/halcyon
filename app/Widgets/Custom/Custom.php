<?php
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
	 * @return  null|\Illuminate\View\View
	 */
	public function run()
	{
		$content = $this->model->content;

		/*if ($this->params->get('prepare_content', 0))
		{
			event($event = new PrepareContent($content));

			$content = $event->content;
		}*/

		return view($this->getViewName(), [
			'content' => $content,
			'model' => $this->model
		]);
	}
}
