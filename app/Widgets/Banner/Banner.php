<?php
namespace App\Widgets\Banner;

use App\Modules\Widgets\Entities\Widget;
use App\Modules\News\Models\Article;
use App\Modules\News\Models\Type;
use Carbon\Carbon;

/**
 * Display news articles from selected category
 */
class Banner extends Widget
{
	/**
	 * Display
	 *
	 * @return  void
	 */
	public function run()
	{
		$now = Carbon::now();

		$today     = Carbon::now()->format('Y-m-d') . ' 00:00:00';
		$tomorrow  = Carbon::now()->modify('+1 day')->format('Y-m-d') . ' 00:00:00';
		$plus12    = Carbon::now()->modify('+12 hours')->toDateTimeString();
		$minus12   = Carbon::now()->modify('-12 hours')->toDateTimeString();

		$query = Article::query()
			->wherePublished()
			->where('template', '=', 0)
			->where(function($where) use ($today, $plus12, $minus12, $tomorrow)
			{
				$where->where('datetimenews', '=', $today)
					->orWhere('datetimenewsend', '=', $today)
					->orWhere(function($w) use ($plus12, $minus12, $tomorrow)
					{
						$w->where(function($wh) use ($plus12, $tomorrow)
						{
							$wh->where('datetimenews', '<=', $plus12)
								->where('datetimenews', '<>', $tomorrow);
						})
						->where(function($wh) use ($minus12)
						{
							$wh->whereNull('datetimenewsend')
								//->orWhere('datetimenewsend', '=', '0000-00-00 00:00:00')
								->orWhere('datetimenewsend', '>=', $minus12);
						});
					});
			});

		$type = new Type();

		if ($id = (int)$this->params->get('catid'))
		{
			$type = Type::findOrFail($id);

			$query->where('newstypeid', '=', $id);
		}

		$outages = $query
			->orderBy('datetimenews', 'desc')
			->limit($this->params->get('limit', 1))
			->get();

		$type2 = new Type();
		$maintenance = array();

		if ($id = (int)$this->params->get('catid2'))
		{
			$type2 = Type::findOrFail($id);

			$maintenance = Article::query()
				->wherePublished()
				->where('template', '=', 0)
				/*->where('datetimenews', '>=', $now->toDateTimeString())
				->where(function($where) use ($now)
				{
					$where->whereNull('datetimenewsend')
						->orWhere('datetimenewsend', '=', '0000-00-00 00:00:00')
						->orWhere(function($w) use ($now)
						{
							$w->where('datetimenewsend', '!=', '0000-00-00 00:00:00')
								->where('datetimenewsend', '>', $now);
						});
				})*/
				->where(function($where) use ($today, $plus12, $minus12, $tomorrow, $now)
				{
					$where->where('datetimenews', '>=', $now)
						->orWhere(function($w) use ($now)
						{
							$w->where('datetimenews', '<', $now)
								->where(function($wh) use ($now)
								{
									$wh->whereNull('datetimenewsend')
										//->orWhere('datetimenewsend', '=', '0000-00-00 00:00:00')
										->orWhere('datetimenewsend', '>=', $now);
								});
						});
				})
				->where('newstypeid', '=', $id)
				->orderBy('datetimenews', 'desc')
				->limit($this->params->get('limit', 1))
				->get();
		}

		$layout = $this->params->get('layout');
		$layout = $layout ?: 'index';

		return view($this->getViewName($layout), [
			'outages' => $outages,
			'maintenance' => $maintenance,
			'params'   => $this->params,
			'type'     => $type,
			'type2'    => $type2
		]);
	}
}
