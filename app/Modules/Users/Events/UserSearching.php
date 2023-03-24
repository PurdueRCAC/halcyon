<?php
namespace App\Modules\Users\Events;

class UserSearching
{
	/**
	 * @var string
	 */
	public $search;

	/**
	 * @var object|array
	 */
	public $results;

	/**
	 * Constructor
	 *
	 * @param  string $search
	 * @param  mixed  $results
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
	 * @return array
	 */
	public function getResults()
	{
		return $this->results;
	}
}
