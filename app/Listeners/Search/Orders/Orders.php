<?php
namespace App\Listeners\Search\Orders;

use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\DB;
use App\Modules\Orders\Models\Product;
use App\Modules\Orders\Models\Category;
use App\Modules\Search\Events\Searching;
use App\Modules\Search\Models\Result;

/**
 * Search listener
 */
class Orders
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

		$p = (new Product)->getTable();
		$c = (new Category)->getTable();

		$query = Product::query()
			->join($c, $c . '.id', $p . '.ordercategoryid');

		$query->select($p . '.*', $c . '.name AS category_name',
			DB::raw("(
				IF(" . $p . ".name = '" . $search . "', 30, 
					IF(" . $p . ".name LIKE '" . $search . "%', 20, 
						IF(" . $p . ".name LIKE '%" . $search . "%', 10, 0)
					)
				) +
				IF(" . $p . ".description LIKE '%" . $search . "%', 5, 0)
			) * 2
			AS `weight`"));

		$query->where($p . '.name', '=', $search)
			->where($p . '.name', 'like', '%' . $search . '%')
			->where($p . '.description', 'like', '%' . $search . '%');

		$rows = $query
			->limit($event->limit)
			->orderBy($event->order, $event->order_dir)
			->get();

		foreach ($rows as $row)
		{
			$event->add(new Result(
				trans('orders::orders.products'),
				$row->weight,
				$row->name,
				($row->description ? $row->description : ''),
				route('site.orders.products') . '#' . $row->id . '_product',
				$row->datetimecreated,
				$row->datetimecreated
			));
		}
	}
}
