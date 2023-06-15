<?php
namespace App\Modules\Users\Events;

use App\Modules\Users\Models\User;

class UserLookup
{
	/**
	 * @var array<string,string>
	 */
	public $criteria;

	/**
	 * @var array<int,User>
	 */
	public $results;

	/**
	 * Constructor
	 *
	 * @param  array<string,string>  $criteria
	 * @return void
	 */
	public function __construct(array $criteria)
	{
		$this->criteria = $criteria;
		$this->results = array();
	}
}
