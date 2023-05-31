<?php
namespace App\Modules\Users\Events;

class UserLookup
{
	/**
	 * @var array<string,string>
	 */
	public $criteria;

	/**
	 * @var object|array
	 */
	public $results;

	/**
	 * Constructor
	 *
	 * @param  array<string,string>  $citeria
	 * @return void
	 */
	public function __construct(array $criteria)
	{
		$this->criteria = $criteria;
		$this->results = array();
	}
}
