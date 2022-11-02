<?php
namespace App\Widgets\Activity;

use App\Modules\Widgets\Entities\Widget;
use App\Modules\History\Models\Log;
use Carbon\Carbon;

/**
 * Module class for user activity
 */
class Activity extends Widget
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

		$range = $this->model->params->get('range', 14);
		$ago = Carbon::now()->modify('-' . $range . ' days');

		$success = array();
		$errors = array();
		$notfound = array();
		for ($i = 0; $i < $range; $i++)
		{
			$today = $ago->format('Y-m-d') . ' 00:00:00';
			$key = $ago->format('Y-m-d');
			$tomorrow = $ago->modify('+1 day')->format('Y-m-d') . ' 00:00:00';

			$errors[$key] = Log::query()
				//->where('transportmethod', '==', 'GET')
				->where('status', '>=', 500)
				->where('datetime', '>=', $today)
				->where('datetime', '<', $tomorrow)
				->count();

			$notfound[$key] = Log::query()
				//->where('transportmethod', '==', 'GET')
				->where('status', '=', 404)
				->where('datetime', '>=', $today)
				->where('datetime', '<', $tomorrow)
				->count();

			$success[$key] = Log::query()
				//->where('transportmethod', '==', 'GET')
				->where('status', '<', 300)
				->where('datetime', '>=', $today)
				->where('datetime', '<', $tomorrow)
				->count();
		}

		/*$activity = Log::query()
			->where('transportmethod', '!=', 'GET')
			->limit($this->model->params->get('limit', 10))
			->orderBy('datetime', 'desc')
			->get();*/

		return view($this->getViewName('index'), [
			'widget' => $this->model,
			//'activity' => $activity,
			'success' => $success,
			'errors' => $errors,
			'notfound' => $notfound,
		]);
	}
}
