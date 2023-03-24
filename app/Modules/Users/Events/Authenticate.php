<?php
namespace App\Modules\Users\Events;

use Illuminate\Http\Request;

class Authenticate
{
	/**
	 * @var Request
	 */
	public $request;

	/**
	 * @var bool
	 */
	public $authenticated;

	/**
	 * Constructor
	 *
	 * @param  Request $request
	 * @return void
	 */
	public function __construct(Request $request)
	{
		$this->request = $request;
		$this->authenticated = false;
	}
}
