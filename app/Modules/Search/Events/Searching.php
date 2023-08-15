<?php

namespace App\Modules\Search\Events;

use Illuminate\Support\Collection;

class Searching
{
	/**
	 * @var string
	 */
	public $search;

	/**
	 * @var int
	 */
	public $page;

	/**
	 * @var int
	 */
	public $limit;

	/**
	 * @var string
	 */
	public $order;

	/**
	 * @var string
	 */
	public $order_dir;

	/**
	 * @var Collection
	 */
	public $rows;

	/**
	 * Constructor
	 *
	 * @param string $search
	 * @param int $page
	 * @param int $limit
	 * @param string $order
	 * @param string $order_dir
	 * @return void
	 */
	public function __construct(string $search, int $page, int $limit, string $order, string $order_dir)
	{
		$this->search = $search;
		$this->page = $page;
		$this->limit = $limit;
		$this->order = $order;
		$this->order_dir = $order_dir;
		$this->rows = new Collection([]);
	}

	/**
	 * Add results
	 *
	 * @param  array<int,object>|Collection $rows
	 * @return self
	 */
	public function add($rows): self
	{
		if (!is_array($rows) && !($rows instanceof Collection))
		{
			$rows = [$rows];
		}

		foreach ($rows as $row)
		{
			$this->rows->push($row);
		}

		return $this;
	}
}
