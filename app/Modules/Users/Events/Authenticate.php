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
	 * @var string
	 */
	public $authenticator;

	/**
	 * @var bool
	 */
	public $authenticated;

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
		$this->authenticated = false;
	}
}
