<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Widgets\Cookiepolicy;

use App\Modules\Widgets\Entities\Widget;

/**
 * Module class for displaying cookie policy
 */
class Cookiepolicy extends Widget
{
	/**
	 * Display module
	 *
	 * @return  void
	 */
	public function run()
	{
		if (auth()->user())
		{
			return;
		}

		if (app('session')->has('cookiepolicy'))
		{
			return;
		}

		$id = $this->params->get('id', 'eprivacy');
		$duration = $this->params->get('duration', 365);
		//$id = $this->widget->id;

		// Get current unix timestamp
		$now = time() + (config('offset') * 60 * 60);

		$expires = $now + 60*60*24* intval($duration);

		$hide = request()->cookie($id, '');

		if (!$hide && request()->input($id, '', 'get'))
		{
			setcookie($id, 'acknowledged', $expires);
			return;
		}

		if ($hide)
		{
			return;
		}

		$message = $this->params->get('message', trans('widget.cookiepolicy::cookiepolicy.default message', config('app.sitename')));

		$uri  = request()->url();
		$uri .= (strstr($uri, '?')) ? '&' : '?';
		$uri .= $id . '=close';

		$layout = $this->params->get('layout');
		$layout = $layout ?: 'index';

		return view($this->getViewName($layout), [
			'params'   => $this->params,
			'message'  => $message,
			'uri'      => $uri,
			'duration' => $duration,
		]);
	}
}
