<?php
namespace App\Listeners\Search\Knowledge;

use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\DB;
use App\Modules\Knowledge\Models\Page;
use App\Modules\Knowledge\Models\Associations;
use App\Modules\Search\Events\Searching;
use App\Modules\Search\Models\Result;

/**
 * Search listener
 */
class Knowledge
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
		$path = array();
		$levels = auth()->user()
			? auth()->user()->getAuthorisedViewLevels()
			: array(1);

		$p = (new Page)->getTable();
		$a = (new Associations)->getTable();

		$query = Associations::query()
			->join($p, $a . '.page_id', $p . '.id');

		$filters['order'] = 'weight';

		$query->select($p . '.title', $p . '.content', $p . '.params', $p . '.snippet', $p . '.created_at', $p . '.updated_at', $a . '.*',
			DB::raw("(
				IF(" . $p . ".title = '" . $search . "', 30, 
					IF(" . $p . ".title LIKE '" . $search . "%', 20, 
						IF(" . $p . ".title LIKE '%" . $search . "%', 10, 0)
					)
				) +
				IF(" . $a . ".path LIKE '%" . $search . "', 10, 0) +
				IF(" . $a . ".path LIKE '%" . $search . "%', 1, 0) +
				IF(" . $p . ".content LIKE '%" . $search . "%', 5, 0)
			) * 2
			AS `weight`"));

		$query->where(function($query) use ($search, $p, $a)
		{
			$query
				->where($p . '.title', '=', $search)
				->orWhere($p . '.title', 'like', $search . '%')
				->orWhere($p . '.title', 'like', '%' . $search . '%')
				->orWhere($a . '.path', 'like', '%' . $search . '%')
				->orWhere($p . '.content', 'like', '%' . $search . '%');
		});

		// Filter out retired trees
		$retired = Associations::query()
			->where('parent_id', '=', 1)
			->where('state', '=', 2)
			->get();

		foreach ($retired as $re)
		{
			$query->where(function($query) use ($a, $re)
			{
				$query->where($a . '.lft', '<', $re->lft)
					->orWhere($a . '.lft', '>', $re->rgt);
			});
		}

		$query->where($a . '.state', '=', 1);
		$query->where($a . '.access', '=', $levels);

		$rows = $query
			->limit($event->limit)
			->orderBy($event->order, $event->order_dir)
			->get();

		foreach ($rows as $row)
		{
			$ancestors = array_reverse($row->ancestors());
			foreach ($ancestors as $ancestor)
			{
				$row->page->mergeVariables($ancestor->page->variables->toArray(), true);
			}

			$event->add(new Result(
				trans('knowledge::knowledge.knowledge base'),
				$row->weight,
				$row->title,
				($row->page->body ? $row->page->body : ''),
				route('site.knowledge.page', ['uri' => $row->path]),
				$row->created_at,
				$row->updated_at
			));
		}
	}
}
