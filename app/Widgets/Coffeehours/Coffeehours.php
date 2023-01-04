<?php
namespace App\Widgets\Coffeehours;

use App\Modules\Widgets\Entities\Widget;
use App\Modules\News\Models\Type;
use App\Modules\News\Models\Association;
use Carbon\Carbon;

/**
 * Display Coffee Hours news articles
 */
class Coffeehours extends Widget
{
	/**
	 * Display
	 *
	 * @return  null|\Illuminate\View\View
	 */
	public function run()
	{
		$type = new Type();

		if ($id = (int)$this->params->get('catid'))
		{
			$type = Type::findOrFail($id);
		}

		$day = date('w');
		$week_start = Carbon::now()->modify('-' . $day . ' days');
		$week_end   = Carbon::now()->modify('+' . $this->params->get('future', 30) . ' days');

		$history = $this->params->get('history', 14);
		$start = $history
			? Carbon::now()->modify('-' . ($day + $history) . ' days')->format('Y-m-d') . ' 00:00:00'
			: $week_start->format('Y-m-d') . ' 00:00:00';
		$stop  = $week_end->format('Y-m-d') . ' 00:00:00';

		$rows = $type->articles()
			->with('associations')
			->where('published', '=', 1)
			->where('template', '=', 0)
			->where(function($where) use ($start, $stop)
			{
				$where->where('datetimenews', '<=', $start)
					->orWhere('datetimenews', '<=', $stop)
					->orWhereNull('datetimenewsend');
			})
			->where(function($where) use ($start, $stop)
			{
				$where->where('datetimenewsend', '>=', $start)
					->orWhere('datetimenewsend', '>=', $stop)
					->orWhereNull('datetimenewsend');
			})
			->where(function($where) use ($start, $stop)
			{
				$where->where('datetimenews', '<=', $start)
					->orWhere('datetimenews', '<=', $stop)
					->orWhereNotNull('datetimenewsend');
			})
			->where(function($where) use ($start, $stop)
			{
				$where->where('datetimenews', '>=', $start)
					->orWhere('datetimenews', '>=', $stop)
					->orWhereNull('datetimenewsend');
			})
			->orderBy('datetimenews', 'asc')
			->limit($this->params->get('limit', 100))
			->get();

		$attending = array();
		foreach ($rows as $event)
		{
			foreach ($event->associations()->get() as $assoc)
			{
				if (auth()->user() && $assoc->associd == auth()->user()->id)
				{
					$attending[$event->datetimenews->format('Y-m-d')] = $assoc->id;
				}
			}
		}

		$layout = $this->params->get('layout');
		$layout = $layout ?: 'index';

		$this->params->set('show_title', $this->model->showtitle);
		$this->params->set('title', $this->model->title);

		return view($this->getViewName($layout), [
			'rows'   => $rows,
			'params' => $this->params,
			'type'   => $type,
			'week_start' => $week_start,
			'attendance' => $attending
		]);
	}
}
