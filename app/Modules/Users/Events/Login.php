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
	 * @var string
	 */
	public $authenticator;

	/**
	 * Constructor
	 *
	 * @param  Request $request
	 * @param  string $authenticator
	 * @return void
	 */
	public function __construct(Request $request, $authenticator = '')
	{
		$this->request = $request;
		$this->authenticator = $authenticator;
	}
}
