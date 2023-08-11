<?php
namespace App\Modules\Users\Events;

class UserSearching
{
	/**
	 * @var string
	 */
	public $search;

	/**
	 * @var \Illuminate\Support\Collection|array<int,\App\Modules\Users\Moels\User>
	 */
	public $results;

	/**
	 * Constructor
	 *
	 * @param  string $search
	 * @param  \Illuminate\Support\Collection|array<int,\App\Modules\Users\Moels\User>  $results
	 * @return void
	 */
	public function __construct($search, $results)
	{
		$this->search = $search;
		$this->results = $results;
	}

	/**
	 * Get results
	 *
	 * @return \Illuminate\Support\Collection|array<int,\App\Modules\Users\Moels\User>
	 */
	public function getResults()
	{
		return $this->results;
	}
}
