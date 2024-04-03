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
	 * @var string
	 */
	public $error = null;

	/**
	 * Constructor
	 *
	 * @param  Request $request
	 * @param  string $authenticator
	 * @return void
	 */
	public function __construct(Request $request, string $authenticator = '')
	{
		$this->request = $request;
		$this->authenticator = $authenticator;
		$this->authenticated = false;
	}
}
