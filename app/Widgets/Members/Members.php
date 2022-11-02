<?php
namespace App\Widgets\Members;

use App\Modules\Widgets\Entities\Widget;
use App\Modules\Users\Models\UserUsername;
use Carbon\Carbon;

/**
 * Module class for user data
 */
class Members extends Widget
{
	/**
	 * Display widget contents
	 *
	 * @return  null|\Illuminate\View\View
	 */
	public function run()
	{
		if (!app('isAdmin'))
		{
			return;
		}

		$data = [];
		$now = Carbon::now();

		// Created in the past 30 days
		for ($i = 30; $i > 0; $i--)
		{
			$de = $now->format('Y-m-d');
			$dt = $now->modify('-1 day')->format('Y-m-d');

			$total = UserUsername::query()
				->where('datecreated', '>=', $dt . ' 00:00:00')
				->where('datecreated', '<', $de . ' 00:00:00')
				->count();

			//$total = rand(0, 50);

			$item = new \stdClass;
			$item->x = $dt;
			$item->y = $total;

			$data[] = $item;
		}

		return view($this->getViewName('index'), [
			'widget' => $this->model,
			'params' => $this->params,
			'data'   => $data
		]);
	}
}
