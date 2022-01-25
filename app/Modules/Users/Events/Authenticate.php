<?php
namespace App\Modules\Users\Events;

class Authenticate
{
	/**
	 * @var object
	 */
	public $request;

	/**
	 * @var string
	 */
	public $authenticated;

	/**
	 * Constructor
	 *
	 * @param  Request $request
	 * @return void
	 */
	public function __construct($request)
	{
		$this->request = $request;
		$this->authenticated = false;
	}
}
