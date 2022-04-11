<?php
namespace App\Widgets\Publications;

use App\Modules\Publications\Models\Publication;
use App\Modules\Publications\Models\Type;
use App\Modules\Widgets\Entities\Widget;
use Carbon\Carbon;

/**
 * Widget for displaying a list of users
 */
class Publications extends Widget
{
	/**
	 * Display
	 *
	 * @return  void
	 */
	public function run()
	{
		$filters = [
			'year' => $this->params->get('year', '*'),
			'type' => $this->params->get('type', '*'),
			'limit' => $this->params->get('limit', 500),
			'order' => $this->params->get('order', 'published_at'),
			'order_dir' => $this->params->get('order_dir', 'desc'),
			'page' => 1,
		];

		$types = Type::query()
			->orderBy('id', 'asc')
			->get();

		// Get records
		$query = Publication::query()
			->where('state', '=', 1);

		if ($filters['year'] && $filters['year'] != '*')
		{
			$query->where('published_at', '>=', $filters['year'] . '-01-01 00:00:00')
				->where('published_at', '<', Carbon::parse($filters['year'] . '-01-01 00:00:00')->modify('+1 year')->format('Y') . '-01-01 00:00:00');
		}

		if ($filters['type'] && $filters['type'] != '*')
		{
			foreach ($types as $type)
			{
				if ($type->alias == $filters['type'])
				{
					$query->where('type_id', '=', $type->id);
					break;
				}
			}
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		$now = date("Y");
		$start = date("Y");
		$first = Publication::query()
			->orderBy('published_at', 'asc')
			->first();
		if ($first)
		{
			$start = $first->published_at->format('Y');
		}

		$years = array();
		for ($start; $start < $now; $start++)
		{
			$years[] = $start;
		}
		$years[] = $now;
		rsort($years);

		$layout = $this->params->get('layout');
		$layout = $layout ?: 'index';

		return view($this->getViewName($layout), [
			'rows' => $rows,
			'years'  => $years,
			'params' => $this->params,
		]);
	}
}
