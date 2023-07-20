<?php
namespace App\Listeners\Search\Resources;

use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\DB;
use App\Modules\Resources\Models\Asset;
use App\Modules\Search\Events\Searching;
use App\Modules\Search\Models\Result;

/**
 * Search listener
 */
class Resources
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Dispatcher  $events
	 * @return void
	 */
	public function subscribe(Dispatcher $events): void
	{
		$events->listen(Searching::class, self::class . '@handle');
	}

	/**
	 * Add records to search results
	 *
	 * @param   Searching $event
	 * @return  void
	 */
	public function handle(Searching $event): void
	{
		if (!$event->search)
		{
			return;
		}

		$search = $event->search;

		$query = Asset::query()
			->with('type')
			->where('display', '>', 0)
			->where(function($where)
			{
				$where->whereNotNull('listname')
					->where('listname', '!=', '');
			});

		$query->select('*',
			DB::raw("(
				IF(name = '" . $search . "', 30, 
					IF(name LIKE '" . $search . "%', 20, 
						IF(name LIKE '%" . $search . "%', 10, 0)
					)
				) +
				IF(listname LIKE '%" . $search . "', 10, 0) +
				IF(listname LIKE '%" . $search . "%', 1, 0) +
				IF(description LIKE '%" . $search . "%', 5, 0)
			) * 2
			AS `weight`"));

		$query->where(function($que) use ($search)
		{
			$que
				->where('name', '=', $search)
				->orWhere('name', 'like', $search . '%')
				->orWhere('name', 'like', '%' . $search . '%')
				->orWhere('listname', 'like', '%' . $search . '%')
				->orWhere('description', 'like', '%' . $search . '%');
		});

		$rows = $query
			->limit($event->limit)
			->orderBy($event->order, $event->order_dir)
			->get();

		foreach ($rows as $row)
		{
			$event->add(new Result(
				trans('resources::resources.resources'),
				$row->weight,
				$row->name,
				($row->description ? $row->description : ''),
				route('site.resources.' . $row->type->alias . '.show', ['name' => $row->listname]),
				$row->created_at,
				$row->updated_at
			));
		}
	}
}
