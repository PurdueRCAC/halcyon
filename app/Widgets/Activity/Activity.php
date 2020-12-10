<?php
namespace App\Widgets\Activity;

use App\Modules\Widgets\Entities\Widget;
use App\Modules\History\Models\Log;

/**
 * Module class for user activity
 */
class Activity extends Widget
{
	/**
	 * Display widget contents
	 *
	 * @return  void
	 */
	public function run()
	{
		if (!app('isAdmin'))
		{
			return;
		}

		$activity = Log::query()
			->where('transportmethod', '!=', 'GET')
			->limit($this->model->params->get('limit', 10))
			->orderBy('datetime', 'desc')
			->get();

		return view($this->getViewName('index'), [
			'widget' => $this->model,
			'activity' => $activity
		]);
	}
}
