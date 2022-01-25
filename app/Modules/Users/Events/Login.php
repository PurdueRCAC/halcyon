<?php
namespace App\Modules\Users\Events;

class Login
{
	/**
	 * @var object
	 */
	public $request;

	/**
	 * Constructor
	 *
	 * @param  Request $request
	 * @return void
	 */
	public function __construct($request)
	{
		$this->request = $request;
	}
}
