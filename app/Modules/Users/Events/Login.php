<?php
namespace App\Modules\Users\Events;

use Illuminate\Http\Request;

class Login
{
	/**
	 * @var Request
	 */
	public $request;

	/**
	 * Constructor
	 *
	 * @param  Request $request
	 * @return void
	 */
	public function __construct(Request $request)
	{
		$this->request = $request;
	}
}
