<?php
namespace App\Modules\Users\Events;

class UserLookup
{
	/**
	 * @var string
	 */
	public $criteria;

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
	public function __construct($criteria)
	{
		$this->criteria = $criteria;
		$this->results = array();
	}
}
