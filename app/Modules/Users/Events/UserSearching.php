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
	 * @return void
	 */
	public function __construct($search, $results)
	{
		$this->search = $search;
		$this->results = $results;
	}

	public function getResults()
	{
		return $this->results;
	}

	/*public function getSearchString()
	{
		return $this->search;
	}

	public function getResults()
	{
		return $this->results;
	}

	public function setResults($results)
	{
		$this->results = $results;

		return $this;
	}*/
}
